CRYPTO/COINBASE API
=========================
### Install Guide
- Install local webserver (OSPanel, XAMPP, etc...)
- Copy unzipped or cloned files and folder to web directory (htdocs, etc...)
- Navigate to app folder to find main files
- Run Composer Update and Composer Install in terminal
- Packages Included are:
  - Guzzle HTTP
  - Slim
  - Slim PSR7
- Use PHP version 8.1
- Sample database schema (crypto.sql) is in app folder
- In case composer install/upgrade fails, vendor.zip is included just unzip inside the app folder
- Local database connection variables(host,user,database,port) are found in db/dbClass.php

Testing the APP
==========================
- When installed and configured successfully, you can test via POSTMAN App
- Checkout API Method
  - Create new request of POST type, type https://your-host-url/checkout
  - Add headers: Key:Content-type, Value:application/x-www-form-urlencoded
  - Navigate to Body Tab and add 2 Key/Value pair
    - Key 1: email
    - Key 2: amount
  - Press send and it should display, Your payment URL is: https://fake.coinbase.com/pay/{random hash}
- Webhook API Method
  - Create new request of POST type, type https://your-host-url/webhook
  - Add headers: Key:Content-type, Value:application/x-www-form-urlencoded
  - Navigate to Body Tab and add 4 Key/Value pair
    - Key 1: type, Value: 'charged:confirmed' or 'queued:pending'
    - Key 2: customer_email, Value: 'email@domain.com'
    - Key 3: id, Value: example ('TRXS000091234900MCD')
    - Key 4: status, Value: example ('confirmed')
  - Press send, and it should say: 
    - Transaction has been confirmed. if the data was successfully logged in the database
    - There was a problem with the confirmation, please try again. if a duplicate transaction ID already exists.

Assumptions
========================
- For the email and amount in checkout method, assuming proper validation and filtering were done via front-end.
- For webhook method, payload data is assumed as post variables
- For the fake transaction ID in the checkout method, a simulated route named, http://{localhost}/payment-api is created
- This simulated url is also hosted locally which returns a random hash for the payment URL
