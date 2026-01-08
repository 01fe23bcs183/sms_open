# Smart School Management System - Payment Gateway Integration Prompts

This document contains detailed prompts for integrating payment gateways for fee collection using DevIn AI.

---

## 📋 How to Use This Guide

1. **Execute prompts in order** - Each prompt builds upon previous ones
2. **Read full context** - Understand purpose and integration before executing
3. **Verify completion** - Ensure each task is complete before moving to next
4. **Reference planning docs** - Use [`../plans/`](../plans/) for detailed architecture
5. **Reference backend prompts** - Use backend prompt files for controller implementation

---

## 🚀 Phase 1: Payment Gateway Setup (10 Prompts)

### Prompt 388: Install Razorpay Package

**Purpose**: Install Razorpay payment gateway package for fee collection.

**Functionality**: Enables online fee payments through Razorpay.

**How it Works**:
- Runs composer require command
- Installs razorpay/razorpay package
- Updates composer.json with razorpay dependency
- Installs latest stable version
- Shows installation success message
- Shows version installed

**Integration**:
- Used by payment controller
- Enables Razorpay payment integration
- Required for online fee collection
- Used in fee collection flow

**Execute**: Install Razorpay package using composer.

---

### Prompt 389: Configure Razorpay Environment Variables

**Purpose**: Configure Razorpay API keys and settings.

**Functionality**: Sets up Razorpay credentials in environment.

**How it Works**:
- Opens `.env` file
- Adds Razorpay configuration:
  - `RAZORPAY_KEY` (Razorpay Key ID)
  - `RAZORPAY_SECRET` (Razorpay Key Secret)
  - `RAZORPAY_CURRENCY` (INR or USD)
  - `RAZORPAY_MODE` (test or live)
  - `RAZORPAY_WEBHOOK_SECRET` (Webhook secret)
- Saves .env file
- Runs `php artisan config:clear` to cache new config

**Integration**:
- Used by payment service
- Enables Razorpay API calls
- Required for payment processing
- Used in fee collection controller

**Execute**: Configure Razorpay environment variables in `.env` file.

---

### Prompt 390: Create Payment Gateway Configuration

**Purpose**: Create payment gateway configuration file.

**Functionality**: Centralized payment gateway settings.

**How it Works**:
- Creates `config/payment-gateway.php`
- Defines payment gateway settings:
  - Default gateway (razorpay, cash, cheque, dd)
  - Razorpay settings (key, secret, currency, mode)
  - Offline methods settings (cash, cheque, dd)
  - Payment timeout settings
  - Webhook URL for Razorpay
  - Success/Redirect URLs
- Returns configuration array
- Uses environment variables for sensitive data

**Integration**:
- Used by payment service
- Centralized gateway configuration
- Enables multiple payment gateways
- Used in fee collection controller

**Execute**: Create payment gateway configuration file with all settings.

---

### Prompt 391: Create Payment Service

**Purpose**: Create payment service for handling payment processing.

**Functionality**: Provides payment processing methods for Razorpay and offline modes.

**How it Works**:
- Creates `app/Services/PaymentService.php`
- Implements payment methods:
  - `createRazorpayOrder($data)` - Creates Razorpay order
  - `verifyRazorpayPayment($razorpayOrderId, $razorpayPaymentId)` - Verifies Razorpay payment
  - `verifyRazorpaySignature($payload)` - Verifies Razorpay signature
  - `refundRazorpayPayment($transactionId, $amount)` - Processes Razorpay refund
  - `processCashPayment($data)` - Processes cash payment
  - `processChequePayment($data)` - Processes cheque payment
  - `processDdPayment($data)` - Processes DD payment
  - `generateReceipt($transaction)` - Generates payment receipt
- Uses payment gateway configuration
- Returns payment response
- Handles payment errors
- Logs payment attempts

**Integration**:
- Used by FeesTransactionController
- Used by payment controller
- Handles payment processing
- Integrates with Razorpay
- Used in fee collection flow

**Execute**: Create payment service with methods for all payment gateways.

---

### Prompt 392: Create Razorpay Controller

**Purpose**: Create controller for Razorpay payment handling.

**Functionality**: Handles Razorpay payment creation and verification.

**How it Works**:
- Creates `app/Http/Controllers/Payment/RazorpayController.php`
- Implements methods:
  - `createOrder(Request $request)` - Creates Razorpay order
    - Validates request data (amount, student_id, fee_ids)
    - Creates Razorpay order using payment service
    - Returns order ID and payment options
  - `verifyPayment(Request $request)` - Verifies Razorpay payment
    - Validates payment signature
    - Verifies payment with Razorpay API
    - Updates fee transaction status
    - Creates payment receipt
    - Returns success/error response
  - `webhook(Request $request)` - Handles Razorpay webhooks
    - Validates webhook signature
    - Updates transaction status
    - Sends notifications
    - Returns 200 OK
- Uses PaymentService for payment processing
- Uses FeesTransaction model for transaction updates
- Validates user permissions
- Returns JSON responses
- Handles errors gracefully

**Integration**:
- Used by payment views
- Integrates with PaymentService
- Updates FeesTransaction model
- Sends notifications
- Used in fee collection flow

**Execute**: Create Razorpay controller with order creation and verification methods.

---

### Prompt 393: Create Payment Controller

**Purpose**: Create main payment controller for handling all payment methods.

**Functionality**: Provides unified payment interface for Razorpay and offline methods.

**How it Works**:
- Creates `app/Http/Controllers/Payment/PaymentController.php`
- Implements methods:
  - `index()` - Shows payment options page
    - Lists available payment methods
    - Shows payment instructions
    - Supports RTL languages
  - `processPayment(Request $request)` - Processes payment
    - Validates payment method
    - Routes to Razorpay or offline handler
    - Handles cash/cheque payments
  - `success(Request $request, $method)` - Shows payment success page
    - Displays payment success message
    - Shows payment details
    - Provides receipt download
  - `failure(Request $request, $method)` - Shows payment failure page
    - Displays payment failure message
    - Shows error details
    - Provides retry option
  - `cancel(Request $request)` - Handles payment cancellation
    - Updates transaction status to cancelled
    - Redirects to fee page
  - `webhook(Request $request)` - Handles Razorpay webhook
- Uses PaymentService for payment processing
- Uses FeesTransaction model for transaction updates
- Validates user permissions
- Returns JSON responses for AJAX requests
- Handles errors gracefully

**Integration**:
- Used by payment views
- Integrates with all payment gateway controllers
- Updates FeesTransaction model
- Used in fee collection flow
- Provides unified payment interface

**Execute**: Create payment controller with methods for all payment methods.

---

### Prompt 394: Create Payment Views

**Purpose**: Create payment views for payment processing UI.

**Functionality**: Provides UI for payment selection and processing.

**How it Works**:
- Creates `resources/views/payment/index.blade.php`
- Extends app layout
- Shows payment options:
  - Online payment gateway (Razorpay)
  - Offline payment methods (Cash, Cheque, DD)
- Shows payment instructions for each gateway
- Shows payment fees (if any)
- Shows payment processing time estimate
- Shows payment support contact
- Uses Bootstrap 5 card component for each gateway
- Shows gateway logos and icons
- Supports RTL languages
- Responsive design
- Shows loading state during payment processing
- Shows success/error messages

**Integration**:
- Uses PaymentController index method
- Links to payment processing
- Used in fee collection flow
- Provides payment gateway selection

**Execute**: Create payment index view with all payment options.

---

### Prompt 395: Create Razorpay Payment View

**Purpose**: Create Razorpay payment processing view.

**Functionality**: Provides UI for Razorpay payment.

**How it Works**:
- Creates `resources/views/payment/razorpay.blade.php`
- Extends app layout
- Shows payment form with:
  - Student details (name, class, roll number)
  - Fee details (fee types, amounts, total)
  - Payment amount
  - Payment button with loading state
- Shows payment instructions
- Shows Razorpay logo
- Shows payment security notice
- Includes Razorpay SDK
- Implements Razorpay checkout
- Handles payment success/failure
- Shows loading spinner during payment
- Shows success/error messages
- Redirects to success/failure page after payment
- Supports RTL languages
- Responsive design

**Integration**:
- Uses RazorpayController createOrder method
- Integrates with Razorpay SDK
- Redirects to success/failure page
- Used in fee collection flow

**Execute**: Create Razorpay payment view with checkout integration.

---

### Prompt 396: Create Payment Success View

**Purpose**: Create payment success view with receipt.

**Functionality**: Shows payment success with receipt download option.

**How it Works**:
- Creates `resources/views/payment/success.blade.php`
- Extends app layout
- Shows success message with icon
- Shows payment details:
  - Transaction ID
  - Payment date
  - Payment method
  - Payment amount
  - Student details
  - Fee details
- Shows receipt preview
- Shows "Download Receipt" button
- Shows "Print Receipt" button
- Shows "Send Receipt" button (email/SMS)
- Shows "Back to Dashboard" button
- Shows "View Fees" button
- Uses Bootstrap 5 alert component for success message
- Supports RTL languages
- Responsive design
- Shows loading state during receipt generation

**Integration**:
- Uses PaymentController success method
- Links to receipt download
- Links to print receipt
- Links to send receipt via email/SMS
- Used after successful payment

**Execute**: Create payment success view with receipt options.

---

### Prompt 397: Create Payment Failure View

**Purpose**: Create payment failure view with retry option.

**Functionality**: Shows payment failure with retry option.

**How it Works**:
- Creates `resources/views/payment/failure.blade.php`
- Extends app layout
- Shows failure message with icon
- Shows payment details:
  - Transaction ID (if available)
  - Payment date
  - Payment method
  - Payment amount
  - Student details
  - Fee details
- Shows error message
- Shows "Retry Payment" button
- Shows "Try Different Method" button
- Shows "Contact Support" button
- Shows "Back to Fees" button
- Uses Bootstrap 5 alert component for failure message
- Supports RTL languages
- Responsive design
- Shows error details

**Integration**:
- Uses PaymentController failure method
- Links to retry payment
- Links to payment options
- Links to support contact
- Used after failed payment

**Execute**: Create payment failure view with retry options.

---

## 🚀 Phase 2: Payment Routes (5 Prompts)

### Prompt 398: Define Payment Routes

**Purpose**: Create routes for payment processing.

**Functionality**: Provides routing for all payment operations.

**How it Works**:
- Opens `routes/web.php`
- Creates payment routes:
  - `/payment` (index)
  - `/payment/process` (process)
  - `/payment/success/{method}` (success)
  - `/payment/failure/{method}` (failure)
  - `/payment/cancel` (cancel)
  - `/payment/razorpay/create-order` (create-order)
  - `/payment/razorpay/verify` (verify)
  - `/payment/razorpay/webhook` (webhook)
  - `/payment/receipt/{id}` (receipt)
  - `/payment/receipt/{id}/download` (download)
  - `/payment/receipt/{id}/print` (print)
- Uses Route::prefix('payment')
- Uses Route::middleware(['auth'])
- Uses named routes for easy reference
- Includes CSRF protection
- Uses proper HTTP methods (GET, POST)
- Includes route model binding

**Integration**:
- Links to PaymentController
- Links to gateway controllers
- Used by payment views
- Used in fee collection flow
- Enables payment processing

**Execute**: Create all payment routes in `routes/web.php`.

---

### Prompt 399: Create Payment Webhook Routes

**Purpose**: Create webhook routes for payment gateways.

**Functionality**: Provides routes for payment gateway webhooks.

**How it Works**:
- Opens `routes/web.php`
- Creates webhook routes:
  - `/webhooks/razorpay` (razorpay webhook)
- Uses Route::prefix('webhooks')
- Uses Route::middleware(['api', 'throttle:60,1'])
- Uses Route::post() for all webhook routes
- Uses named routes
- Includes signature verification
- Handles webhook authentication
- Returns 200 OK for all webhooks
- Logs webhook requests
- Updates transaction status

**Integration**:
- Links to payment gateway controllers
- Used by payment gateways for notifications
- Updates transaction status
- Sends notifications
- Used in payment processing

**Execute**: Create webhook routes for all payment gateways.

---

### Prompt 400: Create API Payment Routes

**Purpose**: Create API routes for mobile app payment integration.

**Functionality**: Provides API endpoints for mobile app payments.

**How it Works**:
- Opens `routes/api.php`
- Creates API payment routes:
  - POST `/api/payment/methods` (list methods)
  - POST `/api/payment/create-order` (create Razorpay order)
  - POST `/api/payment/verify` (verify Razorpay payment)
  - POST `/api/payment/receipt/{id}` (get receipt)
  - POST `/api/payment/history` (payment history)
- Uses Route::prefix('api/payment')
- Uses Route::middleware(['auth:api', 'throttle:60,1'])
- Uses named routes
- Returns JSON responses
- Includes pagination
- Includes filtering and sorting
- Includes search functionality

**Integration**:
- Links to API payment controller
- Used by mobile app
- Provides payment API for mobile communication
- Enables mobile app payments

**Execute**: Create API payment routes in `routes/api.php`.

---

### Prompt 401: Create Receipt Routes

**Purpose**: Create routes for receipt generation and download.

**Functionality**: Provides routing for receipt operations.

**How it Works**:
- Opens `routes/web.php`
- Creates receipt routes:
  - `/payment/receipt/{id}` (show receipt)
  - `/payment/receipt/{id}/download` (download receipt)
  - `/payment/receipt/{id}/print` (print receipt)
  - `/payment/receipt/{id}/send` (send receipt)
- Uses Route::prefix('payment/receipt')
- Uses Route::middleware(['auth'])
- Uses named routes
- Includes route model binding
- Returns PDF file for download
- Returns HTML for print
- Sends email/SMS for send

**Integration**:
- Links to PaymentController
- Links to receipt service
- Used by payment views
- Used in fee collection flow
- Enables receipt generation and download

**Execute**: Create receipt routes in `routes/web.php`.

---

### Prompt 402: Create Payment History Routes

**Purpose**: Create routes for payment history.

**Functionality**: Provides routing for payment history operations.

**How it Works**:
- Opens `routes/web.php`
- Creates payment history routes:
  - `/payment/history` (index)
  - `/payment/history/{id}` (show)
  - `/payment/history/export` (export)
- Uses Route::prefix('payment/history')
- Uses Route::middleware(['auth', 'role:accountant'])
- Uses named routes
- Includes pagination
- Includes filtering and sorting
- Includes search functionality
- Includes export functionality

**Integration**:
- Links to PaymentController
- Links to FeesTransactionController
- Used by accountant panel
- Used for payment history tracking

**Execute**: Create payment history routes in `routes/web.php`.

---

### Prompt 403: Create Razorpay Signature Verification Helper

**Purpose**: Validate Razorpay signatures for checkout and webhooks.

**Functionality**: Centralizes signature verification for all Razorpay callbacks.

**How it Works**:
- Creates `app/Services/RazorpaySignatureService.php`
- Verifies checkout signature using order_id and payment_id
- Verifies webhook signature using payload hash
- Returns boolean and logs failures for audit
- Reused by RazorpayController and webhook handlers

**Integration**:
- Used by RazorpayController verify method
- Used by webhook processing jobs
- Prevents spoofed payment callbacks

**Execute**: Implement RazorpaySignatureService and wire into payment flow.

---

### Prompt 404: Store Razorpay Payloads and Webhook Logs

**Purpose**: Keep an audit trail of payment events.

**Functionality**: Persists Razorpay request/response payloads.

**How it Works**:
- Creates `payment_gateway_logs` table
- Stores event type, payload, signature, processed_at
- Links log to `fees_transactions` when possible
- Adds retention policy for log cleanup

**Integration**:
- Used by RazorpayController and webhook handlers
- Supports dispute resolution and audits
- Works with reconciliation reports

**Execute**: Create gateway log storage and write logs on each callback.

---

### Prompt 405: Add Payment Idempotency and Retry Handling

**Purpose**: Prevent duplicate charges and ensure safe retries.

**Functionality**: Adds idempotency keys and retry guards.

**How it Works**:
- Adds `idempotency_key` field to `fees_transactions`
- Generates key per payment attempt
- Stores Razorpay request/response payloads
- Blocks duplicate processing for same key
- Retries only when status is pending/failed

**Integration**:
- Used by PaymentService and controllers
- Prevents double charging on network errors
- Supports payment retry UI in frontend

**Execute**: Add idempotency tracking and retry guards to payment flow.

---

### Prompt 406: Implement Razorpay Settlement Reconciliation

**Purpose**: Match Razorpay settlements with internal records.

**Functionality**: Reconciles payments and posts to accounting ledger.

**How it Works**:
- Creates `PaymentReconciliationService`
- Pulls Razorpay settlement reports
- Matches by reference and amount
- Marks reconciled transactions
- Posts ledger entries for income accounts

**Integration**:
- Used by accountant reports
- Supports daily closing and audit
- Connects fees and accounting modules

**Execute**: Build reconciliation service and scheduled job.

---

### Prompt 407: Implement Razorpay Refund Flow

**Purpose**: Support full and partial refunds for Razorpay payments.

**Functionality**: Processes refunds and updates statuses.

**How it Works**:
- Adds refund request validation and approval flow
- Calls Razorpay refund API with amount
- Updates `fees_transactions` status to refunded/partial_refund
- Generates refund receipts
- Logs refund reasons and references

**Integration**:
- Used by accountant refund screens
- Links to payment history and reports
- Works with notification system

**Execute**: Implement Razorpay refund processing and update statuses.

---
## 📊 Summary

**Total Payment Gateway Prompts: 20**

**Phases Covered:**
1. **Payment Gateway Setup** (10 prompts)
2. **Payment Routes** (5 prompts)
3. **Payment Reliability and Reconciliation** (5 prompts)
**Features Implemented:**
- Razorpay payment integration
- Razorpay-only online gateway
- Payment service layer
- Payment controller
- Payment views
- Webhook handling
- Receipt generation
- Payment history
- Razorpay signature verification
- Gateway payload logging
- Webhook verification
- Idempotency and retries
- Payment reconciliation
- Full and partial refunds
- API endpoints for mobile app
- Success/failure pages
- Payment instructions

**Next Steps:**
- Backend-Frontend Integration Prompts
- Form Request Validation Prompts
- Middleware Implementation Prompts
- Service Layer Prompts
- File Upload Handling Prompts
- Export Functionality Prompts
- Real-time Notifications Prompts
- Multi-language in Views Prompts
- RTL Implementation Prompts

---

## 🚀 Ready for Implementation

The payment gateway integration is now fully planned with comprehensive prompts for online fee collection.

**Happy Building with DevIn AI!** 🚀


