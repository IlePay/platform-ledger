package main

// @title Platform Ledger API
// @version 0.1.0
// @description Double-entry ledger and transaction processing engine
// @termsOfService https://platform.com/terms

// @contact.name API Support
// @contact.url https://platform.com/support
// @contact.email support@platform.com

// @license.name MIT
// @license.url https://opensource.org/licenses/MIT

// @host localhost:8082
// @BasePath /api/v1
// @schemes http https

import (
	"database/sql"
	"encoding/json"
	"fmt"
	"log"
	"net/http"
	"os"

	"github.com/google/uuid"
	"github.com/gorilla/mux"
	_ "github.com/lib/pq"
	"github.com/shopspring/decimal"
	
	
	
	"github.com/IlePay/platform-ledger/internal/ledger"

)

var db *sql.DB
var accountRepo *ledger.AccountRepository
var transferService *ledger.TransferService
type DBConfig struct {
	Host     string
	Port     string
	User     string
	Password string
	DBName   string
	SSLMode  string
}

func main() {

	// Configuration database
	dbConfig := DBConfig{
		Host:     getEnv("DB_HOST", "localhost"),
		Port:     getEnv("DB_PORT", "5432"),
		User:     getEnv("DB_USER", "ledger_user"),
		Password: getEnv("DB_PASSWORD", "ledger_secret_2026"),
		DBName:   getEnv("DB_NAME", "ledger"),
		SSLMode:  getEnv("DB_SSLMODE", "disable"),
	}

	// Connexion PostgreSQL
	var err error
	db, err = connectDB(dbConfig)
	if err != nil {
		log.Fatalf("❌ Database connection failed: %v", err)
	}
	defer func() {
	if err := db.Close(); err != nil {
		log.Printf("Error closing database: %v", err)
	}
}()

	log.Println("✅ Connected to PostgreSQL")

	// Router
	r := mux.NewRouter()
	r.HandleFunc("/health", healthCheck).Methods("GET")
	r.HandleFunc("/api/v1/accounts", createAccount).Methods("POST")
r.HandleFunc("/api/v1/accounts/{id}", getAccountByID).Methods("GET")
r.HandleFunc("/api/v1/transfers", createTransfer).Methods("POST")

// Routes existantes
r.HandleFunc("/health", healthCheck).Methods("GET")
r.HandleFunc("/api/v1/accounts", createAccount).Methods("POST")
r.HandleFunc("/api/v1/accounts/{id}", getAccountByID).Methods("GET")
r.HandleFunc("/api/v1/transfers", createTransfer).Methods("POST")

// Swagger UI

accountRepo = ledger.NewAccountRepository(db)
transferService = ledger.NewTransferService(db)
	port := getEnv("PORT", "8082")
	fmt.Printf("🚀 Ledger API starting on port :%s\n", port)
	log.Fatal(http.ListenAndServe(":"+port, r))
}

func connectDB(cfg DBConfig) (*sql.DB, error) {
	dsn := fmt.Sprintf(
		"host=%s port=%s user=%s password=%s dbname=%s sslmode=%s",
		cfg.Host, cfg.Port, cfg.User, cfg.Password, cfg.DBName, cfg.SSLMode,
	)

	db, err := sql.Open("postgres", dsn)
	if err != nil {
		return nil, fmt.Errorf("failed to open database: %w", err)
	}

	db.SetMaxOpenConns(25)
	db.SetMaxIdleConns(5)

	if err := db.Ping(); err != nil {
		return nil, fmt.Errorf("failed to ping database: %w", err)
	}

	return db, nil
}

// HealthCheck godoc
// @Summary Health check endpoint
// @Description Get API health status and database connection status
// @Tags system
// @Accept json
// @Produce json
// @Success 200 {object} map[string]string "status, service, version, database"
// @Router /health [get]

func healthCheck(w http.ResponseWriter, r *http.Request) {
	err := db.Ping()
	dbStatus := "ok"
	if err != nil {
		dbStatus = "error"
		log.Printf("DB ping error: %v", err)
	}

	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(http.StatusOK)
	if err := json.NewEncoder(w).Encode(map[string]string{
		"status":   "ok",
		"service":  "ledger-api",
		"version":  "0.1.0",
		"database": dbStatus,
	}); err != nil {  // ← Assure-toi que les accolades sont correctes
		log.Printf("Error encoding response: %v", err)
	}
}

// CreateAccount godoc
// @Summary Create a new account
// @Description Create a new ledger account for a user or merchant
// @Tags accounts
// @Accept json
// @Produce json
// @Param request body object{external_id=string,type=string,currency=string} true "Account creation request"
// @Success 201 {object} ledger.Account
// @Failure 400 {object} map[string]string
// @Failure 500 {object} map[string]string
// @Router /accounts [post]

func createAccount(w http.ResponseWriter, r *http.Request) {
	var req struct {
		ExternalID string `json:"external_id"`
		Type       string `json:"type"`
		Currency   string `json:"currency"`
	}

	if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
		w.Header().Set("Content-Type", "application/json")
		w.WriteHeader(http.StatusBadRequest)
		_ = json.NewEncoder(w).Encode(map[string]string{
			"error": "Invalid request body",
		})
		return
	}

	// Validation
	if req.ExternalID == "" {
		w.Header().Set("Content-Type", "application/json")
		w.WriteHeader(http.StatusBadRequest)
		_ = json.NewEncoder(w).Encode(map[string]string{
			"error": "external_id is required",
		})
		return
	}

	if req.Type == "" {
		req.Type = "USER"
	}

	if req.Currency == "" {
		req.Currency = "XAF"
	}

	// Créer le compte
	account, err := accountRepo.Create(req.ExternalID, ledger.AccountType(req.Type), req.Currency)
	if err != nil {
		log.Printf("Error creating account: %v", err)
		w.Header().Set("Content-Type", "application/json")
		w.WriteHeader(http.StatusInternalServerError)
		_ = json.NewEncoder(w).Encode(map[string]string{
			"error": "Failed to create account",
		})
		return
	}

	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(http.StatusCreated)
	_ = json.NewEncoder(w).Encode(account)
}

func getEnv(key, defaultValue string) string {
	if value := os.Getenv(key); value != "" {
		return value
	}
	return defaultValue
}

// GetAccountByID godoc
// @Summary Get account by ID
// @Description Retrieve account details by UUID
// @Tags accounts
// @Accept json
// @Produce json
// @Param id path string true "Account UUID"
// @Success 200 {object} ledger.Account
// @Failure 400 {object} map[string]string
// @Failure 404 {object} map[string]string
// @Router /accounts/{id} [get]

func getAccountByID(w http.ResponseWriter, r *http.Request) {
	vars := mux.Vars(r)
	idStr := vars["id"]

	id, err := uuid.Parse(idStr)
	if err != nil {
		w.Header().Set("Content-Type", "application/json")
		w.WriteHeader(http.StatusBadRequest)
		_ = json.NewEncoder(w).Encode(map[string]string{
			"error": "Invalid account ID format",
		})
		return
	}

	account, err := accountRepo.GetByID(id)
	if err != nil {
		w.Header().Set("Content-Type", "application/json")
		w.WriteHeader(http.StatusNotFound)
		_ = json.NewEncoder(w).Encode(map[string]string{
			"error": "Account not found",
		})
		return
	}

	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(http.StatusOK)
	_ = json.NewEncoder(w).Encode(account)
}

// CreateTransfer godoc
// @Summary Create a transfer between accounts
// @Description Execute an atomic transfer with double-entry bookkeeping
// @Tags transfers
// @Accept json
// @Produce json
// @Param request body object{idempotency_key=string,from_account_id=string,to_account_id=string,amount=number,currency=string} true "Transfer request"
// @Success 201 {object} ledger.Transfer
// @Failure 400 {object} map[string]string "Invalid request or insufficient funds"
// @Failure 500 {object} map[string]string
// @Router /transfers [post]

func createTransfer(w http.ResponseWriter, r *http.Request) {
	var req struct {
		IdempotencyKey string  `json:"idempotency_key"`
		FromAccountID  string  `json:"from_account_id"`
		ToAccountID    string  `json:"to_account_id"`
		Amount         float64 `json:"amount"`
		Currency       string  `json:"currency"`
	}

	if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
		w.Header().Set("Content-Type", "application/json")
		w.WriteHeader(http.StatusBadRequest)
		_ = json.NewEncoder(w).Encode(map[string]string{"error": "Invalid request"})
		return
	}

	// Validation
	if req.IdempotencyKey == "" || req.FromAccountID == "" || req.ToAccountID == "" {
		w.Header().Set("Content-Type", "application/json")
		w.WriteHeader(http.StatusBadRequest)
		_ = json.NewEncoder(w).Encode(map[string]string{"error": "Missing required fields"})
		return
	}

	fromID, err := uuid.Parse(req.FromAccountID)
	if err != nil {
		w.Header().Set("Content-Type", "application/json")
		w.WriteHeader(http.StatusBadRequest)
		_ = json.NewEncoder(w).Encode(map[string]string{"error": "Invalid from_account_id"})
		return
	}

	toID, err := uuid.Parse(req.ToAccountID)
	if err != nil {
		w.Header().Set("Content-Type", "application/json")
		w.WriteHeader(http.StatusBadRequest)
		_ = json.NewEncoder(w).Encode(map[string]string{"error": "Invalid to_account_id"})
		return
	}

	if req.Currency == "" {
		req.Currency = "XAF"
	}

	// Exécuter le transfert
	transfer, err := transferService.Execute(ledger.TransferRequest{
		IdempotencyKey: req.IdempotencyKey,
		FromAccountID:  fromID,
		ToAccountID:    toID,
		Amount:         decimal.NewFromFloat(req.Amount),
		Currency:       req.Currency,
	})

	if err != nil {
		log.Printf("Transfer error: %v", err)
		w.Header().Set("Content-Type", "application/json")
		w.WriteHeader(http.StatusBadRequest)
		_ = json.NewEncoder(w).Encode(map[string]string{"error": err.Error()})
		return
	}

	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(http.StatusCreated)
	_ = json.NewEncoder(w).Encode(transfer)
}
