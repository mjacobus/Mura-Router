<?php
/**
 * @author marcelo (marcelo.jacobus@gmail.com)
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
 *
 */
class Mura_Router extends Mura_Router_Abstract
{

	
}