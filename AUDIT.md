# Technical Audit Report

This document explains the main security measures and architectural decisions implemented in the Fertilizer Subsidy Management System. It also describes some limitations of the current implementation.

Please note that the payment process currently uses a local payment simulator for development and assessment purposes. It is not yet connected to a live payment gateway.

## Module 4: Security and Concurrency

### 1. QR Code Security and Tamper Validation

The system uses `TransactionQrService` to generate and validate signed QR codes. It uses HMAC-SHA256 with the Laravel application key to help detect changes to the QR data.

During checkout review, the system verifies the signature using `hash_equals` and checks whether the beneficiary ID and total amount match the records in the database. This helps prevent someone from changing the beneficiary or payment amount in the QR code.

The webhook also has its own signature verification using `PAYMENT_WEBHOOK_SECRET` to check whether the incoming payment event has a valid signature.

### 2. QR Expiration and Transaction Reference

Each QR code expires 15 minutes after the order is created. The system stores the expiration time in `qr_expires_at` and rejects expired QR codes during verification.

The QR also contains a signed reference in the format `BeneficiaryID|UUID|Timestamp`. The system reconstructs and validates this reference using the database records to check whether it matches the transaction.

These checks help prevent the use of expired QR codes and the modification of transaction details.

### 3. Handling Simultaneous Transactions

The system uses database transactions and `lockForUpdate()` when creating orders and processing settlements. It locks the relevant farmer, merchant, transaction, and product records while checking balances and stock.

For example, if two transactions attempt to purchase the same remaining stock at nearly the same time, the database locks help prevent both transactions from using the same available quantity.

The system then updates the product stock, farmer subsidy balance, merchant wallet, and transaction status within a single database transaction. If an operation fails, the related database changes can be rolled back together.

For production, the database must support row-level locking, such as MySQL with InnoDB.

### 4. Preventing Duplicate Payment Processing

Payment providers may send the same webhook more than once, especially when retrying after a timeout. To handle this, the system checks the event ID in `payment_logs` before processing a settlement and checks again after locking the transaction.

The database also enforces a unique constraint on the event identifier. This helps prevent the same payment event from updating the farmer's balance, merchant wallet, or product stock multiple times.

The payment-status polling process uses the same settlement service and a stable event ID, so both processing methods follow the same settlement rules.

## Module 6: Architecture and Design Decisions

### 1. Why Use Exact Beneficiary ID Lookup?

The POS requires the merchant to enter the exact beneficiary ID instead of selecting a farmer through search or autocomplete.

This approach reduces the chance of selecting the wrong farmer and limits unnecessary exposure of other beneficiary records. The system also checks the farmer's verification status and eligibility on the server when creating an order.

The exact lookup is an additional safeguard, not a replacement for authentication and authorization checks.

### 2. Separating Payment Processing from Business Logic

The system uses `PaymentSettlementService` to handle the main settlement process, including payment validation and the related database updates.

The webhook and payment-status polling routes both use this shared service. This avoids maintaining separate settlement logic for each payment-processing path.

At present, the project uses a local payment simulator. If a real payment gateway is introduced later, a provider-specific adapter will be needed to handle its checkout process, payment status, and webhook format while keeping the existing settlement rules reusable.

### 3. Handling Interrupted Payment Redirects

The system does not rely only on the user's return to the payment page to determine whether a payment was successful.

Instead, the webhook can report the payment result to the server. The authenticated polling endpoint can also check the payment status and send a paid result through the same settlement service if the browser loses connectivity or the redirect is interrupted.

The shared settlement process, transaction locks, and event ID checks help prevent duplicate processing when both methods attempt to handle the same payment.

One limitation is that polling currently checks the local simulator. A production integration would need to query the actual payment provider through a trusted server-to-server status endpoint.

## Audit Notes and Limitations

The payment-log migrations first create a nullable unique `gateway_event_id` column and later rename it to `event_id` to match the model and settlement code. For a fresh installation, all migrations must run in the correct order.

The current implementation demonstrates the intended payment and security workflows, but it does not prove that a live payment gateway or real-money settlement is operational.

The `APP_KEY` and `PAYMENT_WEBHOOK_SECRET` must remain private and be configured securely for each environment.
