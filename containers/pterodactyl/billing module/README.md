# Thanks for buying the Billing System for Pterodactyl
# Theme by GIGABAIT and Mubeen for Pterodactyl 1.10.x

Before proceeding with installation make sure you have checked the following things:

* Make sure you're running the latest version of Pterodactyl
* Pterodactyl must be completely stock with no addons installed, to remove any addons you can run the pterodactyl update script.
* Make sure you don't use WinsCP to upload the files, use FileZilla Instead
* Make sure yarn is installed: https://pterodactyl.io/community/customization/panel.html

Automatic Installation:

1. Request a unique license key @ our Discord server: https://discord.gg/RJfCxC2W3e
2. If this is your first time, drag and drop all folders in the this folder to /var/www/pterodactyl
3. Open terminal, head to "/var/www/pterodactyl/" and execute the following command: "php artisan billing:install stable"
4. Navigate to Admin Area -> Application API -> Create a new Application API select Read and Write for all options; Paste API in Admin Area -> Billing -> In the top field called "API settings"
5. You're all set, and ready to go!


How-To-Update (NOTE: All data such as plans, user info etc is saved except for manual file changes to billing module)
1. Run following command in /var/www/pterodactyl: php artisan billing:install stable

# Stripe
1. Open Command Line and run the following command: composer require stripe/stripe-php

# Additional Configuration:
For registration emails to work, make sure you have a SMTP server setup in your pterodactyl settings.



# DEBUGGING STEPS:

Is the panel still default after step 5? 
|--> Make sure the files have been replaced correctly by your FTP Program
|--> Clear Laravel cache with following commands: php artisan view:clear && php artisan config:clear


Need help? Join our discord: https://discord.gg/RJfCxC2W3e
