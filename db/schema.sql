-- Create database if not exists
CREATE DATABASE IF NOT EXISTS portfolio_db
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE portfolio_db;

-- Transactions table
CREATE TABLE IF NOT EXISTS transactions (
    id VARCHAR(36) PRIMARY KEY,  -- UUID from JS
    date DATE NOT NULL,
    provider VARCHAR(50) NOT NULL,
    type VARCHAR(20) NOT NULL,
    symbol VARCHAR(20),          -- NULL allowed for DEPOSIT/WITHDRAW
    qty DECIMAL(20,8),          -- NULL allowed for non-trade tx
    price DECIMAL(20,8),        -- NULL allowed for non-trade tx
    ccy VARCHAR(3) NOT NULL DEFAULT 'EUR',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_symbol_date (symbol, date),  -- For quick symbol history
    INDEX idx_type_date (type, date),      -- For filtering by type
    INDEX idx_date (date)                  -- For date range queries
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Transaction notes table
CREATE TABLE IF NOT EXISTS transaction_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id VARCHAR(36) NOT NULL,
    text TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (transaction_id) REFERENCES transactions(id)
        ON DELETE CASCADE,                 -- Delete notes when tx deleted
    INDEX idx_transaction (transaction_id) -- For quick note lookup
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optional: Create triggers for audit logging if needed later
-- CREATE TABLE IF NOT EXISTS audit_log...