# PHP Checkout Assignment

## Requirements

- PHP 8.1+
- Composer

## Setup

```bash
composer install
cp .env.example .env
```

Open `.env` and fill the Stripe test keys:

```
STRIPE_PUBLISHABLE_KEY=pk_test_...
STRIPE_SECRET_KEY=sk_test_...
```

## Running locally

```bash
php -S localhost:8000
```

Then open [http://localhost:8000](http://localhost:8000) in your browser.


## Test cards

Use any of the cards below to complete a payment in test mode. 

| Brand | Number | CVC | Expiry |
|-------|--------|-----|--------|
| Visa | `4242 4242 4242 4242` | Any 3 digits | Any future date |
| Visa (debit) | `4000 0566 5566 5556` | Any 3 digits | Any future date |
| Mastercard | `5555 5555 5555 4444` | Any 3 digits | Any future date |
