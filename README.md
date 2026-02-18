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

(Optional) Fill up the environment variables for the mail notifications

```
MAIL_MAILER=smtp
MAIL_SCHEME=null
MAIL_HOST=your.mailhost.com
MAIL_PORT=587
MAIL_USERNAME=email@mail.host.com
MAIL_PASSWORD=yourpass
MAIL_FROM_ADDRESS=booking@mail.host.com
MAIL_FROM_NAME="Event Booking"
```

Apply migrations and seeders
```
php artisan migrate:fresh --seed
```

Run 

```
composer run dev
```
