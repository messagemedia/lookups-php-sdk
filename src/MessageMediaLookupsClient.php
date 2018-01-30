<?php
/*
 * MessageMediaLookups
 *
 * This file was automatically generated for MessageMedia by APIMATIC v2.0 ( https://apimatic.io ).
 */

namespace MessageMediaLookupsLib;

use MessageMediaLookupsLib\Controllers;

/**
 * MessageMediaLookups client class
 */
class MessageMediaLookupsClient
{
    /**
     * Constructor with authentication and configuration parameters
     */
    public function __construct(
        $basicAuthUserName = null,
        $basicAuthPassword = null
    ) {
        Configuration::$basicAuthUserName = $basicAuthUserName ? $basicAuthUserName : Configuration::$basicAuthUserName;
        Configuration::$basicAuthPassword = $basicAuthPassword ? $basicAuthPassword : Configuration::$basicAuthPassword;
    }
    /**
     * Singleton access to Lookups controller
     * @return Controllers\LookupsController The *Singleton* instance
     */
    public function getLookups()
    {
        return Controllers\LookupsController::getInstance();
    }
}
