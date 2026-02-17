package ledger

import (
	"sync"
	"testing"

	"github.com/google/uuid"
	"github.com/shopspring/decimal"
	"github.com/stretchr/testify/assert"
	"github.com/stretchr/testify/require"
)

func TestTransferService_Execute(t *testing.T) {
	db := setupTestDB(t)
	defer func() { _ = db.Close() }()
	defer cleanupTestData(t, db)
	
	service := NewTransferService(db)
	repo := NewAccountRepository(db)
	
	t.Run("successful transfer", func(t *testing.T) {
		// Setup: Create 2 accounts
		alice, err := repo.Create("alice", AccountTypeUser, "XAF")
		require.NoError(t, err)
		
		bob, err := repo.Create("bob", AccountTypeUser, "XAF")
		require.NoError(t, err)
		
		// Credit Alice with 10,000 XAF
		_, err = db.Exec("UPDATE accounts SET balance = 10000, available_balance = 10000 WHERE id = $1", alice.ID)
		require.NoError(t, err)
		
		// Execute transfer: Alice → Bob : 5000 XAF
		transfer, err := service.Execute(TransferRequest{
			IdempotencyKey: "test_transfer_001",
			FromAccountID:  alice.ID,
			ToAccountID:    bob.ID,
			Amount:         decimal.NewFromInt(5000),
			Currency:       "XAF",
		})
		
		require.NoError(t, err)
		assert.NotNil(t, transfer)
		assert.Equal(t, "test_transfer_001", transfer.IdempotencyKey)
		assert.True(t, transfer.Amount.Equal(decimal.NewFromInt(5000)))
		assert.Equal(t, "COMPLETED", transfer.Status)
		
		// Verify balances
		aliceAfter, _ := repo.GetByID(alice.ID)
		bobAfter, _ := repo.GetByID(bob.ID)
		
		assert.True(t, aliceAfter.Balance.Equal(decimal.NewFromInt(5000)))
		assert.True(t, bobAfter.Balance.Equal(decimal.NewFromInt(5000)))
		
		// Verify double-entry
		var entryCount int
		_ = db.QueryRow("SELECT COUNT(*) FROM entries WHERE transaction_id = $1", transfer.ID).Scan(&entryCount)
		assert.Equal(t, 2, entryCount) // 1 DEBIT + 1 CREDIT
	})
	
	t.Run("insufficient funds", func(t *testing.T) {
		charlie, err := repo.Create("charlie", AccountTypeUser, "XAF")
		require.NoError(t, err)
		
		david, err := repo.Create("david", AccountTypeUser, "XAF")
		require.NoError(t, err)
		
		// Charlie has 0 balance
		_, err = service.Execute(TransferRequest{
			IdempotencyKey: "test_transfer_002",
			FromAccountID:  charlie.ID,
			ToAccountID:    david.ID,
			Amount:         decimal.NewFromInt(100),
			Currency:       "XAF",
		})
		
		assert.Error(t, err)
		assert.Contains(t, err.Error(), "insufficient funds")
	})
	
	t.Run("negative amount rejected", func(t *testing.T) {
		eve, err := repo.Create("eve", AccountTypeUser, "XAF")
		require.NoError(t, err)
		
		frank, err := repo.Create("frank", AccountTypeUser, "XAF")
		require.NoError(t, err)
		
		_, err = service.Execute(TransferRequest{
			IdempotencyKey: "test_transfer_003",
			FromAccountID:  eve.ID,
			ToAccountID:    frank.ID,
			Amount:         decimal.NewFromInt(-100),
			Currency:       "XAF",
		})
		
		assert.Error(t, err)
		assert.Contains(t, err.Error(), "amount must be positive")
	})
}

func TestTransferService_Idempotency(t *testing.T) {
	db := setupTestDB(t)
	defer func() { _ = db.Close() }()
	defer cleanupTestData(t, db)
	
	service := NewTransferService(db)
	repo := NewAccountRepository(db)
	
	t.Run("same idempotency_key returns same transaction", func(t *testing.T) {
		// Create accounts
		grace, err := repo.Create("grace", AccountTypeUser, "XAF")
		require.NoError(t, err)
		
		henry, err := repo.Create("henry", AccountTypeUser, "XAF")
		require.NoError(t, err)
		
		// Credit Grace
		_, err = db.Exec("UPDATE accounts SET balance = 10000, available_balance = 10000 WHERE id = $1", grace.ID)
		require.NoError(t, err)
		
		idempotencyKey := "idempotency_test_001"
		
		// First transfer
		transfer1, err := service.Execute(TransferRequest{
			IdempotencyKey: idempotencyKey,
			FromAccountID:  grace.ID,
			ToAccountID:    henry.ID,
			Amount:         decimal.NewFromInt(3000),
			Currency:       "XAF",
		})
		require.NoError(t, err)
		
		// Second transfer with SAME idempotency key
		transfer2, err := service.Execute(TransferRequest{
			IdempotencyKey: idempotencyKey,
			FromAccountID:  grace.ID,
			ToAccountID:    henry.ID,
			Amount:         decimal.NewFromInt(3000),
			Currency:       "XAF",
		})
		require.NoError(t, err)
		
		// CRITICAL: Must return same transaction ID
		assert.Equal(t, transfer1.ID, transfer2.ID)
		
		// CRITICAL: Balances should NOT change
		graceAfter, _ := repo.GetByID(grace.ID)
		henryAfter, _ := repo.GetByID(henry.ID)
		
		assert.True(t, graceAfter.Balance.Equal(decimal.NewFromInt(7000))) // 10000 - 3000
		assert.True(t, henryAfter.Balance.Equal(decimal.NewFromInt(3000)))
		
		// Verify only ONE transaction exists
		var txCount int
		_ = db.QueryRow("SELECT COUNT(*) FROM transactions WHERE idempotency_key = $1", idempotencyKey).Scan(&txCount)
		assert.Equal(t, 1, txCount)
	})
}

func TestTransferService_Concurrency(t *testing.T) {
	db := setupTestDB(t)
	defer func() { _ = db.Close() }()
	defer cleanupTestData(t, db)
	
	service := NewTransferService(db)
	repo := NewAccountRepository(db)
	
	t.Run("concurrent transfers maintain consistency", func(t *testing.T) {
		// Create accounts
		ivan, err := repo.Create("ivan", AccountTypeUser, "XAF")
		require.NoError(t, err)
		
		judy, err := repo.Create("judy", AccountTypeUser, "XAF")
		require.NoError(t, err)
		
		// Credit Ivan with 10,000 XAF
		_, err = db.Exec("UPDATE accounts SET balance = 10000, available_balance = 10000 WHERE id = $1", ivan.ID)
		require.NoError(t, err)
		
		// Launch 10 concurrent transfers of 1000 XAF each
		var wg sync.WaitGroup
		successCount := 0
		var mu sync.Mutex
		
		for i := 0; i < 10; i++ {
			wg.Add(1)
			go func(index int) {
				defer wg.Done()
				
				_, err := service.Execute(TransferRequest{
					IdempotencyKey: uuid.New().String(),
					FromAccountID:  ivan.ID,
					ToAccountID:    judy.ID,
					Amount:         decimal.NewFromInt(1000),
					Currency:       "XAF",
				})
				
				if err == nil {
					mu.Lock()
					successCount++
					mu.Unlock()
				}
			}(i)
		}
		
		wg.Wait()
		
		// Only 10 transfers should succeed (10 * 1000 = 10000)
		assert.Equal(t, 10, successCount)
		
		// Verify final balances
		ivanAfter, _ := repo.GetByID(ivan.ID)
		judyAfter, _ := repo.GetByID(judy.ID)
		
		assert.True(t, ivanAfter.Balance.Equal(decimal.Zero))
		assert.True(t, judyAfter.Balance.Equal(decimal.NewFromInt(10000)))
		
		// CRITICAL: Total money in system preserved
		var totalMoney decimal.Decimal
		var total float64
		_ = db.QueryRow("SELECT SUM(balance) FROM accounts WHERE id IN ($1, $2)", ivan.ID, judy.ID).Scan(&total)
		totalMoney = decimal.NewFromFloat(total)
		
		assert.True(t, totalMoney.Equal(decimal.NewFromInt(10000)))
	})
}
