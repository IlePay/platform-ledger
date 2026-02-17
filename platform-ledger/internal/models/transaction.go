package models

import (
	"time"

	"github.com/google/uuid"
	"github.com/shopspring/decimal"
)

type TransactionType string

const (
	TransactionTypeTransfer TransactionType = "TRANSFER"
	TransactionTypePayment  TransactionType = "PAYMENT"
	TransactionTypeRefund   TransactionType = "REFUND"
)

type TransactionStatus string

const (
	TransactionStatusPending   TransactionStatus = "PENDING"
	TransactionStatusCompleted TransactionStatus = "COMPLETED"
	TransactionStatusFailed    TransactionStatus = "FAILED"
)

type Transaction struct {
	ID             uuid.UUID         `json:"id"`
	IdempotencyKey string            `json:"idempotency_key"`
	Type           TransactionType   `json:"type"`
	Amount         decimal.Decimal   `json:"amount"`
	Currency       string            `json:"currency"`
	Status         TransactionStatus `json:"status"`
	Metadata       map[string]any    `json:"metadata,omitempty"`
	CreatedAt      time.Time         `json:"created_at"`
}
