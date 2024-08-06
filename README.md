List of Commands

Setup Database and Queue

1. Please refer to .env.example, just put your database credential in it
2. run command php artisan migrate for creating table
3. run command php artisan db:seed for seeding products

API List can be obtained in e_commerce_api_list_command

1. The file is in bruno collection
2. https://www.usebruno.com/ can download bruno website

Testing Part

1. Please refer to .env.example, just put your database credential in it
2. run command php artisan migrate --env=testing for creating table
3. run command php artisan test --filter=CartControllerTest --env=testing for testing
