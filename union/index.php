<?php

function my_autoloader($className) {
    $ololo = '/Users/andrey/PhpstormProjects/polygon/';
    if ($lastNsPos = strrpos($className, '\\')) {
        $namespace = substr($className, 0, $lastNsPos);
        $className = substr($className, $lastNsPos + 1);
        $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
    }
    $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

    include $ololo . $fileName;
}

spl_autoload_register('my_autoloader');

$vertices1 = [
    [61,68],
    [145,122],
    [186,94],
    [224,135],
    [204,211],
    [105,200],
    [141,163],
    [48,139],
    [74,117]
];
$poly1 = new \union\GeometricObject\Polygon($vertices1);

$vertices2 = [
    [131,84],
    [224,110],
    [174,180],
    [120,136],
    [60,167],
];
$poly2= new \union\GeometricObject\Polygon($vertices2);

$vertices3 = [
    [300,84],
    [365,110],
    [380,180],
    [350,210],
    [320,197],
];
$poly3= new \union\GeometricObject\Polygon($vertices3);


$vertices4 = [
    [181,120],
    [320,130],
    [340,150],
    [185, 160]
];
$poly4= new \union\GeometricObject\Polygon($vertices4);


$vertices5 = [
    [181,180],
    [320,190],
    [340,210],
    [185,220]
];
$poly5= new \union\GeometricObject\Polygon($vertices5);

$vertices6 = [
    [400,100],
    [420,100],
    [420,200],
    [400,200]
];
$poly6= new \union\GeometricObject\Polygon($vertices6);


$vertices7 = [
    [400,100],
    [500,100],
    [500,120],
    [400,120]
];
$poly7= new \union\GeometricObject\Polygon($vertices7);


$vertices8 = [
    [480,100],
    [500,100],
    [500,200],
    [480,200]
];
$poly8= new \union\GeometricObject\Polygon($vertices8);


$vertices9 = [
    [400,180],
    [500,180],
    [500,200],
    [400,200]
];
$poly9= new \union\GeometricObject\Polygon($vertices9);

$utils = new \union\PolygonUtils();

$diff = $utils->union($poly1, $poly3);
//$diff = $utils->union($diff, $poly2);
//$diff = $utils->union($diff, $poly4);
//$diff = $utils->union($diff, $poly5);
//$diff = $utils->union($diff, $poly6);
//$diff = $utils->union($diff, $poly7);
//$diff = $utils->union($diff, $poly8);
//$diff = $utils->union($diff, $poly9);

var_dump($diff);

