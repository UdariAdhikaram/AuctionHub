# AuctionHub - Laravel Auction Platform

## Setup Instructions

1. Clone repository
2. Copy `.env.example` to `.env`
3. Configure database connection in `.env`
4. Run `composer install`
5. Run `php artisan key:generate`
6. Run `php artisan migrate --seed`
7. Run `php artisan serve`

## Running Tests

```bash
php artisan test