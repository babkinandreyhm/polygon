<?php

function my_autoloader($className) {
    $ololo = '/Users/andrey/PhpstormProjects/polygon/';
    $fileName = '';
    if ($lastNsPos = strrpos($className, '\\')) {
        $namespace = substr($className, 0, $lastNsPos);
        $className = substr($className, $lastNsPos + 1);
        $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
    }
    $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

    include $ololo . $fileName;
}

spl_autoload_register('my_autoloader');

function createPoly(array $points) {
    $res = new \union\GeometricObject\PolyDefault();
    foreach ($points as $point) {
        $res->addPoint(new \union\Point\Point($point[0], $point[1]));
    }
    
    return $res;
}

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
$poly1 = createPoly($vertices1);

$vertices2 = [
    [131,84],
    [224,110],
    [174,180],
    [120,136],
    [60,167],
];
$poly2= createPoly($vertices2);

$vertices3 = [
    [300,84],
    [365,110],
    [380,180],
    [350,210],
    [320,197],
];
$poly3= createPoly($vertices3);


$vertices4 = [
    [181,120],
    [320,130],
    [340,150],
    [185, 160]
];
$poly4= createPoly($vertices4);


$vertices5 = [
    [181,180],
    [320,190],
    [340,210],
    [185,220]
];
$poly5= createPoly($vertices5);

$vertices6 = [
    [400,100],
    [420,100],
    [420,200],
    [400,200]
];
$poly6= createPoly($vertices6);


$vertices7 = [
    [400,100],
    [500,100],
    [500,120],
    [400,120]
];
$poly7= createPoly($vertices7);


$vertices8 = [
    [480,100],
    [500,100],
    [500,200],
    [480,200]
];
$poly8= createPoly($vertices8);


$vertices9 = [
    [400,180],
    [500,180],
    [500,200],
    [400,200]
];
$poly9= createPoly($vertices9);

$utils = new \union\PolygonUtils();

$polyClass = 'union\GeometricObject\PolyDefault';
$diff = $utils->union($poly1, $poly2, $polyClass);
$diff = $utils->union($diff, $poly3, $polyClass);
$diff = $utils->union($diff, $poly4, $polyClass);
$diff = $utils->union($diff, $poly5, $polyClass);
$diff = $utils->union($diff, $poly6, $polyClass);
$diff = $utils->union($diff, $poly7, $polyClass);
$diff = $utils->union($diff, $poly8, $polyClass);
$diff = $utils->union($diff, $poly9, $polyClass);

