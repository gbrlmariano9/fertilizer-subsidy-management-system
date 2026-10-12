# Fertilizer Subsidy Management System

A Laravel-based web application for managing fertilizer subsidy transactions between registered farmers and accredited merchants. The system supports QR-based order verification, product inventory management, farmer subsidy balances, merchant wallets, and payment settlement.

## Technology Stack

- **Backend:** PHP 8.2+, Laravel 12
- **Frontend:** Laravel Blade, Vite, Tailwind CSS, Alpine.js
- **Database:** MySQL 8.4
- **Containerization:** Docker and Docker Compose
- **Web Server:** Apache
- **Build Tools:** Composer, Node.js, and npm
- **Security:** Authenticated role-based workflows, cryptographically signed QR payloads, QR expiration validation, HMAC-SHA256 webhook signature verification, and transactional payment settlement

## Features

### Farmer

- Farmer account and beneficiary identification
- Subsidy balance management
- Merchant QR scanning and transaction review
- QR signature and expiration validation
- Order and payment status tracking
- Pending order cancellation

### Merchant

- Product catalog and stock management
- Order creation and QR-based checkout workflow
- Transaction monitoring
- Merchant wallet balance and withdrawal-related workflows

### Payment and Transaction Security

- Signed webhook event verification
- Payment amount and PHP currency validation
- Duplicate payment-event detection
- Database transactions and row locking during settlement
- Payment status polling as a fallback
- Transaction, product stock, farmer subsidy, and merchant wallet updates

**Payment integration note:** The current implementation uses a local payment simulator for development and assessment. It does not represent a live third-party payment gateway or a real-money transfer.

## Prerequisites

### Option A: Docker Setup (Recommended)

Install:

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) with Docker Compose
- Git

PHP, Composer, Node.js, npm, and MySQL do not need to be installed directly on the host computer when using the Docker setup.

### Option B: Local Setup

Install:

- PHP 8.2 or later
- Composer 2.x
- MySQL server
- Node.js and npm
- Git

The project was developed and tested using PHP 8.2.12, Composer 2.10.1, Node.js 24.18.0, npm 11.16.0, and Laravel 12.69.3.

## Installation

### Option A: Docker Setup (Recommended)

## Installation Guide (Docker)

### Prerequisites

- Git
- Docker Desktop installed and running

PHP, Composer, and MySQL do not need to be installed on the host machine.

### Step 1: Clone the Repository

Open a terminal and run:

```bash
git clone https://github.com/gbrlmariano9/fertilizer-subsidy-management-system.git
```

Navigate to the project directory:

```bash
cd fertilizer-subsidy-management-system
```

### Step 2: Create the Environment File

Copy the example environment file:

**Windows PowerShell:**

```powershell
Copy-Item .env.example .env
```

**macOS/Linux:**

```bash
cp .env.example .env
```

### Step 3: Configure the Application Key

Open `.env` and make sure the `APP_KEY` entry is present:

```dotenv
APP_KEY=
```

The application key will be generated in a later step.

### Step 4: Build the Docker Image

```bash
docker compose build
```

Wait for the build to finish successfully.

### Step 5: Generate the Application Key

Run:

```bash
docker compose run --rm --no-deps app php artisan key:generate --show
```

Copy the generated key and paste it into `.env`:

```dotenv
APP_KEY=base64:YOUR_GENERATED_KEY
```

Replace `base64:YOUR_GENERATED_KEY` with the complete generated key.

### Step 6: Start the Application and Database

```bash
docker compose up -d
```

Check that the containers are running:

```bash
docker compose ps
```

### Step 7: Run Database Migrations and Seeders

```bash
docker compose exec app php artisan migrate --seed
```

This creates the database tables and populates the initial data.

### Step 8: Open the Application

Open your browser and visit:

**http://localhost:8080**

### Useful Commands

View application logs:

```bash
docker compose logs -f app
```

Stop the application:

```bash
docker compose down
```

Start the application again:

```bash
docker compose up -d
```

**Note:** Do not run `docker compose down -v` unless you intend to remove the database volume and its stored data.

### Option B: Local PHP and MySQL Setup

#### 1. Clone the repository

```bash
git clone https://github.com/gbrlmariano9/fertilizer-subsidy-management-system.git
cd fertilizer-subsidy-management-system
```

#### 2. Install PHP dependencies

```bash
composer install
```

#### 3. Configure the environment

Create the `.env` file.

**Windows PowerShell:**

```powershell
Copy-Item .env.example .env
```

**macOS/Linux:**

```bash
cp .env.example .env
```

Generate the application key:

```bash
php artisan key:generate
```

#### 4. Create a database

Create an empty MySQL database named:

```text
fertilizer_subsidy
```

Update the database settings in `.env` to match your local MySQL configuration:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=fertilizer_subsidy
DB_USERNAME=root
DB_PASSWORD=
```

Change the username and password if your local database uses different credentials.

#### 5. Configure the payment webhook secret

Set a unique secret in `.env`:

```dotenv
PAYMENT_WEBHOOK_SECRET=replace_with_a_long_random_secret
```

Use a securely generated value. Do not commit `.env` or publish real secrets.

#### 6. Run database migrations and seeders

```bash
php artisan migrate --seed
```

#### 7. Install frontend dependencies and build assets

```bash
npm install
npm run build
```

#### 8. Start the application

```bash
php artisan serve
```

Open the local URL displayed in the terminal, usually:

http://127.0.0.1:8000

## Demo Accounts

The database seeder creates the following local evaluation accounts.

| Role | Email | Password |
|---|---|---|
| Farmer | `farmer@gmail.com` | `Password123!` |
| Merchant | `merchant@gmail.com` | `Password123!` |

These are development and evaluation credentials only. Do not use them for a production deployment.

The seeder also creates sample fertilizer products for testing.

## Running Tests

**Docker:**

```bash
docker compose exec app php artisan test
```

**Local setup:**

```bash
php artisan test
```

## Payment Simulation

The current checkout flow uses `LocalPaymentSimulator` to generate a simulated successful payment event.

The payment settlement workflow validates the event and transaction reference, verifies the amount and currency, and updates the relevant transaction and account balances.

The webhook handler verifies signatures using the `X-Payment-Signature` header and the configured `PAYMENT_WEBHOOK_SECRET`.

This implementation is intended for local development and assessment. A production payment deployment requires integration with a real payment provider, verification of authentic provider events, and appropriate production configuration.

## Important Security Notes

- Keep `.env` private.
- Never commit API credentials, webhook secrets, or production passwords.
- Use a separate database for development and evaluation.
- Configure HTTPS and production-safe environment settings before deployment.
- Use a real payment provider and its official verification procedure before processing real payments.
- Replace development secrets and demo credentials before any production deployment.
- Docker simplifies environment setup but does not, by itself, make an application production-ready.

## License

This project was developed for software engineering assessment purposes.
