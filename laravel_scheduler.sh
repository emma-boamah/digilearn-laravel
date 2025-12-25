#!/bin/bash

# Change to the application's root directory
cd /var/www/learn_Laravel/digilearn-laravel/digilearn-laravel

# Run the Laravel scheduler using Sail
./vendor/bin/sail artisan schedule:run

# Log the output for debugging (optional)
 >> /var/www/learn_Laravel/digilearn-laravel/storage/logs/cron.log 2>&1

