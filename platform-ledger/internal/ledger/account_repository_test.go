package ledger

import (
	"database/sql"
	"testing"

	"github.com/google/uuid" 
	_ "github.com/lib/pq"
	"github.com/shopspring/decimal"
	"github.com/stretchr/testify/assert"
	"github.com/stretchr/testify/require"
)

func setupTestDB(t *testing.T) *sql.DB {
	db, err := sql.Open("postgres", "host=localhost port=5432 user=ledger_user password=ledger_secret_2026 dbname=ledger sslmode=disable")
	require.NoError(t, err)
	
	err = db.Ping()
	require.NoError(t, err)
	
	return db
}

func cleanupTestData(t *testing.T, db *sql.DB) {
	_, err := db.Exec("DELETE FROM entries")
	require.NoError(t, err)
	
	_, err = db.Exec("DELETE FROM transactions")
	require.NoError(t, err)
	
	_, err = db.Exec("DELETE FROM accounts")
	require.NoError(t, err)
}

func TestAccountRepository_Create(t *testing.T) {
	db := setupTestDB(t)
	defer func() { _ = db.Close() }()

        cleanupTestData(t, db)
	defer cleanupTestData(t, db)
	
	repo := NewAccountRepository(db)
	
	t.Run("create user account successfully", func(t *testing.T) {
		account, err := repo.Create("test_user_001", AccountTypeUser, "XAF")
		
		require.NoError(t, err)
		assert.NotNil(t, account)
		assert.Equal(t, "test_user_001", account.ExternalID)
		assert.Equal(t, AccountTypeUser, account.Type)
		assert.Equal(t, "XAF", account.Currency)
		assert.True(t, account.Balance.Equal(decimal.Zero))
		assert.True(t, account.AvailableBalance.Equal(decimal.Zero))
		assert.Equal(t, "ACTIVE", account.Status)
	})
	
	t.Run("fail on duplicate external_id", func(t *testing.T) {
		_, err := repo.Create("test_user_002", AccountTypeUser, "XAF")
		require.NoError(t, err)
		
		// Try to create again with same external_id
		_, err = repo.Create("test_user_002", AccountTypeUser, "XAF")
		assert.Error(t, err)
	})
	
	t.Run("create merchant account", func(t *testing.T) {
	      t.Cleanup(func() { cleanupTestData(t, db) })
		
		account, err := repo.Create("merchant_001", AccountTypeMerchant, "XAF")
		
		require.NoError(t, err)
		assert.Equal(t, AccountTypeMerchant, account.Type)
	})
}

func TestAccountRepository_GetByID(t *testing.T) {
	db := setupTestDB(t)
	defer func() { _ = db.Close() }()
	defer cleanupTestData(t, db)
	
	repo := NewAccountRepository(db)
	
	t.Run("get existing account", func(t *testing.T) {
		created, err := repo.Create("test_user_003", AccountTypeUser, "XAF")
		require.NoError(t, err)
		
		fetched, err := repo.GetByID(created.ID)
		
		require.NoError(t, err)
		assert.Equal(t, created.ID, fetched.ID)
		assert.Equal(t, created.ExternalID, fetched.ExternalID)
	})
	
	t.Run("fail on non-existent account", func(t *testing.T) {
		randomUUID := "00000000-0000-0000-0000-000000000000"
		id, _ := uuid.Parse(randomUUID)
		
		_, err := repo.GetByID(id)
		assert.Error(t, err)
	})
}

func TestAccountRepository_GetByExternalID(t *testing.T) {
	db := setupTestDB(t)
	defer func() { _ = db.Close() }()
	defer cleanupTestData(t, db)
	
	repo := NewAccountRepository(db)
	
	t.Run("get by external_id", func(t *testing.T) {
		created, err := repo.Create("test_user_004", AccountTypeUser, "XAF")
		require.NoError(t, err)
		
		fetched, err := repo.GetByExternalID("test_user_004")
		
		require.NoError(t, err)
		assert.Equal(t, created.ID, fetched.ID)
	})
}
