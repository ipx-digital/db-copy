# DB Copy
Quickly and easily refresh your local MySQL database with production data by automating the process of copying your production database to your local development database.

## Installation
1. Use `composer require --dev ipx-digital/db-copy` in your project's directory to include it.
2. In config/app.php, under 'providers' add:
	`IPX\DbCopy\DbCopyServiceProvider::class`
3. Add the following to .env:
	```
	PRODUCTION_DB_CONNECTION=mysql
	PRODUCTION_DB_HOST=<address to production DB>
	PRODUCTION_DB_DATABASE=<database name>
	PRODUCTION_DB_USERNAME=<mysql username>
	PRODUCTION_DB_PASSWORD=<mysql password>
	SSH_USERNAME=<only needed if you're connecting over SSH>
	```
## Usage
Simply use `php artisan db:copy`.

---
#### Disclaimer
We're dealing with databases here, so remember to be careful. Make backups often and be careful with protected data and PII concerns.

---
#### Built by [IPX](https://ipx.org/) for internal use with largely Laravel projects, but works with any PHP and MySQL application or framework.