<?php
/*
 * MessageMediaLookups
 *
 * This file was automatically generated for MessageMedia by APIMATIC v2.0 ( https://apimatic.io ).
 */

namespace MessageMediaLookupsLib\Controllers;

use MessageMediaLookupsLib\APIException;
use MessageMediaLookupsLib\APIHelper;
use MessageMediaLookupsLib\Configuration;
use MessageMediaLookupsLib\Models;
use MessageMediaLookupsLib\Exceptions;
use MessageMediaLookupsLib\Http\HttpRequest;
use MessageMediaLookupsLib\Http\HttpResponse;
use MessageMediaLookupsLib\Http\HttpMethod;
use MessageMediaLookupsLib\Http\HttpContext;
use Unirest\Request;

/**
 * @todo Add a general description for this controller.
 */
class LookupsController extends BaseController
{
    /**
     * @var LookupsController The reference to *Singleton* instance of this class
     */
    private static $instance;

    /**
     * Returns the *Singleton* instance of this class.
     * @return LookupsController The *Singleton* instance.
     */
    public static function getInstance()
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Use the Lookups API to find information about a phone number.
     * A request to the lookups API has the following format:
     * ```/v1/lookups/phone/{phone_number}?options={carrier,type}```
     * The `{phone_number}` parameter is a required field and should be set to the phone number to be
     * looked up.
     * The options query parameter can also be used to request additional information about the phone
     * number.
     * By default, a request will only return the `country_code` and `phone_number` properties in the
     * response.
     * To request details about the the carrier, include `carrier` as a value of the options parameter.
     * To request details about the type, include `type` as a value of the options parameter. To pass
     * multiple values
     * to the options parameter, use a comma separated list, i.e. `carrier,type`.
     * A successful request to the lookups endpoint will return a response body as follows:
     * ```json
     * {
     * "country_code": "AU",
     * "phone_number": "+61491570156",
     * "type": "mobile",
     * "carrier": {
     * "name": "Telstra"
     * }
     * }
     * ```
     * Each property in the response body is defined as follows:
     * - ```country_code``` ISO ALPHA 2 country code of the phone number
     * - ```phone_number``` E.164 formatted phone number
     * - ```type``` The type of number. This can be ```"mobile"``` or ```"landline"```
     * - ```carrier``` Holds information about the specific carrier (if available)
     * - ```name``` The carrier's name as reported by the network
     *
     * @param string $phoneNumber  The phone number to be looked up
     * @param string $options      (optional) TODO: type description here
     * @return mixed response from the API call
     * @throws APIException Thrown if API call fails
     */
    public function getLookupAPhoneNumber(
        $phoneNumber,
        $options = null
    ) {

        //the base uri for api requests
        $_queryBuilder = Configuration::$BASEURI;

        //prepare query string for API call
        $_queryBuilder = $_queryBuilder.'/v1/lookups/phone/{phone_number}';

        //process optional query parameters
        $_queryBuilder = APIHelper::appendUrlWithTemplateParameters($_queryBuilder, array (
            'phone_number' => $phoneNumber,
            ));

        //process optional query parameters
        APIHelper::appendUrlWithQueryParameters($_queryBuilder, array (
            'options'      => $options,
        ));

        //validate and preprocess url
        $_queryUrl = APIHelper::cleanUrl($_queryBuilder);

        //prepare headers
        $_headers = array (
            'user-agent'    => 'messagemedia-lookups-php-sdk-1.0.0',
            'Accept'        => 'application/json'
        );


        if(strlen(Configuration::$basicAuthUserName) !== 20 || strlen(Configuration::$basicAuthPassword) !== 30) {
              echo "~~~~~ It appears as though your REST API Keys are invalid. Please check and make sure they are correct. ~~~~~";
        }
        //set HTTP basic auth parameters
        Request::auth(Configuration::$basicAuthUserName, Configuration::$basicAuthPassword);

        //call on-before Http callback
        $_httpRequest = new HttpRequest(HttpMethod::GET, $_headers, $_queryUrl);
        if ($this->getHttpCallBack() != null) {
            $this->getHttpCallBack()->callOnBeforeRequest($_httpRequest);
        }

        //and invoke the API call request to fetch the response
        $response = Request::get($_queryUrl, $_headers);

        $_httpResponse = new HttpResponse($response->code, $response->headers, $response->raw_body);
        $_httpContext = new HttpContext($_httpRequest, $_httpResponse);

        //call on-after Http callback
        if ($this->getHttpCallBack() != null) {
            $this->getHttpCallBack()->callOnAfterRequest($_httpContext);
        }

        //Error handling using HTTP status codes
        if ($response->code == 404) {
            throw new APIException('Number was invalid', $_httpContext);
        }

        //handle errors defined at the API level
        $this->validateResponse($_httpResponse, $_httpContext);

        $mapper = $this->getJsonMapper();

        return $mapper->mapClass($response->body, 'MessageMediaLookupsLib\\Models\\LookupAPhoneNumberResponse');
    }
}
