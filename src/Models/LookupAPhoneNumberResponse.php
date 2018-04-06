<?php
/*
 * MessageMediaLookups
 */

namespace MessageMediaLookupsLib\Models;

use JsonSerializable;

/**
 * @todo Write general description for this model
 */
class LookupAPhoneNumberResponse implements JsonSerializable
{
    /**
     * @todo Write general description for this property
     * @required
     * @maps country_code
     * @var string $countryCode public property
     */
    public $countryCode;

    /**
     * @todo Write general description for this property
     * @required
     * @maps phone_number
     * @var string $phoneNumber public property
     */
    public $phoneNumber;

    /**
     * @todo Write general description for this property
     * @required
     * @var string $type public property
     */
    public $type;

    /**
     * @todo Write general description for this property
     * @required
     * @var object $carrier public property
     */
    public $carrier;

    /**
     * Constructor to set initial or default values of member properties
     * @param string $countryCode Initialization value for $this->countryCode
     * @param string $phoneNumber Initialization value for $this->phoneNumber
     * @param string $type        Initialization value for $this->type
     * @param object $carrier     Initialization value for $this->carrier
     */
    public function __construct()
    {
        if (4 == func_num_args()) {
            $this->countryCode = func_get_arg(0);
            $this->phoneNumber = func_get_arg(1);
            $this->type        = func_get_arg(2);
            $this->carrier     = func_get_arg(3);
        }
    }


    /**
     * Encode this object to JSON
     */
    public function jsonSerialize()
    {
        $json = array();
        $json['country_code'] = $this->countryCode;
        $json['phone_number'] = $this->phoneNumber;
        $json['type']         = $this->type;
        $json['carrier']      = $this->carrier;

        return $json;
    }
}
