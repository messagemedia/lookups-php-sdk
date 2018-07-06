# MessageMedia Lookups PHP SDK
[![Build Status](https://travis-ci.org/messagemedia/lookups-php-sdk.svg?branch=master)](https://travis-ci.org/messagemedia/lookups-php-sdk)
[![composer](https://badge.fury.io/ph/messagemedia%2Flookups-sdk.svg)](https://packagist.org/packages/messagemedia/lookups-sdk)

The MessageMedia Lookups API provides a number of endpoints for validating the phone numbers you’re sending to by checking their validity, type and carrier records.

![Isometric](https://developers.messagemedia.com/wp-content/uploads/2017/11/lookups-api.png)

## ⭐️ Installing via Composer
Now install messagemedia-lookups-sdk via composer by using the following to your composer.json file:
```
composer require messagemedia/lookups-sdk
```

## 🎬 Get Started
It's easy to get started. Simply enter the API Key and secret you obtained from the [MessageMedia Developers Portal](https://developers.messagemedia.com) into the code snippet below and a mobile number you wish to send to.

### 👀 Lookup a number
```php
<?php
require_once "vendor/autoload.php";

$basicAuthUserName = 'YOUR_API_KEY'; // The username to use with basic authentication
$basicAuthPassword = 'YOUR_SECRET_KEY'; // The password to use with basic authentication

$client = new MessageMediaLookupsLib\MessageMediaLookupsClient($basicAuthUserName, $basicAuthPassword);

$lookups = $client->getLookups();

$phoneNumber = 'YOUR_MOBILE_NUMBER';
$options = 'carrier,type';

$result = $lookups->getLookupAPhoneNumber($phoneNumber, $options);
print_r($result);
?>
```

## 📕 Documentation
The PHP SDK Documentation can be viewed [here](DOCUMENTATION.md)

## 😕 Need help?
Please contact developer support at developers@messagemedia.com or check out the developer portal at [developers.messagemedia.com](https://developers.messagemedia.com/)
