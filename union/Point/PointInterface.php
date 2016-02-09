<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 27.01.16
 * Time: 22:52
 */
namespace union\Point;

interface PointInterface
{
    /**
     * @return float
     */
    public function getX();

    /**
     * @param float $x
     *
     * @return $this
     */
    public function setX($x);

    /**
     * @return float
     */
    public function getY();

    /**
     * @param float $y
     *
     * @return $this
     */
    public function setY($y);

    /**
     * @param Point $point
     *
     * @returns $this
     */
    public function copyFrom(Point $point);

    /**
     * @param float $x
     * @param float $y
     *
     * @return $this
     */
    public function translate($x, $y);

    /**
     * @param Point $point
     *
     * @return bool
     */
    public function equals(Point $point);
}
