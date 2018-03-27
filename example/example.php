<?php
require_once "../vendor/autoload.php";

use MessageMediaLookupsLib\MessageMediaMessagesClient;
use MessageMediaLookupsLib\APIHelper;

$basicAuthUserName = 'API_KEY'; // The username to use with basic authentication
$basicAuthPassword = 'API_SECRET'; // The password to use with basic authentication

$client = new MessageMediaLookupsLib\MessageMediaLookupsClient($basicAuthUserName, $basicAuthPassword);

$lookups = $client->getLookups();

$phoneNumber = 'MOBILE_NUMBER';
$options = 'carrier,type';

$result = $lookups->getLookupAPhoneNumber($phoneNumber, $options);
print_r($result);