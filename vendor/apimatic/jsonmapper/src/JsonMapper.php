<?php
/**
 * Part of JsonMapper
 *
 * PHP version 5
 *
 * @category Netresearch
 * @package  JsonMapper
 * @author   Christian Weiske <christian.weiske@netresearch.de>
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 * @link     http://www.netresearch.de/
 */

namespace apimatic\jsonmapper;

/**
 * Automatically map JSON structures into objects.
 *
 * @category Netresearch
 * @package  JsonMapper
 * @author   Christian Weiske <christian.weiske@netresearch.de>
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 * @link     http://www.netresearch.de/
 */
class JsonMapper
{
    /**
     * PSR-3 compatible logger object
     *
     * @link http://www.php-fig.org/psr/psr-3/
     * @var  object
     * @see  setLogger()
     */
    protected $logger;

    /**
     * Throw an exception when JSON data contain a property
     * that is not defined in the PHP class
     *
     * @var boolean
     */
    public $bExceptionOnUndefinedProperty = false;

    /**
     * Calls this method on the PHP class when an undefined property
     * is found. This method should receive two arguments, $key
     * and $value for the property key and value. Only works if
     * $bExceptionOnUndefinedProperty is set to false.
     *
     * @var string
     */
    public $sAdditionalPropertiesCollectionMethod = null;

    /**
     * Throw an exception if the JSON data miss a property
     * that is marked with @required in the PHP class
     *
     * @var boolean
     */
    public $bExceptionOnMissingData = false;

    /**
     * If the types of map() parameters shall be checked.
     * You have to disable it if you're using the json_decode "assoc" parameter.
     *
     *     `json_decode($str, false)`
     *
     * @var boolean
     */
    public $bEnforceMapType = true;

    /**
     * Contains user provided map of class names vs their child classes.
     * This is only needed if discriminators are to be used. PHP reflection is not
     * used to get child classes because most code bases use autoloaders where
     * classes are lazily loaded.
     * 
     * @var array
     */
    public $arChildClasses = array();

    /**
     * Runtime cache for inspected classes. This is particularly effective if
     * mapArray() is called with a large number of objects
     *
     * @var array property inspection result cache
     */
    protected $arInspectedClasses = array();

    /**
     * Map data all data in $json into the given $object instance.
     *
     * @param object $json   JSON object structure from json_decode()
     * @param object $object Object to map $json data into
     *
     * @return object Mapped object is returned.
     * @see    mapArray()
     */
    public function map($json, $object)
    {
        if ($this->bEnforceMapType && !is_object($json)) {
            throw new \InvalidArgumentException(
                'JsonMapper::map() requires first argument to be an object'
                . ', ' . gettype($json) . ' given.'
            );
        }
        if (!is_object($object)) {
            throw new \InvalidArgumentException(
                'JsonMapper::map() requires second argument to be an object'
                . ', ' . gettype($object) . ' given.'
            );
        }

        $strClassName = get_class($object);
        $rc = new \ReflectionClass($object);
        $strNs = $rc->getNamespaceName();
        $providedProperties = array();
        $additionalPropertiesMethod = $this->getAdditionalPropertiesMethod($rc);

        foreach ($json as $key => $jvalue) {
            // $providedProperties[$key] = true;
            $isAdditional = false;

            // Store the property inspection results so we don't have to do it
            // again for subsequent objects of the same type
            if (!isset($this->arInspectedClasses[$strClassName][$key])) {
                $this->arInspectedClasses[$strClassName][$key]
                    = $this->inspectProperty($rc, $key);
            }

            list($hasProperty, $accessor, $type, $factoryMethod)
                = $this->arInspectedClasses[$strClassName][$key];

            if ($accessor !== null) {
                $providedProperties[$accessor->getName()] = true; 
            }

            if (!$hasProperty) {
                if ($this->bExceptionOnUndefinedProperty) {
                    throw new JsonMapperException(
                        'JSON property "' . $key . '" does not exist'
                        . ' in object of type ' . $strClassName
                    );
                }
                $isAdditional = true;
                $this->log(
                    'info',
                    'Property {property} does not exist in {class}',
                    array('property' => $key, 'class' => $strClassName)
                );
            }

            if ($accessor === null) {
                if ($this->bExceptionOnUndefinedProperty) {
                    throw new JsonMapperException(
                        'JSON property "' . $key . '" has no public setter method'
                        . ' in object of type ' . $strClassName
                    );
                }
                $isAdditional = true;
                $this->log(
                    'info',
                    'Property {property} has no public setter method in {class}',
                    array('property' => $key, 'class' => $strClassName)
                );
            }

            if ($isAdditional) {
                if ($additionalPropertiesMethod !== null) {
                    $additionalPropertiesMethod->invoke($object, $key, $jvalue); 
                }
                continue;
            }

            //use factory method generated value if factory provided
            if ($factoryMethod !== null) {
                if (!is_callable($factoryMethod)) {
                    throw new JsonMapperException(
                        'Factory method "' . $factoryMethod . '" referenced by "'
                        . $strClassName . '" is not callable'
                    );
                }
                $factoryValue = call_user_func($factoryMethod, $jvalue);
                $this->setProperty($object, $accessor, $factoryValue);
                continue;
            }

            if ($this->isNullable($type)) {
                if ($jvalue === null) {
                    $this->setProperty($object, $accessor, null);
                    continue;
                }
                $type = $this->removeNullable($type);
            }

            if ($type === null || $type === 'mixed') {
                //no given type - simply set the json data
                $this->setProperty($object, $accessor, $jvalue);
                continue;
            } else if ($this->isObjectOfSameType($type, $jvalue)) {
                $this->setProperty($object, $accessor, $jvalue);
                continue;
            } else if ($this->isSimpleType($type)) {
                settype($jvalue, $type);
                $this->setProperty($object, $accessor, $jvalue);
                continue;
            }

            //FIXME: check if type exists, give detailled error message if not
            if ($type === '') {
                throw new JsonMapperException(
                    'Empty type at property "'
                    . $strClassName . '::$' . $key . '"'
                );
            }

            $array = null;
            $subtype = null;
            if (substr($type, -2) == '[]') {
                //array
                $array = array();
                $subtype = substr($type, 0, -2);
            } else if (substr($type, -1) == ']') {
                list($proptype, $subtype) = explode('[', substr($type, 0, -1));
                if (!$this->isSimpleType($proptype)) {
                    $proptype = $this->getFullNamespace($proptype, $strNs);
                }
                $array = $this->createInstance($proptype);
            } else if ($type == 'ArrayObject'
                || is_subclass_of($type, 'ArrayObject')
            ) {
                $array = $this->createInstance($type);
            }

            if ($array !== null) {
                if (!$this->isSimpleType($subtype)) {
                    $subtype = $this->getFullNamespace($subtype, $strNs);
                }
                if ($jvalue === null) {
                    $child = null;
                } else if ($this->isRegisteredType(
                    $this->getFullNamespace($subtype, $strNs)
                )
                ) {
                    $child = $this->mapClassArray($jvalue, $subtype);
                } else {
                    $child = $this->mapArray($jvalue, $array, $subtype);
                }
            } else if ($this->isFlatType(gettype($jvalue))) {
                //use constructor parameter if we have a class
                // but only a flat type (i.e. string, int)
                if ($jvalue === null) {
                    $child = null;
                } else {
                    $type = $this->getFullNamespace($type, $strNs);
                    $child = $this->createInstance($type, true, $jvalue);
                }
            } else if ($this->isRegisteredType(
                $this->getFullNamespace($type, $strNs)
            )
            ) {
                $type = $this->getFullNamespace($type, $strNs);
                $child = $this->mapClass($jvalue, $type);
            } else {
                $type = $this->getFullNamespace($type, $strNs);
                $child = $this->createInstance($type);
                $this->map($jvalue, $child);
            }
            $this->setProperty($object, $accessor, $child);
        }

        if ($this->bExceptionOnMissingData) {
            $this->checkMissingData($providedProperties, $rc);
        }

        return $object;
    }

    /**
     * Map all data in $json into a new instance of $type class.
     *
     * @param object|null $json JSON object structure from json_decode()
     * @param string      $type The type of class instance to map into.
     *
     * @return object|null      Mapped object is returned.
     * @see    mapClassArray()
     */
    public function mapClass($json, $type)
    {
        if ($json === null) {
            return null;
        }

        if (!is_object($json)) {
            throw new \InvalidArgumentException(
                'JsonMapper::mapClass() requires first argument to be an object'
                . ', ' . gettype($json) . ' given.'
            );
        }

        if (!class_exists($type)) {
            throw new \InvalidArgumentException(
                'JsonMapper::mapClass() requires second argument to be a class name'
                . ', ' . $type . ' given.'
            );
        }

        $ttype = ltrim($type, "\\");
        $rc = new \ReflectionClass($ttype);

        //try and find a class with matching discriminator
        $instance = $this->getDiscriminatorMatch($json, $rc);

        //otherwise fallback to an instance of $type class
        if ($instance === null) {
            $instance = $this->createInstance($ttype);
        }

        return $this->map($json, $instance);
    }

    /**
     * Get class instance that best matches the class
     * 
     * @param object|null      $json JSON object structure from json_decode()
     * @param \ReflectionClass $rc   Class to get instance of. This method
     *                                will try to first match the discriminator
     *                                field with the discriminator value of
     *                                the current class or its child class.
     *                                If no matches is found, then the current
     *                                class's instance is returned.
     *                                
     * @return object|null           Object instance if match is found.
     */
    protected function getDiscriminatorMatch($json, $rc)
    {
        $discriminator = $this->getDiscriminator($rc);
        if ($discriminator) {
            list($fieldName, $fieldValue) = $discriminator;
            if (isset($json->{$fieldName}) && $json->{$fieldName} === $fieldValue) {
                return $rc->newInstance();
            }
            if (!$this->isRegisteredType($rc->name)) {
                return null;
            }
            foreach ($this->getChildClasses($rc) as $clazz) {
                $instance = $this->getDiscriminatorMatch($json, $clazz);
                if ($instance) {
                    return $instance;
                }
            }
        } else {
            return null;
        }
    }

    /**
     * Get discriminator info
     * 
     * @param \ReflectionClass $rc ReflectionClass of class to inspect
     * 
     * @return array|null          An array with discriminator arguments
     *                             Element 1 is discriminator field name
     *                             and element 2 is discriminator value.
     */
    protected function getDiscriminator($rc)
    {
        $annotations = $this->parseAnnotations($rc->getDocComment());
        $annotationInfo = array();
        if (isset($annotations['discriminator'])) {
            $annotationInfo[0] = trim($annotations['discriminator'][0]);
            if (isset($annotations['discriminatorType'])) {
                $annotationInfo[1] = trim($annotations['discriminatorType'][0]);
            } else {
                $annotationInfo[1] = $rc->getShortName();
            }
            return $annotationInfo;
        }
        return null;
    }

    /**
     * Get child classes from a ReflectionClass
     * 
     * @param \ReflectionClass $rc ReflectionClass of class to inspect
     * 
     * @return \ReflectionClass[]  ReflectionClass instances for child classes
     */
    protected function getChildClasses($rc)
    {
        $children  = array();
        foreach ($this->arChildClasses[$rc->name] as $class) {
            $child = new \ReflectionClass($class);
            if ($rc->isInstance($child->newInstance())) {
                $children[] = $child;
            }
        }
        return $children;
    }

    /**
     * Convert a type name to a fully namespaced type name.
     *
     * @param string $type  Type name (simple type or class name)
     * @param string $strNs Base namespace that gets prepended to the type name
     *
     * @return string Fully-qualified type name with namespace
     */
    protected function getFullNamespace($type, $strNs)
    {
        if ($type !== '' && $type{0} != '\\') {
            //create a full qualified namespace
            if ($strNs != '') {
                $type = '\\' . $strNs . '\\' . $type;
            }
        }
        return $type;
    }

    /**
     * Check required properties exist in json
     *
     * @param array  $providedProperties array with json properties
     * @param object $rc                 Reflection class to check
     *
     * @throws JsonMapperException
     *
     * @return void
     */
    protected function checkMissingData($providedProperties, \ReflectionClass $rc)
    {
        foreach ($rc->getProperties() as $property) {
            $rprop = $rc->getProperty($property->name);
            $docblock = $rprop->getDocComment();
            $annotations = $this->parseAnnotations($docblock);
            if (isset($annotations['required'])
                && !isset($providedProperties[$property->name])
            ) {
                throw new JsonMapperException(
                    'Required property "' . $property->name . '" of class '
                    . $rc->getName()
                    . ' is missing in JSON data'
                );
            }
        }
    }

    /**
     * Get additional properties setter method for the class.
     *
     * @param \ReflectionClass $rc Reflection class to check
     * 
     * @return \ReflectionMethod    Method or null if disabled.
     */
    protected function getAdditionalPropertiesMethod(\ReflectionClass $rc)
    {
        if ($this->bExceptionOnUndefinedProperty === false
            && $this->sAdditionalPropertiesCollectionMethod !== null
        ) {
            $additionalPropertiesMethod = null;
            try {
                $additionalPropertiesMethod 
                    = $rc->getMethod($this->sAdditionalPropertiesCollectionMethod);
                if (!$additionalPropertiesMethod->isPublic()) {
                    throw new  \InvalidArgumentException(
                        $this->sAdditionalPropertiesCollectionMethod . 
                        " method is not public on the given class."
                    ); 
                }
                if ($additionalPropertiesMethod->getNumberOfParameters() < 2) {
                    throw new  \InvalidArgumentException(
                        $this->sAdditionalPropertiesCollectionMethod . 
                        " method does not receive two args, $key and $value."
                    ); 
                }
            } catch (\ReflectionException $e) {
                throw new  \InvalidArgumentException(
                    $this->sAdditionalPropertiesCollectionMethod . 
                    " method is not available on the given class."
                );
            }
            return $additionalPropertiesMethod;
        } else {
            return null;
        }
    }

    /**
     * Map an array
     *
     * @param array         $json  JSON array structure from json_decode()
     * @param mixed         $array Array or ArrayObject that gets filled with
     *                             data from $json
     * @param string|object $class Class name for children objects.
     *                             All children will get mapped onto this type.
     *                             Supports class names and simple types
     *                             like "string".
     *
     * @return mixed Mapped $array is returned
     */
    public function mapArray($json, $array, $class = null)
    {
        foreach ($json as $key => $jvalue) {
            if ($class === null) {
                $array[$key] = $jvalue;
            } else if ($this->isFlatType(gettype($jvalue))) {
                //use constructor parameter if we have a class
                // but only a flat type (i.e. string, int)
                if ($jvalue === null) {
                    $array[$key] = null;
                } else {
                    if ($this->isSimpleType($class)) {
                        settype($jvalue, $class);
                        $array[$key] = $jvalue;
                    } else {
                        $array[$key] = $this->createInstance(
                            $class, true, $jvalue
                        );
                    }
                }
            } else {
                $array[$key] = $this->map(
                    $jvalue, $this->createInstance($class)
                );
            }
        }
        return $array;
    }

    /**
     * Map an array
     * 
     * @param array|null $jsonArray JSON array structure from json_decode()
     * @param string     $type      Class name
     * 
     * @return array                A new array containing object of $type
     *                              which is mapped from $jsonArray
     */
    public function mapClassArray($jsonArray, $type)
    {
        if ($jsonArray === null) {
            return null;
        }

        $array = array();
        foreach ($jsonArray as $key => $jvalue) {
            $array[$key] = $this->mapClass($jvalue, $type);
        }

        return $array;
    }

    /**
     * Try to find out if a property exists in a given class.
     * Checks property first, falls back to setter method.
     *
     * @param object $rc   Reflection class to check
     * @param string $name Property name
     *
     * @return array First value: if the property exists
     *               Second value: the accessor to use (
     *                 ReflectionMethod or ReflectionProperty, or null)
     *               Third value: type of the property
     *               Fourth value: factory method
     */
    protected function inspectProperty(\ReflectionClass $rc, $name)
    {
        //try setter method first
        $setter = 'set' . str_replace(
            ' ', '', ucwords(str_replace('_', ' ', $name))
        );
        if ($rc->hasMethod($setter)) {
            $rmeth = $rc->getMethod($setter);
            if ($rmeth->isPublic()) {
                $rparams = $rmeth->getParameters();
                if (count($rparams) > 0) {
                    $pclass = $rparams[0]->getClass();
                    if ($pclass !== null) {
                        return array(
                            true, $rmeth, '\\' . $pclass->getName(), null
                        );
                    }
                }

                $docblock    = $rmeth->getDocComment();
                $annotations = $this->parseAnnotations($docblock);

                if (!isset($annotations['param'][0])) {
                    return array(true, $rmeth, null, null);
                }
                list($type) = explode(' ', trim($annotations['param'][0]));
                return array(true, $rmeth, $type, null);
            }
        }

        $rprop = null;
        // check for @maps annotation for hints
        foreach ($rc->getProperties(\ReflectionProperty::IS_PUBLIC) as $p) {
            $mappedName = $this->getMapAnnotation($p);
            if ($mappedName !== null && $name == $mappedName) {
                $rprop = $p;
                break;
            }
        }

        //now try to set the property directly
        if ($rprop === null) {
            if ($rc->hasProperty($name) 
                && $this->getMapAnnotation($rc->getProperty($name)) === null
            ) {
                $rprop = $rc->getProperty($name);
            } else {
                //case-insensitive property matching
                foreach ($rc->getProperties(\ReflectionProperty::IS_PUBLIC) as $p) {
                    if ((strcasecmp($p->name, $name) === 0) 
                        && $this->getMapAnnotation($p) === null
                    ) {
                        $rprop = $p;
                        break;
                    }
                }
            }
        }

        if ($rprop !== null) {
            if ($rprop->isPublic()) {
                $docblock      = $rprop->getDocComment();
                $annotations   = $this->parseAnnotations($docblock);
                $type          = null;
                $factoryMethod = null;

                //support "@var type description"
                if (isset($annotations['var'][0])) {
                    list($type) = explode(' ', $annotations['var'][0]);
                }

                //support "@factory method_name"
                if (isset($annotations['factory'][0])) {
                    list($factoryMethod) = explode(' ', $annotations['factory'][0]);
                }

                return array(true, $rprop, $type, $factoryMethod);
            } else {
                //no setter, private property
                return array(true, null, null, null);
            }
        }

        //no setter, no property
        return array(false, null, null, null);
    }

    /**
     * Get map annotation value for a property
     * 
     * @param object $property Property of a class
     * 
     * @return string|null      Map annotation value
     */
    protected function getMapAnnotation($property)
    {
        $annotations = $this->parseAnnotations($property->getDocComment());
        if (isset($annotations['maps'][0])) {
            return $annotations['maps'][0];
        }
        return null;
    }

    /**
     * Set a property on a given object to a given value.
     *
     * Checks if the setter or the property are public are made before
     * calling this method.
     *
     * @param object $object   Object to set property on
     * @param object $accessor ReflectionMethod or ReflectionProperty
     * @param mixed  $value    Value of property
     *
     * @return void
     */
    protected function setProperty(
        $object, $accessor, $value
    ) {
        if ($accessor instanceof \ReflectionProperty) {
            $object->{$accessor->getName()} = $value;
        } else {
            $object->{$accessor->getName()}($value);
        }
    }

    /**
     * Create a new object of the given type.
     *
     * This method exists to be overwritten in child classes,
     * so you can do dependency injection or so.
     *
     * @param string  $class        Class name to instantiate
     * @param boolean $useParameter Pass $parameter to the constructor or not
     * @param mixed   $parameter    Constructor parameter
     *
     * @return object Freshly created object
     */
    public function createInstance(
        $class, $useParameter = false, $parameter = null
    ) {
        if ($useParameter) {
            return new $class($parameter);
        } else {
            return new $class();
        }
    }

    /**
     * Checks if the given type is a "simple type"
     *
     * @param string $type type name from gettype()
     *
     * @return boolean True if it is a simple PHP type
     */
    protected function isSimpleType($type)
    {
        return $type == 'string'
            || $type == 'boolean' || $type == 'bool'
            || $type == 'integer' || $type == 'int'   || $type == 'float'
            || $type == 'double'  || $type == 'array' || $type == 'object';
    }

    /**
     * Checks if the object is of this type or has this type as one of its parents
     *
     * @param string $type  class name of type being required
     * @param mixed  $value Some PHP value to be tested
     *
     * @return boolean True if $object has type of $type
     */
    protected function isObjectOfSameType($type, $value)
    {
        if (false === is_object($value)) {
            return false;
        }

        return is_a($value, $type);
    }

    /**
     * Checks if the given type is a type that is not nested
     * (simple type except array and object)
     *
     * @param string $type type name from gettype()
     *
     * @return boolean True if it is a non-nested PHP type
     */
    protected function isFlatType($type)
    {
        return $type == 'NULL'
            || $type == 'string'
            || $type == 'boolean' || $type == 'bool'
            || $type == 'integer' || $type == 'int'
            || $type == 'double';
    }

    /**
     * Is type registered with mapper
     * 
     * @param string $type Class name
     *                      
     * @return boolean     True if registered with $this->arChildClasses
     */
    protected function isRegisteredType($type)
    {
        return isset($this->arChildClasses[ltrim($type, "\\")]);
    }

    /**
     * Checks if the given type is nullable
     *
     * @param string $type type name from the phpdoc param
     *
     * @return boolean True if it is nullable
     */
    protected function isNullable($type)
    {
        return stripos('|' . $type . '|', '|null|') !== false;
    }

    /**
     * Remove the 'null' section of a type
     *
     * @param string $type type name from the phpdoc param
     *
     * @return string The new type value
     */
    protected function removeNullable($type)
    {
        return substr(
            str_ireplace('|null|', '|', '|' . $type . '|'),
            1, -1
        );
    }

    /**
     * Copied from PHPUnit 3.7.29, Util/Test.php
     *
     * @param string $docblock Full method docblock
     *
     * @return array
     */
    protected static function parseAnnotations($docblock)
    {
        $annotations = array();
        // Strip away the docblock header and footer
        // to ease parsing of one line annotations
        $docblock = substr($docblock, 3, -2);

        $re = '/@(?P<name>[A-Za-z_-]+)(?:[ \t]+(?P<value>.*?))?[ \t]*\r?$/m';
        if (preg_match_all($re, $docblock, $matches)) {
            $numMatches = count($matches[0]);

            for ($i = 0; $i < $numMatches; ++$i) {
                $annotations[$matches['name'][$i]][] = $matches['value'][$i];
            }
        }

        return $annotations;
    }

    /**
     * Log a message to the $logger object
     *
     * @param string $level   Logging level
     * @param string $message Text to log
     * @param array  $context Additional information
     *
     * @return null
     */
    protected function log($level, $message, array $context = array())
    {
        if ($this->logger) {
            $this->logger->log($level, $message, $context);
        }
    }

    /**
     * Sets a logger instance on the object
     *
     * @param LoggerInterface $logger PSR-3 compatible logger object
     *
     * @return null
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }
}
?>
