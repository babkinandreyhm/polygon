<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 27.01.16
 * Time: 22:25
 */

namespace union\GeometricObject;

use union\Point\Point;
use union\Point\PointInterface;

class Rectangle implements Shape
{
    /** @var float */
    private $topLeftX;

    /** @var float */
    private $topLeftY;

    /** @var float */
    private $width;

    /** @var float */
    private $height;

    /**
     * @param float $topLeftX
     * @param float $topLeftY
     * @param float $width
     * @param float $height
     */
    public function __construct($topLeftX, $topLeftY, $width, $height)
    {
        $this->topLeftX = $topLeftX;
        $this->topLeftY = $topLeftY;
        $this->width = $width;
        $this->height = $height;
    }

    /**
     * @return Rectangle
     */
    public function calculateRegularBoundingBox()
    {
        return new Rectangle($this->topLeftX, $this->topLeftY, $this->width, $this->height);
    }

    /**
     * @param float $x
     * @param float $y
     *
     * @return $this
     */
    public function translate($x, $y)
    {
        $this->topLeftX += $x;
        $this->topLeftY += $y;

        return $this;
    }

    /**
     * @param float $scaleFactor
     *
     * @return $this
     */
    public function scale($scaleFactor)
    {
        $this->topLeftX *= $scaleFactor;
        $this->topLeftY *= $scaleFactor;
        $this->width *= $scaleFactor;
        $this->height *= $scaleFactor;

        return $this;
    }

    /**
     * @return PointInterface
     */
    public function calculateCentroid()
    {
        return new Point($this->topLeftX + $this->width / 2, $this->topLeftY + $this->height / 2);
    }

    /**
     * @return float
     */
    public function minX()
    {
        return $this->topLeftX;
    }

    /**
     * @return float
     */
    public function minY()
    {
        return $this->topLeftY;
    }

    /**
     * @return float
     */
    public function maxX()
    {
        return $this->topLeftX + $this->width;
    }

    /**
     * @return float
     */
    public function maxY()
    {
        return $this->topLeftY + $this->height;
    }

    /**
     * @return float
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @return float
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param PointInterface $point
     *
     * @return bool
     */
    public function isInside(PointInterface $point)
    {
        $px = $point->getX();
        $py = $point->getY();

        if ($px >= $this->topLeftX
            && $px <= $this->topLeftX + $this->width
            && $py >= $this->topLeftY
            && $py <= $this->topLeftY + $this->height
        ) {
            return true;
        }

        return false;
    }

    /**
     * @return float
     */
    public function calculateArea()
    {
        return $this->width * $this->height;
    }

    /**
     * @return float
     */
    public function calculatePerimeter()
    {
        return 2 * ($this->width + $this->height);
    }

    /**
     * @return boolean
     */
    public function isConvex()
    {
        return true;
    }
}
