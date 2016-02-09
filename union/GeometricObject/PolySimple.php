<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 31.01.16
 * Time: 21:34
 */

namespace union\GeometricObject;

use union\Point\Point;

class PolySimple
{
    public $mList = [];

    public $mContributes;

    public function __construct()
    {
        $this->mList = [];
        $this->mContributes = true;
    }

    public function nVertices()
    {
        return count($this->mList);
    }

    public function add($arg0, $arg1 = null)
    {
        $args = [];
        $args[0] = $arg0;

        if ($arg1) {
            $args[1] = $arg1;
        }

        if (count($args) === 2) {
            $this->addPointXY($args[0], $args[1]);
        } elseif (count($args) === 1) {
            if ($args[0] instanceof Point) {
                $this->addPoint($args[0]);
            } elseif ($args[0] instanceof PolyDefault) {
                $this->addPoly($args[0]);
            } elseif (is_array($args[0])) {
                for ($k = 0; $k < count($args[0]); $k++) {
                    $this->add($args[0][$k]);
                }
            }
        }
    }

    public function addPointXY($x, $y)
    {
        $this->addPoint(new Point($x, $y));
    }

    public function addPoint(Point $point)
    {
        $this->mList[] = $point;
    }

    public function addPoly($p)
    {
        throw new \Exception("Cannot add poly to a simple poly.");
    }

    public function getY($index)
    {
        return $this->mList[$index]->getY();
    }

    public function isEmpty()
    {
        return (count($this->mList) === 0);
    }

    public function getInnerPolygon($index)
    {
        if ($index != 0) {
            throw new \Exception("PolySimple only has one poly");
        }
    }

    public function getNumInnerPoly()
    {
        return 1;
    }

    public function getNumPoints()
    {
        return count($this->mList);
    }

    public function isHole()
    {
        return false;
    }

    public function setIsHole($isHole)
    {
        throw new \Exception("PolySimple cannot be a hole");
    }

    public function isContributing($index)
    {
        if ($index != 0) {
            throw new \Exception("PolySimple only has one poly");
        }

        return $this->mContributes;
    }

    public function setContributing($index, $contributes)
    {
        if ($index != 0) {
            throw new \Exception("PolySimple only has one poly");
        }

        $this->mContributes = $contributes;
    }
}
