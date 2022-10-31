
# Aspire Mini API Project

It is an app that allows authenticated users to go through a loan application and Apply for loan.

Loan Amount would be paid all together or it could be paid weekly based on term selected by customer



## System Requirement

**Database:** MySql, PhpMyAdmin

**Server:** Apache with PHP v.8.0

**Tools Required:** Postman,Composer

**Other:** Make Sure You have Installed Laravel Installer using the following command
. Skip If Already done

```bash
  composer global require laravel/installer
```




## Installation
**1. Clone the github repository or download the zip and extract the same**

**2. Create the .env file by copying the content from .env.example and create the database and add the credentials of same in your .env file**

**3. Run the following command's**

```bash
  composer update
  
  if You Get this error
  In order to use the Auth::routes() method, please install the laravel/ui package.
  Run Below Commands
  
  composer require laravel/ui
  php artisan ui vue --auth
```
```bash
  composer require doctrine/dbal
```
```bash
  php artisan key:generate
```
```bash
  php artisan config:cache
  php artisan config:clear
```
```bash
  php artisan migrate
```
```bash
  php artisan db:seed
```
**4. Make Sure that You Run the Above two commands in order to create the necessary tables and add the dummy data that is used for testing purpose**

**5. Below are the admin Credentials for testing Purpose**
```bash
  Username: admin@gmail.com
  Password: 123456
```


**6. For Futher Usage of the App Go through the link below: https://docs.google.com/document/d/1XBK2_XLj99m1813HB3IB1PJup9i_WdEVViBRa4VY-oY/edit?usp=sharing**
