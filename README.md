<p align="center">
<img src="https://sonarcloud.io/api/project_badges/measure?project=0v3rl0rd3gg_blackjack&metric=reliability_rating" alt="Build Status" />
<img src="https://sonarcloud.io/api/project_badges/measure?project=0v3rl0rd3gg_blackjack&metric=alert_status" alt="Build Status" />
<img src="https://sonarcloud.io/api/project_badges/measure?project=0v3rl0rd3gg_blackjack&metric=code_smells" alt="Build Status" />
<img src="https://sonarcloud.io/api/project_badges/measure?project=0v3rl0rd3gg_blackjack&metric=sqale_rating" alt="Build Status" />
<img src="https://sonarcloud.io/api/project_badges/measure?project=0v3rl0rd3gg_blackjack&metric=security_rating" alt="Build Status" />
<img src="https://sonarcloud.io/api/project_badges/measure?project=0v3rl0rd3gg_blackjack&metric=bugs" alt="Build Status" />
<img src="https://sonarcloud.io/api/project_badges/measure?project=0v3rl0rd3gg_blackjack&metric=vulnerabilities" alt="Build Status" />
</p>

# About Blackjack

This is a simple game.


## To set up

##### Create a local database

```bash
cp .env.example .env
```

##### Enter the DB username and password into the newly created .env file


##### Generate a new key
```bash
php artisan key:generate
```

##### Set up the database
```bash
php artisan migrate
```

##### Or if you want to pre-populate a user to log in with.  
##### Edit database/seeders/DatabaseSeeder.php to add your username and password

```
php artisan migrate --seed 
```

##### Install the composer dependencies
```bash
composer install
```

##### Install the node dependencies
```bash
npm install
```

##### Compile frontend assets
```bash
npm run dev
```