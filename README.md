# Event Booking System API

## Setup

Clone the repository

```
git clone git@github.com:KimPalao/event-booking-system-api.git
```

Install dependencies 

```
npm install
composer install
```

Copy the environment variables

```
cp .env.example .env
```

Apply migrations and seeders
```
php artisan migrate:fresh --seed
```

Run 

```
composer run dev
```
