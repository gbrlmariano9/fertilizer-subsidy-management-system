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

**Payment integration note:** The current implementation uses a local payment simulator for development and assessment. It does not, by itself, represent a live third-party payment gateway or a real-money transfer.

## Prerequisites

### Option A: Run with Docker (Recommended)

Install the following:

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) with Docker Compose
- Git

PHP, Composer, Node.js, and MySQL do not need to be installed directly on the host computer because the Docker image provides the application runtime, and Docker Compose runs the MySQL database.

### Option B: Run Locally

Install the following:

- PHP 8.2 or later
- Composer 2.x
- MySQL server
- Node.js and npm
- Git

The project was developed and tested using PHP 8.2.12, Composer 2.10.1, Node.js 24.18.0, npm 11.16.0, and Laravel 12.69.3.

## Installation

### Option A: Docker Setup (Recommended)

#### 1. Clone the repository

```bash
git clone https://github.com/gbrlmariano9/fertilizer-subsidy-management-system.git
cd fertilizer-subsidy-management-system
```

#### 2. Configure environment variables

Create a local `.env` file from the example file if it does not already exist.

**Windows PowerShell:**

```powershell
Copy-Item .env.example .env
```

**macOS/Linux:**

```bash
cp .env.example .env
```

Generate an application key:

```bash
php artisan key:generate
```

> Run this command on the host only if PHP and Composer are installed. Otherwise, generate the key inside the Docker container after building the image, or provide a valid `APP_KEY` through the environment configuration before starting the application.

Ensure that your `compose.yaml` receives a valid `APP_KEY` and a unique `PAYMENT_WEBHOOK_SECRET`. The provided Docker Compose configuration uses MySQL service `db`, database `fertilizer_subsidy`, and the application port `8080`.

Do not commit `.env` or publish real secrets.

#### 3. Build and start the containers

```bash
docker compose up --build -d
```

This builds the application image and starts the Laravel/Apache application and MySQL database containers.

Check the container status:

```bash
docker compose ps
```

Wait until the database reports a healthy status before proceeding.

#### 4. Run database migrations and seed demo data

```bash
docker compose exec app php artisan migrate --seed
```

This creates the required database tables and seeds demo farmer, merchant, and product records.

If the demo user seeder has already completed successfully, you do not need to run the seeders again unless you intentionally want to re-run them.

#### 5. Open the application

Visit:

[http://localhost:8080](http://localhost:8080)

#### 6. Useful Docker commands

View running containers:

```bash
docker compose ps
```

View application logs:

```bash
docker compose logs -f app
```

Stop the containers:

```bash
docker compose down
```

Start the existing containers again:

```bash
docker compose up -d
```

Stop the containers and remove the database volume **(deletes the Docker database data)**:

```bash
docker compose down -v
```

**Important:** Do not use `docker compose down -v` unless you intentionally want to delete the database volume and its data.

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

Copy the example environment file.

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

Set a unique, random secret in `.env` for local webhook signature verification:

```dotenv
PAYMENT_WEBHOOK_SECRET=replace_with_a_long_random_secret
```

Use a securely generated value. Do not commit `.env` or publish real secrets.

#### 6. Run database migrations and seed demo data

```bash
php artisan migrate --seed
```

This creates the required database tables and seeds demo farmer, merchant, and product records.

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

[http://127.0.0.1:8000](http://127.0.0.1:8000)

## Demo Accounts

The database seeder creates the following local evaluation accounts.

| Role | Email | Password |
|---|---|---|
| Farmer | `farmer@gmail.com` | `Password123!` |
| Merchant | `merchant@gmail.com` | `Password123!` |

These are development/demo credentials only. Do not use them for a production deployment.

The seeder also creates sample fertilizer products for testing the application.

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
