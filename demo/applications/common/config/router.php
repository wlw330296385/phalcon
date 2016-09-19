<?php
$di->set('router',function() use($di){
    $router = new Phalcon\Mvc\Router;
    $router->notFound(["controller" => "index", "action"=> "index"]);
	$router->add('/about/intro/:action\.html/:params', array (
    'controller' => 'Page',
    'action' => 1,
    'params' => 2,
));
	$router->add('/about/policy/:action\.html/:params', array (
    'controller' => 'Page',
    'action' => 1,
    'params' => 2,
));
	$router->add('/notice/:action\.html/:params', array (
    'controller' => 'News',
    'action' => 1,
    'params' => 2,
));
	$router->add('/about/media/:action\.html/:params', array (
    'controller' => 'Article',
    'action' => 1,
    'params' => 2,
));
	$router->add('/about/qualification/:action\.html/:params', array (
    'controller' => 'Page',
    'action' => 1,
    'params' => 2,
));
	$router->add('/about/responsibility/:action\.html/:params', array (
    'controller' => 'Page',
    'action' => 1,
    'params' => 2,
));
	$router->add('/about/contact/:action\.html/:params', array (
    'controller' => 'Page',
    'action' => 1,
    'params' => 2,
));
	$router->add('/about/partner/:action\.html/:params', array (
    'controller' => 'Partner',
    'action' => 1,
    'params' => 2,
));
	$router->add('/help/:action\.html/:params', array (
    'controller' => 'Article',
    'action' => 1,
    'params' => 2,
));
	$router->add('/help/safe/:action\.html/:params', array (
    'controller' => 'Article',
    'action' => 1,
    'params' => 2,
));
	$router->add('/help/cj/:action\.html/:params', array (
    'controller' => 'Article',
    'action' => 1,
    'params' => 2,
));
	$router->add('/help/xy/:action\.html/:params', array (
    'controller' => 'Article',
    'action' => 1,
    'params' => 2,
));
	$router->add('/about/job/:action\.html/:params', array (
    'controller' => 'Page',
    'action' => 1,
    'params' => 2,
));
	$router->add('/nav/:action\.html/:params', array (
    'controller' => 'Page',
    'action' => 1,
    'params' => 2,
));
	$router->add('/sitemap/:action\.html/:params', array (
    'controller' => 'Sitemap',
    'action' => 1,
    'params' => 2,
));
	$router->add('/login/:action\.html/:params', array (
    'controller' => 'Login',
    'action' => 1,
    'params' => 2,
));
	$router->add('/register/:action\.html/:params', array (
    'controller' => 'Register',
    'action' => 1,
    'params' => 2,
));
	$router->add('/member/:action\.html/:params', array (
    'controller' => 'Member',
    'action' => 1,
    'params' => 2,
));
	$router->add('/newbie/:action\.html/:params', array (
    'controller' => 'Newbie',
    'action' => 1,
    'params' => 2,
));
	$router->add('/newbie/safe/:action\.html/:params', array (
    'controller' => 'Page',
    'action' => 1,
    'params' => 2,
));

    return $router;
});