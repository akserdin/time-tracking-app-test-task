## Welcome to Time tracking app!

- Make sure you have docker and docker-compose installed.
- Clone repository
- Copy .env.example as .env and fill credentials for database
- Run `docker-compose build app`
- Run `docker-compose up -d`
- Run `docker-compose exec app composer install`
- Run `docker-compose exec app doctrine:migrations:migrate`
- Edit your /etc/hosts file, add `127.0.0.1	time-tracking-app.local`

Your app is ready, navigate to http://time-tracking-app.local
