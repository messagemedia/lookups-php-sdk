<?php
/*
 * MessageMediaLookups
 */

namespace MessageMediaLookupsLib\Tests;

use MessageMediaLookupsLib\APIException;
use MessageMediaLookupsLib\Exceptions;
use MessageMediaLookupsLib\APIHelper;
use MessageMediaLookupsLib\Models;

class LookupsControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \MessageMediaLookupsLib\Controllers\LookupsController Controller instance
     */
    protected static $controller;

    /**
     * @var HttpCallBackCatcher Callback
     */
    protected $httpResponse;

    /**
     * Setup test class
     */
    public static function setUpBeforeClass()
    {
        TestHelper::getAuthorizationFromEnvironment();
        $client = new \MessageMediaLookupsLib\MessageMediaLookupsClient();
        self::$controller = $client->getLookups();
    }

    /**
     * Setup test
     */
    protected function setUp()
    {
        $this->httpResponse = new HttpCallBackCatcher();
    }

    /**
     * Use the Lookups API to find information about a phone number.
A request to the lookups API has the following format:
```/v1/lookups/phone/{phone_number}?options={carrier,type}```
The `{phone_number}` parameter is a required field and should be set to the phone number to be looked up.
The options query parameter can also be used to request additional information about the phone number.
By default, a request will only return the `country_code` and `phone_number` properties in the response.
To request details about the the carrier, include `carrier` as a value of the options parameter.
To request details about the type, include `type` as a value of the options parameter. To pass multiple values
to the options parameter, use a comma separated list, i.e. `carrier,type`.
A successful request to the lookups endpoint will return a response body as follows:
```json
{
  "country_code": "AU",
  "phone_number": "+61491570156",
  "type": "mobile",
  "carrier": {
    "name": "Telstra"
  }
}
```
Each property in the response body is defined as follows:
- ```country_code``` ISO ALPHA 2 country code of the phone number
- ```phone_number``` E.164 formatted phone number
- ```type``` The type of number. This can be ```"mobile"``` or ```"landline"```
- ```carrier``` Holds information about the specific carrier (if available)
  - ```name``` The carrier's name as reported by the network
     */
    public function testLookupAPhoneNumber()
    {
        // Parameters for the API call
        $phoneNumber = '+61491570156';
        $options = 'carrier,type';

        // Set callback and perform API call
        $result = null;
        self::$controller->setHttpCallBack($this->httpResponse);
        try {
            $result = self::$controller->getLookupAPhoneNumber($phoneNumber, $options);
        } catch (APIException $e) {
        }

        // Test response code
        $this->assertEquals(
            200,
            $this->httpResponse->getResponse()->getStatusCode(),
            "Status is not 200"
        );

        // Test headers
        $headers = [];
        $headers['Content-Type'] = null ;

        $this->assertTrue(
            TestHelper::areHeadersProperSubsetOf($headers, $this->httpResponse->getResponse()->getHeaders(), true),
            "Headers do not match"
        );

        // Test whether the captured response is as we expected
        $this->assertNotNull($result, "Result does not exist");

        $this->assertEquals(
            '{"carrier":{"name":"AU Landline Carrier"},"country_code":"AU","phone_number":"+61491570156","type":"MOBILE"}',
            $this->httpResponse->getResponse()->getRawBody(),
            "Response body does not match exactly"
        );
    }
}
