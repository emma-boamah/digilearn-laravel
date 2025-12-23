#!/bin/bash

# DigiLearn Authentication Log Monitor Cron Script
# This script runs the authentication log monitoring with alerting

# Set the Laravel project directory
PROJECT_DIR="/var/www/learn_Laravel/digilearn-laravel"
LOG_FILE="/var/log/auth_monitor.log"
EMAIL_RECIPIENT="emmanuelboamah046@gmail.com"  # Change this to your admin email

# Change to project directory
cd "$PROJECT_DIR" || exit 1

# Log the monitoring run
echo "$(date): Running authentication log monitor" >> "$LOG_FILE"

# Run the monitoring command with alerting
# Monitor auth logs for the last 24 hours with email alerts
/usr/bin/php artisan auth:monitor --alert --email="$EMAIL_RECIPIENT" --silent >> "$LOG_FILE" 2>&1

# Optional: Also monitor security logs separately
/usr/bin/php artisan auth:monitor --log=security --alert --email="$EMAIL_RECIPIENT" --silent >> "$LOG_FILE" 2>&1

# Optional: Monitor Google auth logs
/usr/bin/php artisan auth:monitor --log=google_auth --alert --email="$EMAIL_RECIPIENT" --silent >> "$LOG_FILE" 2>&1

echo "$(date): Authentication monitoring completed" >> "$LOG_FILE"