<?php

require_once __DIR__.'/../utils.php';

use ju1ius\Pegasus\Twig\Extension\PegasusTwigExtension;


$loader = new \Twig_Loader_Filesystem(__DIR__.'/tpl');
$twig = new \Twig_Environment($loader, [
    'autoescape' => false,
    'cache' => __DIR__.'/tpl/cache',
    'debug' => true
]);
$twig->addExtension(new PegasusTwigExtension);
$twig->addExtension(new \Twig_Extension_Debug);

$tpl = $twig->loadTemplate('class.twig');
$pre = $tpl->render([]);
echo str_replace('### placeholder properties ###', implode('', $twig->getGlobals()['data_collector']->retrieve('properties')), $pre);
