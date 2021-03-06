<?php

require_once 'Mura/Router/Route.php';

/**
 *
 * Mura_Router_Abstract is a collection that manages its routes and verifies
 * which one matches the requested url
 *
 * It must be instatiated via singleton pattern in order to be accessed in
 * wherever you are inside your application.
 *
 * $router = Mura_Router::getInstance();
 *
 * Mura_Router currently does not support base url auto discovery so if you are
 * not working on the root of your web application you should explicity
 * inform it as follow.
 *
 * $router->setBaseUrl('/my-projects/mura-solution');
 *
 * By default it mapps the baseUrl to '/'
 *
 * $this is the way you add a new route to your router:
 *
 * $router->addRoute($route);
 * $router->addRoute($route2);
 * $router->addRoute($route3);
 *
 * You should issue the following command in order to get the matching route.
 *
 * $route = $router->getRoute();
 *
 * If no route is mached, a exception is thrown.
 *
 * You can access all the route parameters by doing:
 * $params = $route->getParams();
 *
 * Or you can get just one, if you will
 *
 * //www.example.com/some-route/?name=Mura
 * $param = $route->getParam('name','Marcelo');
 *
 * In the above example, if no name was given, Marcelo it is.
 *
 * @author Marcelo Guilherme Jacobus Jr (marcelo.jacobus@gmail.com)
 */
abstract class Mura_Router_Abstract
{

    /**
     * Mura_Router_Abstract
     */
    protected static $_instance = null;

    /**
     * Route Collection
     * @var array Colecao de Mura_Router_Route
     */
    protected $_routes = array();

    /**
     * Once the matching route is found, it is set to this property
     * @var Mura_Router_Route
     */
    protected $_route = null;

    /**
     * Url base da site
     * @var string
     */
    protected $_baseUrl = '/';

    /**
     * @var string
     */
    protected $_requestUri;

    /**
     * constructor
     */
    public function  __construct()
    {
    }


    /**
     * Set base url
     * @param string $base the application base uri
     * @return Mura_Router_Abstract
     */
    public function setBaseUrl($base)
    {
        if ($base != '/'){
            $base = '/' . trim($base,'/') . '/';
        }
        $this->_baseUrl = $base;
        return $this;
    }

    /**
     * Adds a Route.
     * Each route must have a unique name. If the given name exists, an
     * Exception is throwwn.
     *
     * @param string $routeName
     * @param Mura_Router_Route $route
     * @return Mura_Router_Abstract
     * @throws
     */
    public function addRoute($routeName, Mura_Router_Route $route)
    {
        if (isset($this->_routes[$routeName])) {
            require_once 'Mura/Router/Exception.php';
            throw new Mura_Router_Exception('A route with name "' . $routeName . '" was already taken');
        }
        $this->_routes[$routeName] = $route;
        return $this;
    }

    /**
     * Get the matching route.
     * If none is found, throws a Mura_Router_Route_Exception
     *
     * @return Mura_Router_Route
     * @throws Mura_Router_Route_Exception
     */
    public function getRoute()
    {
        if ($this->_route == null) {
            foreach($this->_routes as $route) {
                try {
                    $route->validate();
                    $this->_route = $route;
                    return $this->_route;
                } catch (Mura_Router_Route_Exception $e) {}
            }
            throw new Mura_Router_Exception('No route matches given url.');
        }
        return $this->_route;
    }

    /**
     * Get the application base url
     * @return string
     */
    public function getBaseUrl($append = '')
    {
        return $this->_baseUrl . trim($append,'/');
    }

    /**
     * Manually sets a request uri. Usefull for testing
     *
     * @param string $uri
     * @return Mura_Router_Abstract
     */
    public function setRequestUri($uri)
    {
        $this->_requestUri = $uri;
        return $this;
    }

    /**
     * Gets request Uri
     * @return string
     */
    public function getRequestUri()
    {
        if (null == $this->_requestUri) {
            $this->_requestUri = $_SERVER['REQUEST_URI'] ;
        }
        return $this->_requestUri;
    }

    /**
     * Gets a route by name
     * @param string $name
     * @return Mura_Router_Route
     * @throws Mura_Router_Exception when route is not found
     */
    public function getRouteByName($name)
    {
        if (!array_key_exists($name, $this->_routes)) {
            require_once 'Mura/Router/Exception.php';
            throw new Exception('Route with name "' . $name . '" was not found');
        }
        return $this->_routes[$name];
    }

    /**
     * Builds and return a url that matches the pattern imposed by $routeName
     * @param string $routeName
     * @param array $params params to append to the url
     */
    public function getUrlFor($routeName,array $params = array())
    {
        $route = $this->getRouteByName($routeName);
        return $route->getUrl($params);
    }

}