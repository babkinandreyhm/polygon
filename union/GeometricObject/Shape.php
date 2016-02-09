<?php

namespace union\GeometricObject;


use union\Point\PointInterface;

interface Shape extends GeometricObject
{
    /**
     * @param PointInterface $point
     *
     * @return bool
     */
    public function isInside(PointInterface $point);

    /**
     * @return float
     */
    public function calculateArea();

    /**
     * @return float
     */
    public function calculatePerimeter();

    /**
     * @return boolean
     */
    public function isConvex();
}
