# Expense & Income Tracker

A full-stack financial tracking application built with **Laravel 13**, **Vue 3 + TypeScript**, **Inertia.js**, and **MySQL**.

## Features

- **Dashboard** – KPI summary cards, recent transactions, bank account overview
- **Bank Accounts** – CRUD, balance tracking, account-to-account transfers
- **Expenses** – Filterable table with category, payment method, date range filters
- **Income** – Dedicated income tracking with search and filters
- **Reports & Charts** – Income vs Expenses, Net Balance, Expense/Income breakdown (donut), Cash vs Bank (pie) via ApexCharts
- **Categories** – System + custom categories, tabbed by type (Expense / Income)
- **Recurring Transactions** – Auto-generated via scheduled command
- **Dark/Light Mode** – Full theme support
- **Receipt Upload** – Attach receipts to transactions

## Tech Stack

| Layer    | Technology                            |
| -------- | ------------------------------------- |
| Backend  | Laravel 13, PHP 8.3+                  |
| Frontend | Vue 3.5, TypeScript, Vite 8           |
| Routing  | Inertia.js v2                         |
| Auth     | Laravel Fortify (web) + Sanctum (API) |
| UI       | Tailwind CSS 4, shadcn-vue (reka-ui)  |
| Charts   | ApexCharts (vue3-apexcharts)          |
| Database | MySQL 8+                              |
| Icons    | Lucide Vue Next                       |

## Setup

### Prerequisites

- PHP 8.3+
- Composer
- Node.js 20+
- MySQL 8+

### Installation

```bash
# Clone and install dependencies
composer install
npm install

# Configure environment
cp .env.example .env
php artisan key:generate
```

Edit `.env` with your MySQL credentials:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=troskovi
DB_USERNAME=root
DB_PASSWORD=
```

### Database

```bash
# Run migrations and seed demo data
php artisan migrate:fresh --seed
```

This creates:

- Default expense/income categories (system categories)
- A test user: `test@example.com` / `password`
- 3 demo bank accounts with 6 months of transactions

### Development

```bash
# Start Vite dev server
npm run dev

# Start Laravel (or use Herd/Valet)
php artisan serve
```

### Recurring Transactions

Process due recurring transactions:

```bash
php artisan transactions:process-recurring
```

This is also scheduled to run daily via Laravel's task scheduler.

## API Endpoints

All API routes are under `/api/v1/` and require Sanctum authentication.

| Resource      | Endpoints                                                                                                                                                           |
| ------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Bank Accounts | `GET/POST /bank-accounts`, `PUT/DELETE /bank-accounts/{id}`, `POST /bank-accounts/{id}/restore`, `GET /bank-accounts/{id}/overview`, `POST /bank-accounts/transfer` |
| Transactions  | `GET/POST /transactions`, `PUT/DELETE /transactions/{id}`, `GET /transactions/{id}/receipt`                                                                         |
| Categories    | `GET/POST /categories`, `PUT/DELETE /categories/{id}`                                                                                                               |
| Recurring     | `GET/POST /recurring-transactions`, `PUT/DELETE /recurring-transactions/{id}`                                                                                       |
| Reports       | `GET /reports/summary`, `/income-vs-expenses`, `/net-balance`, `/expense-breakdown`, `/income-breakdown`, `/cash-vs-bank`                                           |
