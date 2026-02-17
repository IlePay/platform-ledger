package ledger

import (
	"testing"

	"github.com/google/uuid"
	"github.com/shopspring/decimal"
)

func BenchmarkTransferService_Execute(b *testing.B) {
	db := setupTestDB(&testing.T{})
	defer func() { _ = db.Close() }()
	
	service := NewTransferService(db)
	repo := NewAccountRepository(db)
	
	// Setup: Create 2 accounts
	alice, _ := repo.Create("bench_alice", AccountTypeUser, "XAF")
	bob, _ := repo.Create("bench_bob", AccountTypeUser, "XAF")
	
	// Credit Alice with 1,000,000 XAF
       _, _ = db.Exec("UPDATE accounts SET balance = 1000000, available_balance = 1000000 WHERE id = $1", alice.ID)
	
	b.ResetTimer()
	
	for i := 0; i < b.N; i++ {
		_, _ = service.Execute(TransferRequest{
	IdempotencyKey: uuid.New().String(),
	FromAccountID:  alice.ID,
	ToAccountID:    bob.ID,
	Amount:         decimal.NewFromInt(100),
	Currency:       "XAF",
})
	}
	
	// Cleanup
        _, _ = db.Exec("DELETE FROM entries")
        _, _ = db.Exec("DELETE FROM transactions")
        _, _ = db.Exec("DELETE FROM accounts")
}
