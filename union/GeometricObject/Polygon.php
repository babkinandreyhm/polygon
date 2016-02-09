<?php

namespace union\GeometricObject;

use union\Point\PointInterface;

class Polygon extends PointList implements Shape
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var bool
     */
    private $isHole = false;

    /**
     * @var Polygon[]
     */
    private $innerPolygons = [];

    /**
     * @param array $points
     */
    public function __construct($points = [])
    {
        parent::__construct($points);
        $this->id = uniqid();
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return boolean
     */
    public function isHole()
    {
        return $this->isHole;
    }

    /**
     * @param boolean $isHole
     *
     * @return $this
     */
    public function setIsHole($isHole)
    {
        $this->isHole = $isHole;

        return $this;
    }

    /**
     * @return array
     */
    public function getInnerPolygons()
    {
        return $this->innerPolygons;
    }

    /**
     * @param array $innerPolygons
     *
     * @return $this
     */
    public function setInnerPolygons(array $innerPolygons)
    {
        $this->innerPolygons = $innerPolygons;

        return $this;
    }

    /**
     * @param Polygon $polygon
     *
     * @return $this
     */
    public function addInnerPolygon(Polygon $polygon)
    {
        $this->innerPolygons[] = $polygon;

        return $this;
    }

    /**
     * @param Polygon $polygon
     * @param bool $inferOuter
     *
     * @return $this
     */
    public function addPolygon(Polygon $polygon, $inferOuter = true)
    {
        if (!$inferOuter) {
            $this->innerPolygons[] = $polygon;
        } else {
            if (count($this->points) === 0) {
                foreach ($polygon->getPoints() as $point) {
                    $this->points[] = $point;
                }
                $this->isHole = $polygon->isHole();
            } else {
                $this->addPolygon($polygon, false);
            }
        }
//        $this->innerPolygons[] = $polygon;

        return $this;
    }

    /**
     * @return \union\Point\PointInterface[]
     */
    public function getVertices()
    {
        return $this->points;
    }

    /**
     * @param PointInterface $point
     *
     * @return $this
     */
    public function addVertex(PointInterface $point)
    {
        $this->points[] = $point;

        return $this;
    }

    /**
     * @return int the number of inner polygons in this polygon including this polygon
     */
    public function getNumInnerPoly()
    {
        return count($this->innerPolygons) + 1;
    }

    /**
     * @param int $index
     *
     * @return $this
     */
    public function getInnerPolygon($index)
    {
        if ($index === 0) {
            return $this;
        }

        return $this->innerPolygons[$index - 1];
    }

    /**
     * @return bool
     */
    public function isClosed()
    {
        if (count($this->points) > 0
            && reset($this->points)->getX() == end($this->points)->getX()
            && reset($this->points)->getY() == end($this->points)->getY()
        ) {
            return true;
        }

        return false;
    }

    /**
     * @return $this
     */
    public function close()
    {
        if (!$this->isClosed() && count($this->points) > 0) {
            $this->points[] = reset($this->points);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function open()
    {
        if ($this->isClosed() && count($this->points) > 1) {
            array_pop($this->points);
        }

        return $this;
    }

    /**
     * @param PointInterface $point
     * @link http://alienryderflex.com/polygon/
     *
     * @return bool
     */
    public function isInside(PointInterface $point)
    {
        $isClosed = $this->isClosed();

        if (!$isClosed) {
            $this->close();
        }

        $isOdd = false;

        for ($polygonIndex = 0; $polygonIndex < $this->getNumInnerPoly(); $polygonIndex++) {
            $vertices = $this->getInnerPolygon($polygonIndex)->getVertices();
            $vertexCount = count($vertices) - 1;
            for ($vertexIndex = 0; $vertexIndex < $vertexCount + 1; $vertexIndex++) {
                $y = $point->getY();
                if ($vertices[$vertexIndex]->getY() < $y && $vertices[$vertexCount]->getY() >= $y
                    || $vertices[$vertexCount]->getY() < $y && $vertices[$vertexIndex]->getY() >= $y
                ) {
                    if ($vertices[$vertexIndex]->getX() + ($point->getY() - $vertices[$vertexIndex]->getY())
                        / ($vertices[$vertexCount]->getY() - $vertices[$vertexIndex]->getY())
                        * ($vertices[$vertexCount]->getX() - $vertices[$vertexCount]->getX()) < $point->getX()
                    ) {
                        $isOdd = !$isOdd;
                    }
                }
            }
        }

        if ($isClosed) {
            $this->open();
        }

        return $isOdd;
    }

    /**
     * @return float
     */
    public function calculateArea()
    {
        // TODO: Implement calculateArea() method.
    }

    /**
     * @return float
     */
    public function calculatePerimeter()
    {
        // TODO: Implement calculatePerimeter() method.
    }

    /**
     * @return boolean
     */
    public function isConvex()
    {
        // TODO: Implement isConvex() method.
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return count($this->points) === 0 && count($this->innerPolygons) === 0;
    }

    /**
     * @return int
     */
    public function nVertices()
    {
        if ($this->isClosed()) {
            return count($this->points) - 1;
        }

        return count($this->points);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getId();
    }
}
