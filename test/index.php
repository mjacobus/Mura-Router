<?php

//set include path
set_include_path(implode(PATH_SEPARATOR, array(
        dirname(__FILE__) . '/../lib',
        get_include_path())));


require_once 'Mura/Router.php';

$router = Mura_Router::getInstance();
$router->setBaseUrl('/Mura-Router/test');

//example route
$router->addRoute(
    'customer',
    new Mura_Router_Route('/customer/:customer-name/:second-parameter',
    array(
            'lang' => 'en'
    ),
    array(
            ':customer-name' => '([\w\d]){1,}'
    ))
);
$router->addRoute(
    'tag',
    new Mura_Router_Route('/search/:q/*',
    array(),
    array(
            'q' => '.{3,}'
    ))
);

try{
    //get valid route, if any.
    $route = $router->getRoute();
    $params = $route->getParams();
} catch(Mura_Router_Exception $e){
    //deals with exception
}
?>


<h1>Parameters</h1>

Tags
<a href="<?php echo $router->getBaseUrl()?>/search/bomba">bomba</a>
<a href="<?php echo $router->getBaseUrl()?>/search/ferragem">ferrage</a>
<a href="<?php echo $router->getBaseUrl()?>/search/surdina">surdina</a>
<br/>
Customer
<a href="<?php echo $router->getBaseUrl()?>/customer/marcelo/jacobus">Marcelo</a>
<a href="<?php echo $router->getBaseUrl()?>/customer/mura/">Mura</a>

<pre>
    <?php var_dump($params); ?>
</pre>

