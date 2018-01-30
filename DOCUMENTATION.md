# Getting started

The MessageMedia Lookups API provides a number of endpoints for validating the phone numbers you’re sending to by checking their validity, type and carrier records.

## How to Build

The generated code has dependencies over external libraries like UniRest. These dependencies are defined in the ```composer.json``` file that comes with the SDK. 
To resolve these dependencies, we use the Composer package manager which requires PHP greater than 5.3.2 installed in your system. 
Visit [https://getcomposer.org/download/](https://getcomposer.org/download/) to download the installer file for Composer and run it in your system. 
Open command prompt and type ```composer --version```. This should display the current version of the Composer installed if the installation was successful.

* Using command line, navigate to the directory containing the generated files (including ```composer.json```) for the SDK. 
* Run the command ```composer install```. This should install all the required dependencies and create the ```vendor``` directory in your project directory.

![Building SDK - Step 1](https://apidocs.io/illustration/php?step=installDependencies&workspaceFolder=MessageMediaLookups-PHP)

### [For Windows Users Only] Configuring CURL Certificate Path in php.ini

CURL used to include a list of accepted CAs, but no longer bundles ANY CA certs. So by default it will reject all SSL certificates as unverifiable. You will have to get your CA's cert and point curl at it. The steps are as follows:

1. Download the certificate bundle (.pem file) from [https://curl.haxx.se/docs/caextract.html](https://curl.haxx.se/docs/caextract.html) on to your system.
2. Add curl.cainfo = "PATH_TO/cacert.pem" to your php.ini file located in your php installation. “PATH_TO” must be an absolute path containing the .pem file.

```ini
[curl]
; A default value for the CURLOPT_CAINFO option. This is required to be an
; absolute path.
;curl.cainfo =
```

## How to Use

The following section explains how to use the MessageMediaLookups library in a new project.

### 1. Open Project in an IDE

Open an IDE for PHP like PhpStorm. The basic workflow presented here is also applicable if you prefer using a different editor or IDE.

![Open project in PHPStorm - Step 1](https://apidocs.io/illustration/php?step=openIDE&workspaceFolder=MessageMediaLookups-PHP)

Click on ```Open``` in PhpStorm to browse to your generated SDK directory and then click ```OK```.

![Open project in PHPStorm - Step 2](https://apidocs.io/illustration/php?step=openProject0&workspaceFolder=MessageMediaLookups-PHP)     

### 2. Add a new Test Project

Create a new directory by right clicking on the solution name as shown below:

![Add a new project in PHPStorm - Step 1](https://apidocs.io/illustration/php?step=createDirectory&workspaceFolder=MessageMediaLookups-PHP)

Name the directory as "test"

![Add a new project in PHPStorm - Step 2](https://apidocs.io/illustration/php?step=nameDirectory&workspaceFolder=MessageMediaLookups-PHP)
   
Add a PHP file to this project

![Add a new project in PHPStorm - Step 3](https://apidocs.io/illustration/php?step=createFile&workspaceFolder=MessageMediaLookups-PHP)

Name it "testSDK"

![Add a new project in PHPStorm - Step 4](https://apidocs.io/illustration/php?step=nameFile&workspaceFolder=MessageMediaLookups-PHP)

Depending on your project setup, you might need to include composer's autoloader in your PHP code to enable auto loading of classes.

```PHP
require_once "../vendor/autoload.php";
```

It is important that the path inside require_once correctly points to the file ```autoload.php``` inside the vendor directory created during dependency installations.

![Add a new project in PHPStorm - Step 4](https://apidocs.io/illustration/php?step=projectFiles&workspaceFolder=MessageMediaLookups-PHP)

After this you can add code to initialize the client library and acquire the instance of a Controller class. Sample code to initialize the client library and using controller methods is given in the subsequent sections.

### 3. Run the Test Project

To run your project you must set the Interpreter for your project. Interpreter is the PHP engine installed on your computer.

Open ```Settings``` from ```File``` menu.

![Run Test Project - Step 1](https://apidocs.io/illustration/php?step=openSettings&workspaceFolder=MessageMediaLookups-PHP)

Select ```PHP``` from within ```Languages & Frameworks```

![Run Test Project - Step 2](https://apidocs.io/illustration/php?step=setInterpreter0&workspaceFolder=MessageMediaLookups-PHP)

Browse for Interpreters near the ```Interpreter``` option and choose your interpreter.

![Run Test Project - Step 3](https://apidocs.io/illustration/php?step=setInterpreter1&workspaceFolder=MessageMediaLookups-PHP)

Once the interpreter is selected, click ```OK```

![Run Test Project - Step 4](https://apidocs.io/illustration/php?step=setInterpreter2&workspaceFolder=MessageMediaLookups-PHP)

To run your project, right click on your PHP file inside your Test project and click on ```Run```

![Run Test Project - Step 5](https://apidocs.io/illustration/php?step=runProject&workspaceFolder=MessageMediaLookups-PHP)

## How to Test

Unit tests in this SDK can be run using PHPUnit. 

1. First install the dependencies using composer including the `require-dev` dependencies.
2. Run `vendor\bin\phpunit --verbose` from commandline to execute tests. If you have 
   installed PHPUnit globally, run tests using `phpunit --verbose` instead.

You can change the PHPUnit test configuration in the `phpunit.xml` file.

## Initialization

### Authentication
In order to setup authentication and initialization of the API client, you need the following information.

| Parameter | Description |
|-----------|-------------|
| basicAuthUserName | The username to use with basic authentication |
| basicAuthPassword | The password to use with basic authentication |



API client can be initialized as following.

```php
$basicAuthUserName = 'basicAuthUserName'; // The username to use with basic authentication
$basicAuthPassword = 'basicAuthPassword'; // The password to use with basic authentication

$client = new MessageMediaLookupsLib\MessageMediaLookupsClient($basicAuthUserName, $basicAuthPassword);
```


# Class Reference

## <a name="list_of_controllers"></a>List of Controllers

* [LookupsController](#lookups_controller)

## <a name="lookups_controller"></a>![Class: ](https://apidocs.io/img/class.png ".LookupsController") LookupsController

### Get singleton instance

The singleton instance of the ``` LookupsController ``` class can be accessed from the API Client.

```php
$lookups = $client->getLookups();
```

### <a name="get_lookup_a_phone_number"></a>![Method: ](https://apidocs.io/img/method.png ".LookupsController.getLookupAPhoneNumber") getLookupAPhoneNumber

> Use the Lookups API to find information about a phone number.
> A request to the lookups API has the following format:
> ```/v1/lookups/phone/{phone_number}?options={carrier,type}```
> The `{phone_number}` parameter is a required field and should be set to the phone number to be looked up.
> The options query parameter can also be used to request additional information about the phone number.
> By default, a request will only return the `country_code` and `phone_number` properties in the response.
> To request details about the the carrier, include `carrier` as a value of the options parameter.
> To request details about the type, include `type` as a value of the options parameter. To pass multiple values
> to the options parameter, use a comma separated list, i.e. `carrier,type`.
> A successful request to the lookups endpoint will return a response body as follows:
> ```json
> {
>   "country_code": "AU",
>   "phone_number": "+61491570156",
>   "type": "mobile",
>   "carrier": {
>     "name": "Telstra"
>   }
> }
> ```
> Each property in the response body is defined as follows:
> - ```country_code``` ISO ALPHA 2 country code of the phone number
> - ```phone_number``` E.164 formatted phone number
> - ```type``` The type of number. This can be ```"mobile"``` or ```"landline"```
> - ```carrier``` Holds information about the specific carrier (if available)
>   - ```name``` The carrier's name as reported by the network


```php
function getLookupAPhoneNumber(
        $phoneNumber,
        $options = null)
```

#### Parameters

| Parameter | Tags | Description |
|-----------|------|-------------|
| phoneNumber |  ``` Required ```  | The phone number to be looked up |
| options |  ``` Optional ```  | TODO: Add a parameter description |



#### Example Usage

```php
$phoneNumber = '+61491570156';
$options = 'carrier,type';

$result = $lookups->getLookupAPhoneNumber($phoneNumber, $options);

```

#### Errors

| Error Code | Error Description |
|------------|-------------------|
| 404 | Number was invalid |



[Back to List of Controllers](#list_of_controllers)



