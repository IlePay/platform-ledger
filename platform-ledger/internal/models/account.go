package models

import (
	"time"

	"github.com/google/uuid"
	"github.com/shopspring/decimal"
)

type AccountType string

const (
	AccountTypeUser       AccountType = "USER"
	AccountTypeMerchant   AccountType = "MERCHANT"
	AccountTypeSystem     AccountType = "SYSTEM"
	AccountTypeFee        AccountType = "FEE"
	AccountTypeSettlement AccountType = "SETTLEMENT"
)

type AccountStatus string

const (
	AccountStatusActive AccountStatus = "ACTIVE"
	AccountStatusFrozen AccountStatus = "FROZEN"
	AccountStatusClosed AccountStatus = "CLOSED"
)

type Account struct {
	ID               uuid.UUID       `json:"id"`
	ExternalID       string          `json:"external_id"`
	Type             AccountType     `json:"type"`
	Currency         string          `json:"currency"`
	Balance          decimal.Decimal `json:"balance"`
	AvailableBalance decimal.Decimal `json:"available_balance"`
	Status           AccountStatus   `json:"status"`
	CreatedAt        time.Time       `json:"created_at"`
	UpdatedAt        time.Time       `json:"updated_at"`
	Version          int             `json:"version"`
}
