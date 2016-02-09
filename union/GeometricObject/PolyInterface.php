<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 09.02.16
 * Time: 16:05
 */
namespace union\GeometricObject;

use union\Point\Point;

interface PolyInterface
{
    public function nVertices();

    public function add($arg0, $arg1 = null);

    public function addPointXY($x, $y);

    public function addPoint(Point $point);

    public function addPoly($p);

    public function getY($index);

    public function getX($index);

    public function isEmpty();

    /**
     * @param $index
     *
     * @return PolyInterface
     */
    public function getInnerPolygon($index);

    public function getNumInnerPoly();

    public function getNumPoints();

    public function isHole();

    public function setIsHole($isHole);

    public function isContributing($index);

    public function setContributing($index, $contributes);
}