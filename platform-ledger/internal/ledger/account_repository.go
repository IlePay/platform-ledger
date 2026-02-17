package ledger

import (
	"database/sql"
	"fmt"
	"time"

	"github.com/google/uuid"
	"github.com/shopspring/decimal"
)

type AccountType string

const (
	AccountTypeUser     AccountType = "USER"
	AccountTypeMerchant AccountType = "MERCHANT"
	AccountTypeSystem   AccountType = "SYSTEM"
)

type Account struct {
	ID               uuid.UUID       `json:"id"`
	ExternalID       string          `json:"external_id"`
	Type             AccountType     `json:"type"`
	Currency         string          `json:"currency"`
	Balance          decimal.Decimal `json:"balance"`
	AvailableBalance decimal.Decimal `json:"available_balance"`
	Status           string          `json:"status"`
	CreatedAt        time.Time       `json:"created_at"`
	Version          int             `json:"version"`
}

type AccountRepository struct {
	db *sql.DB
}

func NewAccountRepository(db *sql.DB) *AccountRepository {
	return &AccountRepository{db: db}
}

func (r *AccountRepository) Create(externalID string, accountType AccountType, currency string) (*Account, error) {
	account := &Account{
		ID:               uuid.New(),
		ExternalID:       externalID,
		Type:             accountType,
		Currency:         currency,
		Balance:          decimal.Zero,
		AvailableBalance: decimal.Zero,
		Status:           "ACTIVE",
		CreatedAt:        time.Now(),
		Version:          0,
	}

	query := `
		INSERT INTO accounts (id, external_id, type, currency, balance, available_balance, status, created_at, version)
		VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9)
		RETURNING id, created_at
	`

	err := r.db.QueryRow(
		query,
		account.ID,
		account.ExternalID,
		account.Type,
		account.Currency,
		account.Balance,
		account.AvailableBalance,
		account.Status,
		account.CreatedAt,
		account.Version,
	).Scan(&account.ID, &account.CreatedAt)

	if err != nil {
		return nil, fmt.Errorf("failed to create account: %w", err)
	}

	return account, nil
}

func (r *AccountRepository) GetByID(id uuid.UUID) (*Account, error) {
	account := &Account{}

	query := `
		SELECT id, external_id, type, currency, balance, available_balance, status, created_at, version
		FROM accounts
		WHERE id = $1
	`

	err := r.db.QueryRow(query, id).Scan(
		&account.ID,
		&account.ExternalID,
		&account.Type,
		&account.Currency,
		&account.Balance,
		&account.AvailableBalance,
		&account.Status,
		&account.CreatedAt,
		&account.Version,
	)

	if err == sql.ErrNoRows {
		return nil, fmt.Errorf("account not found")
	}
	if err != nil {
		return nil, fmt.Errorf("failed to get account: %w", err)
	}

	return account, nil
}

func (r *AccountRepository) GetByExternalID(externalID string) (*Account, error) {
	account := &Account{}

	query := `
		SELECT id, external_id, type, currency, balance, available_balance, status, created_at, version
		FROM accounts
		WHERE external_id = $1
	`

	err := r.db.QueryRow(query, externalID).Scan(
		&account.ID,
		&account.ExternalID,
		&account.Type,
		&account.Currency,
		&account.Balance,
		&account.AvailableBalance,
		&account.Status,
		&account.CreatedAt,
		&account.Version,
	)

	if err == sql.ErrNoRows {
		return nil, fmt.Errorf("account not found")
	}
	if err != nil {
		return nil, fmt.Errorf("failed to get account: %w", err)
	}

	return account, nil
}
