## Northplay
Based on laravel, includes a seperated package that has all main api code to interact with frontend.

## Run from clean Laravel
To run from a clean Laravel install just make sure to include the Laravel Breeze (auth) starter package from official Laravel package, see it's [documentation](https://laravel.com/docs/10.x/starter-kits#laravel-breeze).

Then copy the northplay folder from this repository into the Laravel root folder and include it within `composer.json`:
```
    "repositories": [
        {
            "type": "path",
            "url": "northplay/*"
        }
    ],
    "require": {
        "northplay/northplay-api": "*",
```
## KeyDB (redis)
```
    docker run -p 6200:6379 -d eqalpha/keydb keydb-server /etc/keydb/keydb.conf --server-threads 4 --requirepass password
```

### Northplay Installer
```
php artisan northplay-api:install
```

Installer will do the following:
    - publish config file
    - publish assets
    - publish database migrations
    - ask you if you want to run migrations
    - copy and register service provider
    - run all database seeding

## Run from this repository
Copy this repository and run the commands below.

### Set docker user to the current linux user:
```
echo 'export DOCKER_USER="$(id -u)"' >> ~/.bash_profile
echo 'export DOCKER_GROUP="$(id -g)"' >> ~/.bash_profile
source ~/.bash_profile
```

### Start up docker container:
```
docker-compose up -d
```

### Enter the main docker APP container:
```
docker exec -it #DOCKER_CONTAINER_NAME# bash
```

## Example .env
```
APP_NAME=Casino
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=https://casinoapi.northplay.me
FRONTEND_URL=https://casino.northplay.me

LOG_CHANNEL=papertrail
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=pgsql
DATABASE_URL="postgresql://postgres:*******@db.euflgeqzxfrnlaqfdwce.supabase.co:5432/postgres"

#DB_CONNECTION=sqlite
#DB_DATABASE="/home/ryan/start/api/database/db.db"

BROADCAST_DRIVER=redis
CACHE_DRIVER=redis
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis
SESSION_DRIVER=database
SESSION_LIFETIME=120

PAPERTRAIL_URL=logs.papertrailapp.com
PAPERTRAIL_PORT=0

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=0
REDIS_PORT=6200

MAIL_MAILER=smtp
MAIL_HOST=postal.northplay.online
MAIL_PORT=25
MAIL_USERNAME=ryan/northplayonline
MAIL_PASSWORD=
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="mailer@northplay.online"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"

```