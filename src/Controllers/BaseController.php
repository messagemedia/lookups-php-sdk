<?php
/*
 * MessageMediaLookups
 */

namespace MessageMediaLookupsLib\Controllers;

use MessageMediaLookupsLib\Http\HttpCallBack;
use MessageMediaLookupsLib\Http\HttpContext;
use MessageMediaLookupsLib\Http\HttpResponse;
use MessageMediaLookupsLib\APIException;
use MessageMediaLookupsLib\Exceptions;
use \apimatic\jsonmapper\JsonMapper;
use Unirest\Request;

/**
* Base controller
*/
class BaseController
{
    /**
     * HttpCallBack instance associated with this controller
     * @var HttpCallBack
     */
    private $httpCallBack = null;

     /**
     * Constructor that sets the timeout of requests
     */

    /**
     * Set HttpCallBack for this controller
     * @param HttpCallBack $httpCallBack Http Callbacks called before/after each API call
     */
    public function setHttpCallBack(HttpCallBack $httpCallBack)
    {
        $this->httpCallBack = $httpCallBack;
    }

    /**
     * Get HttpCallBack for this controller
     * @return HttpCallBack The HttpCallBack object set for this controller
     */
    public function getHttpCallBack()
    {
        return $this->httpCallBack;
    }

    /**
     * Get a new JsonMapper instance for mapping objects
     * @return \apimatic\jsonmapper\JsonMapper JsonMapper instance
     */
    protected function getJsonMapper()
    {
        $mapper = new JsonMapper();
        return $mapper;
    }

    protected function validateResponse(HttpResponse $response, HttpContext $_httpContext)
    {
        if (($response->getStatusCode() < 200) || ($response->getStatusCode() > 208)) { //[200,208] = HTTP OK
            throw new APIException('HTTP Response Not OK', $_httpContext);
        }
    }
}
