<?php
/**
 *
 * This is how a Mura_Roteador_Rota shoud be instanciated
 * Once instantiated, this should more likely be added to a router.
 *
 *
 * new Mura_Roteador_Rota(
 *	 '/:lang/customer/:customer-name',
 *   array(
 *       'lang'=>'pt',
 *        'load-file'=>'customer.php'
 *	 ),
 *   array(
 *      'lang'=>'(en|pt|es)'
 *   )
 * );
 *
 * @author Marcelo Guilherme Jacobus Jr (marcelo.jacobus@gmail.com)
 */
class Mura_Router_Route
{

    /**
     * The array key is the parameter name and the value
     * is the regular expression to validate it.
     * @var array
     */
    protected $_requirements = array();

    /**
     * Gets the url parts
     * @var array
     */
    protected $_expectedParts = array();


    /**
     * @var array the variables from the url
     */
    protected $_requestParts = array();

    /**
     * Receives the name of the variable parts of the url.
     * The array index is set to the sequence the variable should appear.
     * for instance if the url pattern is /:lang/customer/:customer-name
     * the $_variables become array(0 => 'lang', 2 => 'customer-name')
     * because lang parameter is the first (index 0) parameter and customer-name
     * is the third (index 2)
     *
     * @var array
     */
    protected $_variables = array();

    /**
     * Default values added to the route.
     * @var array
     */
    protected $_predefinedValues = array();

    /**
     * If the route is valid, it stores the parameters gotten from the url.
     * @var array
     */
    protected $_parameters = array();

    /**
     * The url pattern
     * @var string
     */
    protected $_urlPattern;

    /**
     * The router
     * @var Mura_Roteador
     */
    protected $_router;

    /**
     * Se deve aceitar parametros extras
     * @var bool
     */
    protected $_allowExtraParameters = false;

    /**
     *
     * Uma rota recebe como o primeiro parametro o padrao da url
     * Exemplo /clientes/:nome-cliente/*
     *
     * @param string $url a url com variaveis separadas por ':'
     * @param array $predefinedValues
     * @param array $requirements
     */
    public function __construct($urlPattern,  $predefinedValues = array(), array $requirements = array())
    {
        $this->setUrlPattern($urlPattern);

        $this->_predefinedValues = $predefinedValues;

        $this->_requirements =  $requirements;

        $this->_router = Mura_Router::getInstance();
    }

    /**
     * Gets base url
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->_router->getBaseUrl();
    }

    /**
     * Sets a parameter
     * @param string $name
     * @param string $value
     * @return Mura_Router_Route_Exception
     */
    public function setParam($name,  $value)
    {
        $this->_parameters[$name] = urldecode($value);
        return $this;
    }

    /**
     * Sets a parameters
     * @param array $params key being name and value, value.
     * @return Mura_Router_Route_Exception
     */
    function setParams($params)
    {
        foreach ($params as $name => $value) {
            $this->setParam($name, $value);
        }
        return $this;
    }

    /**
     * Return route parameters gotten from the request.
     * Throws exception if route is not a match.
     *
     * @return array
     * @throws Mura_Router_Route_Exception
     */
    public function getParams()
    {
        return $this->_parameters;
    }

    /**
     * Get parameter by name
     *
     * @param string $name parameter name
     * @param mixed $valor default value to be returned. false if none
     * @return string
     */
    public function getParam($name, $default = false)
    {
        if (isset($this->_parameters[$name]) && array_key_exists($name, $this->_parameters)) {
            $value =  $this->_parameters[$name];
            if ($value && $value != '') {
                return $value;
            }
        }
        return $default;
    }

    /**
     * Sets the expected params based on the pattern
     * @return Mura_Router_Route
     */
    private function _setExpectedParts()
    {
        //remove leading slashes from the url pattern
        $urlPattern = trim($this->getUrlPattern(),'/');
        $this->_expectedParts = explode('/', $urlPattern);
        return $this;
    }

    /**
     * Get expected parts. In order.
     * @return array
     */
    private function getExpectedParts()
    {
        return $this->_expectedParts;
    }

    /**
     * set the requested parts
     * @return Mura_Router_Route
     */
    private function _setRequestParts()
    {
        $request = explode('?',$this->_router->getRequestUri());
        $request = $request[0];

        $baseUrl = $this->getBaseUrl();
        if ($baseUrl != '/') {
            $request = explode(trim($baseUrl,'/'), $request);
            $request = $request[1];
        }

        $this->_requestParts = explode('/',trim($request,'/'));
        return $this;
    }

    /**
     * get request parts
     * @return array
     */
    public function getRequestParts()
    {
        return $this->_requestParts;
    }

    /**
     * return first char of a string
     * @param array $string
     * @return string
     */
    private function _getFirstChar($string)
    {
        return substr($string, 0, 1);
    }

    /**
     * set veriables on its positions
     * @return Mura_Router_Route
     */
    private function _setVariables()
    {
        foreach ($this->getExpectedParts() as $index => $part) {
            // checks the variable parts
            if ($this->_getFirstChar($part) == ':') {
                $variable = substr($part, 1);
                $this->_variables[$index] = $variable;
            }
        }
        return $this;
    }

    private function _isVar($partName)
    {
        return ($this->_getFirstChar($partName) == ':');
    }

    private function _isStatic($partName)
    {
        $firstChar = $this->_getFirstChar($partName);
        return ( $firstChar != '_' && $firstChar != '*');
    }

    /**
     * Checks the request and if it matches this route, adds its parameters to
     * the parameter collection.
     * @return Mura_Router_Route
     * @throws Mura_Router_Route_Exception when route is not valid
     */
    public function validate()
    {
        $this->_setExpectedParts();
        $this->_setRequestParts();

        //limits parameters
        if ((false == $this->allowExtraParams()) && (count($this->getRequestParts()) > count($this->getExpectedParts()))) {
            $this->_throwException();
        }

        //set default values
        $this->setParams($this->_predefinedValues)->_setVariables();

        foreach ($this->getExpectedParts() as $index => $exectedPart) {
            if ($this->_isVar($exectedPart)) {

                $variable = $this->_variables[$index];
                $value = $this->_requestParts[$index];

                //:expected variables must not be blank
                if ($this->isValid($variable,$value) && strlen($value) > 0 ) {
                    $this->setParam($variable,$value);
                } else {
                    $this->_throwException();
                }

                //Checks the non variable parts
            } else if ($this->_isStatic($exectedPart)) {
                if ($exectedPart != $this->_requestParts[$index]) {
                    $this->_throwException();
                }
            }
        }

        //add non predifined parameters
        $this->_addExtraParameters();

        return true;
    }

    /**
     * Set the Url Pattern
     * @return Mura_Router_Route
     */
    public function setUrlPattern($pattern)
    {
        $this->_urlPattern = $pattern;
        $lastCharPosition = (strlen($pattern) - 1);
        $lastChar = substr($pattern, $lastCharPosition, 1);
        $this->_allowExtraParameters = ($lastChar == '*');

        return $this;
    }

    /**
     * Get the Url Pattern
     * @return string
     */
    public function getUrlPattern()
    {
        return $this->_urlPattern;
    }

    /**
     * Checks wheter the url allows slash separated extra parameters
     * @return bool
     */
    protected function allowExtraParams()
    {
        return $this->_allowExtraParameters;
    }

    /**
     *
     * @return Mura_Router_Route
     */
    protected function _throwException($message = 'Invalid Route')
    {
        require_once 'Mura/Router/Route/Exception.php';
        throw new Mura_Router_Route_Exception($message);
    }

    /**
     * Adds non predefined parameters that might be prepended as $_GET or $_POST.
     * Parameters are not overriden.
     * vars.
     * @return Mura_Router_Route
     */
    protected function _addExtraParameters()
    {
        $firstExtraParameterIndex = count($this->getExpectedParts());
        // if allowd extraparameters, it means the first extra parameter
        // is actually the second index(*)
        if ($this->allowExtraParams()) {
            --$firstExtraParameterIndex;
        }

        $extraParams = array_slice($this->getRequestParts(), $firstExtraParameterIndex);
        $size = count($extraParams);

        /**
         * Extra params are built as key/value on the url, so it must go throw
         * 2 by 2
         */
        for($i = 0; $i < $size; $i+=2 ) {
            if (array_key_exists($i, $extraParams)) {
                $name = $extraParams[$i];
                $valueKey = $i + 1;
                if (array_key_exists($valueKey, $extraParams)) {
                    $value = $extraParams[$valueKey];
                } else {
                    $value = false;
                }
                $this->setParam($name,$value);
            }
        }

        //If not set at the request, adds default values.
        foreach ($this->_predefinedValues as $name => $value) {
            if ($this->canOverride($name)) {
                $this->setParam($name, $value);
            }
        }

        // Adds query string variables. Does not override.
        if (isset($_GET)) {
            foreach ($_GET as $name => $value) {
                if ($this->canOverride($name)) {
                    $this->setParam($name,$value);
                }
            }
        }

        // Adds $_POST variables. Does not override.
        if (isset($_POST)) {
            foreach ($_POST as $name => $value) {
                if ($this->canOverride($name)) {
                    $this->setParam($name,$value);
                }
            }
        }
        return $this;
    }

    /**
     * Check whether the parameter is valid
     * @param string $name
     * @param string $value
     * @return bool
     */
    public function isValid($name, $value)
    {
        if (array_key_exists($name, $this->_requirements)) {
            $pattern = '/^' . $this->_requirements[$name] . '$/';
            if (!preg_match($pattern, $value)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Checks wheter a parameter can have its value changed
     * @param string $name parameter name
     * @return bool
     */
    public function canOverride($name)
    {
        $firstChar = $this->_getFirstChar($name);
        if (($firstChar == '_') && ((isset($this->_parameters[$name]))  && ($this->_parameters[$name] !== ''))) {
            return false;
        }
        return true;
    }

    /**
     * Get a url with required params
     * @param array $params
     * @return string
     * @throws Mura_Router_Route_Exception when required parameter is not valid
     */
    public function getUrl(array $params = array())
    {
        $default = $this->_predefinedValues;

        foreach ($params as $name => $value) {
            if (!$this->isValid($name, $value)) {
                $this->_throwException('Parameter "' . $name . '" does not match pattern ' . $this->_requirements[$name] . '.');
            }
            $default[$name] = $value;
        }

        $params = $default;

        $url = trim($this->getUrlPattern(),'/') . '/';
        $slashParams = array();

        foreach ($params as $name => $value) {

            //protected param
            if ($this->_getFirstChar($name) == '_') {
                unset($params[$name]);
                continue;
            }

            if (strpos($url,":$name") !== false) {

                if (strlen($value) > 0) {
                    $url = str_replace(":$name", $this->encode($value), $url);
                    unset($params[$name]);
                } else {
                    $this->_throwException();
                }
            } else if ($this->allowExtraParams()) {
                if ($value !== '') {
                    $slashParams[] = $name . '/' . $this->encode($value);
                    unset($params[$name]);
                }
            }
        }

        //verifies if required parameter is missing
        if (strpos($url,':') !== false) {
            $this->_throwException('One or more required parameters are missing.');
        }

        $url .= implode('/',$slashParams);
        $queryString = str_replace('&amp;','&',http_build_query($params));
        if (strlen($queryString)) {
            $url .= '?' . $queryString;
        }

        //removes *
        $url = preg_replace('/(\*\/|\*)/','',$url);

        return $url;
    }

    public function encode($string)
    {
        return urlencode($string);
    }
}