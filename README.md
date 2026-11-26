# Liquid Books
##### A bookkeeping system that helps you manage your small business accounting

## How It Works
1. Add all of your clients through the `Manage Clients` tab
2. Add all of your expense categories through the `Manage Categories` tab
3. Add your transactions
  - From the main page, click the year you'd like to access
  - Tap `Add Transaction` and provide the details

## Unique Features
  - Automatically split transactions up across multiple months (ex: quartely invoice, yearly license, etc.)
  - Automatically generated forecasts based off of historical data
  - Automatically attach credit card fees to income transactions

## Installation
1. Run `docker-compose up -d`
2. Run `docker compose exec liquidbooks-web composer install`
3. Visit [http://localhost:8001](http://localhost:8001)

## Tests
Run the unit tests from the PHP application container:

```sh
docker compose exec liquidbooks-web php tests/stdlib_test.php
docker compose exec liquidbooks-web php tests/database_test.php
```

The database tests clone the local `liquid_books` schema and data into a uniquely
named temporary database, run the app's query helpers against it, then drop the
temporary database. They do not modify the application database.