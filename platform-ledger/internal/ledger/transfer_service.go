package ledger

import (
	"database/sql"
	"fmt"
	"time"

	"github.com/google/uuid"
	"github.com/shopspring/decimal"
)

type TransferRequest struct {
	IdempotencyKey string          `json:"idempotency_key"`
	FromAccountID  uuid.UUID       `json:"from_account_id"`
	ToAccountID    uuid.UUID       `json:"to_account_id"`
	Amount         decimal.Decimal `json:"amount"`
	Currency       string          `json:"currency"`
	Metadata       map[string]any  `json:"metadata,omitempty"`
}

type Transfer struct {
	ID             uuid.UUID       `json:"id"`
	IdempotencyKey string          `json:"idempotency_key"`
	FromAccountID  uuid.UUID       `json:"from_account_id"`
	ToAccountID    uuid.UUID       `json:"to_account_id"`
	Amount         decimal.Decimal `json:"amount"`
	Currency       string          `json:"currency"`
	Status         string          `json:"status"`
	CreatedAt      time.Time       `json:"created_at"`
}

type TransferService struct {
	db *sql.DB
}

func NewTransferService(db *sql.DB) *TransferService {
	return &TransferService{db: db}
}

func (s *TransferService) Execute(req TransferRequest) (*Transfer, error) {
	// 1. Vérification idempotence
	existing, err := s.findByIdempotencyKey(req.IdempotencyKey)
	if err != nil && err != sql.ErrNoRows {
		return nil, fmt.Errorf("failed to check idempotency: %w", err)
	}
	if existing != nil {
		return existing, nil // Retourne la transaction existante
	}

	// 2. Validation
	if req.Amount.LessThanOrEqual(decimal.Zero) {
		return nil, fmt.Errorf("amount must be positive")
	}

	// 3. Transaction PostgreSQL
	tx, err := s.db.Begin()
	if err != nil {
		return nil, fmt.Errorf("failed to begin transaction: %w", err)
	}
	defer func() {
	_ = tx.Rollback() // Rollback si commit n'a pas eu lieu
}()

	// 4. Lock et vérification comptes
	fromAccount, err := s.lockAccount(tx, req.FromAccountID)
	if err != nil {
		return nil, fmt.Errorf("failed to lock source account: %w", err)
	}

	toAccount, err := s.lockAccount(tx, req.ToAccountID)
	if err != nil {
		return nil, fmt.Errorf("failed to lock destination account: %w", err)
	}

	// 5. Vérification solde
	if fromAccount.Balance.LessThan(req.Amount) {
		return nil, fmt.Errorf("insufficient funds: balance=%s, amount=%s", 
			fromAccount.Balance.String(), req.Amount.String())
	}

	// 6. Créer la transaction
	transactionID := uuid.New()
	now := time.Now()

	_, err = tx.Exec(`
		INSERT INTO transactions (id, idempotency_key, type, amount, currency, status, created_at)
		VALUES ($1, $2, $3, $4, $5, $6, $7)
	`, transactionID, req.IdempotencyKey, "TRANSFER", req.Amount.String(), req.Currency, "COMPLETED", now)

	if err != nil {
		return nil, fmt.Errorf("failed to create transaction: %w", err)
	}

	// 7. Double-écriture : DEBIT du compte source
	newFromBalance := fromAccount.Balance.Sub(req.Amount)
	err = s.createEntry(tx, transactionID, req.FromAccountID, "DEBIT", req.Amount, newFromBalance)
	if err != nil {
		return nil, fmt.Errorf("failed to create debit entry: %w", err)
	}

	// 8. Double-écriture : CREDIT du compte destination
	newToBalance := toAccount.Balance.Add(req.Amount)
	err = s.createEntry(tx, transactionID, req.ToAccountID, "CREDIT", req.Amount, newToBalance)
	if err != nil {
		return nil, fmt.Errorf("failed to create credit entry: %w", err)
	}

	// 9. Mise à jour balances
	_, err = tx.Exec(`
		UPDATE accounts SET balance = $1, updated_at = $2, version = version + 1
		WHERE id = $3
	`, newFromBalance.String(), now, req.FromAccountID)
	if err != nil {
		return nil, fmt.Errorf("failed to update source balance: %w", err)
	}

	_, err = tx.Exec(`
		UPDATE accounts SET balance = $1, updated_at = $2, version = version + 1
		WHERE id = $3
	`, newToBalance.String(), now, req.ToAccountID)
	if err != nil {
		return nil, fmt.Errorf("failed to update destination balance: %w", err)
	}

	// 10. Commit
	if err := tx.Commit(); err != nil {
		return nil, fmt.Errorf("failed to commit transaction: %w", err)
	}

	return &Transfer{
		ID:             transactionID,
		IdempotencyKey: req.IdempotencyKey,
		FromAccountID:  req.FromAccountID,
		ToAccountID:    req.ToAccountID,
		Amount:         req.Amount,
		Currency:       req.Currency,
		Status:         "COMPLETED",
		CreatedAt:      now,
	}, nil
}

func (s *TransferService) lockAccount(tx *sql.Tx, accountID uuid.UUID) (*Account, error) {
	account := &Account{}
	var balance, availableBalance float64

	err := tx.QueryRow(`
		SELECT id, external_id, type, currency, balance, available_balance, status, version
		FROM accounts
		WHERE id = $1
		FOR UPDATE
	`, accountID).Scan(
		&account.ID,
		&account.ExternalID,
		&account.Type,
		&account.Currency,
		&balance,
		&availableBalance,
		&account.Status,
		&account.Version,
	)

	if err == sql.ErrNoRows {
		return nil, fmt.Errorf("account not found")
	}
	if err != nil {
		return nil, err
	}

	account.Balance = decimal.NewFromFloat(balance)
	account.AvailableBalance = decimal.NewFromFloat(availableBalance)

	return account, nil
}

func (s *TransferService) createEntry(tx *sql.Tx, transactionID, accountID uuid.UUID, entryType string, amount, balanceAfter decimal.Decimal) error {
	_, err := tx.Exec(`
		INSERT INTO entries (id, transaction_id, account_id, type, amount, balance_after, created_at)
		VALUES ($1, $2, $3, $4, $5, $6, $7)
	`, uuid.New(), transactionID, accountID, entryType, amount.String(), balanceAfter.String(), time.Now())

	return err
}

func (s *TransferService) findByIdempotencyKey(key string) (*Transfer, error) {
	transfer := &Transfer{}
	var amount float64

	err := s.db.QueryRow(`
		SELECT id, idempotency_key, status, created_at, amount, currency
		FROM transactions
		WHERE idempotency_key = $1 AND type = 'TRANSFER'
	`, key).Scan(
		&transfer.ID,
		&transfer.IdempotencyKey,
		&transfer.Status,
		&transfer.CreatedAt,
		&amount,
		&transfer.Currency,
	)

	if err == sql.ErrNoRows {
		return nil, err
	}
	if err != nil {
		return nil, err
	}

	transfer.Amount = decimal.NewFromFloat(amount)
	return transfer, nil
}
