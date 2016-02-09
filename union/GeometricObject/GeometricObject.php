<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 27.01.16
 * Time: 21:00
 */

namespace union\GeometricObject;


use union\Point\PointInterface;
use union\Rectangle;

interface GeometricObject
{
    /**
     * @return Rectangle
     */
    public function calculateRegularBoundingBox();

    /**
     * @param float $x
     * @param float $y
     *
     * @return $this
     */
    public function translate($x, $y);

    /**
     * @param float $scaleFactor
     *
     * @return $this
     */
    public function scale($scaleFactor);

    /**
     * @return PointInterface
     */
    public function calculateCentroid();

    /**
     * @return float
     */
    public function minX();

    /**
     * @return float
     */
    public function minY();

    /**
     * @return float
     */
    public function maxX();

    /**
     * @return float
     */
    public function maxY();

    /**
     * @return float
     */
    public function getWidth();

    /**
     * @return float
     */
    public function getHeight();
}
