<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 31.01.16
 * Time: 22:50
 */

namespace union\GeometricObject;

use union\Point\Point;

class PolyDefault extends PolySimple
{
    public function __construct($isHole = null)
    {
        parent::__construct();
        if ($isHole === null) {
            $isHole = false;
        }

        $this->isHole = $isHole;
    }

    public function nVertices()
    {
        return $this->mList[0]->nVertices();
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
                $arr = $arg0;
                if ((count($arr) == 2 && is_numeric($arr[0]) && is_numeric($arr[1]))) {
                    $this->add($arr[0], $arr[1]);
                } else {
                    for ($k = 0; $k < count($args[0]); $k++) {
                        $this->add($args[0][$k]);
                    }
                }
            }
        }
    }

    public function addPoint(Point $point)
    {
        if (count($this->mList) === 0) {
            $this->mList[] = new PolySimple();
        }

        $this->mList[0]->addPoint($point);
    }

    public function addPoly($p)
    {
        if (count($this->mList) > 0 && $this->isHole) {
            throw new \Exception('Can not add smth to hole');
        }
        $this->mList[] = $p;
    }

    public function getInnerPolygon($index)
    {
        return $this->mList[$index];
    }

    public function getY($index)
    {
        return $this->mList[0]->getY($index);
    }

    public function getNumInnerPoly()
    {
        return count($this->mList);
    }

    public function isHole()
    {
        if (count($this->mList) > 1) {
            throw new \Exception('Cannot call on a poly made up of more than one poly.');
        }

        return $this->isHole;
    }

    public function setIsHole($isHole)
    {
        if (count($this->mList) > 1) {
            throw new \Exception('Cannot call on a poly made up of more than one poly.');
        }

        $this->isHole = $isHole;
    }

    public function isContributing($index)
    {
        return $this->mList[$index]->isContributing(0);
    }

    public function setContributing($index, $c)
    {
        if (count($this->mList) != 1) {
            throw new \Exception('Only applies to polys of size 1');
        }

        $this->mList[$index]->setContributing(0, $c);
    }
}
