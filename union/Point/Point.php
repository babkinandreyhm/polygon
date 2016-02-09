<?php

namespace union\Point;


class Point implements PointInterface
{
    /** @var float */
    private $x;

    /** @var float */
    private $y;

    /**
     * @param float $x
     * @param float $y
     */
    public function __construct($x = 0.0, $y = 0.0)
    {
        $this->x = $x;
        $this->y = $y;
    }

    /**
     * @return float
     */
    public function getX()
    {
        return $this->x;
    }

    /**
     * @param float $x
     *
     * @return $this
     */
    public function setX($x)
    {
        $this->x = $x;

        return $this;
    }

    /**
     * @return float
     */
    public function getY()
    {
        return $this->y;
    }

    /**
     * @param float $y
     *
     * @return $this
     */
    public function setY($y)
    {
        $this->y = $y;

        return $this;
    }

    /**
     * @param Point $point
     *
     * @returns $this
     */
    public function copyFrom(Point $point)
    {
        $this->x = $point->getX();
        $this->y = $point->getY();

        return $this;
    }

    /**
     * @param float $x
     * @param float $y
     *
     * @return $this
     */
    public function translate($x, $y)
    {
        $this->x += $x;
        $this->y += $y;

        return $this;
    }

    /**
     * @param Point $point
     *
     * @return bool
     */
    public function equals(Point $point)
    {
        return $point->getX() == $this->x && $point->getY() == $this->y;
    }

    public function __toString()
    {
        return 'x: ' . $this->getX() . ', y: ' . $this->getY();
    }
}
