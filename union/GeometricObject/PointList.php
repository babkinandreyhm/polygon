<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 27.01.16
 * Time: 23:17
 */

namespace union\GeometricObject;

use union\Point\Point;
use union\Point\PointInterface;

class PointList implements GeometricObject, \Iterator
{
    const MAX_VALUE  = 0x7fffffff;

    /** @var PointInterface[]*/
    protected $points = [];

    /** @var int */
    protected $pointerPosition = 0;

    /**
     * @param PointInterface[] $points
     */
    public function __construct(array $points = [])
    {
        foreach ($points as $point) {
            if (is_array($point)) {
                $point = new Point($point[0], $point[1]);
            }
            $this->points[] = $point;
        }
    }

    /**
     * @return \SplDoublyLinkedList|\union\Point\PointInterface[]
     */
    public function getPoints()
    {
        return $this->points;
    }

    /**
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     * @since 5.0.0
     */
    public function current()
    {
        return $this->points[$this->pointerPosition];
    }

    /**
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next()
    {
        $this->pointerPosition++;
    }

    /**
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key()
    {
        return $this->pointerPosition;
    }

    /**
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid()
    {
        return isset($this->points[$this->pointerPosition]);
    }

    /**
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind()
    {
        $this->pointerPosition = 0;
    }

    /**
     * @return Rectangle
     */
    public function calculateRegularBoundingBox()
    {
        $xMin = self::MAX_VALUE;
        $xMax = 0;
        $yMin = self::MAX_VALUE;
        $yMax = 0;

        foreach ($this->points as $point) {
            if ($point->getX() < $xMin) {
                $xMin = $point->getX();
            }
            if ($point->getX() >$xMax) {
                $xMax = $point->getX();
            }
            if ($point->getY() < $yMin) {
                $yMin = $point->getY();
            }
            if ($point->getY() > $yMax) {
                $yMax = $point->getY();
            }
        }

        return new Rectangle($xMin, $yMin, $xMax - $xMin, $yMax - $yMin);
    }

    /**
     * @param float $x
     * @param float $y
     *
     * @return $this
     */
    public function translate($x, $y)
    {
        foreach($this->points as $point) {
            $point->setX($point->getX() + $x);
            $point->setY($point->getY() + $y);
        }

        return $this;
    }

    /**
     * @param float $scaleFactor
     *
     * @return $this
     */
    public function scale($scaleFactor)
    {
        foreach($this->points as $point) {
            $point->setX($point->getX() * $scaleFactor);
            $point->setY($point->getY() * $scaleFactor);
        }

        return $this;
    }

    /**
     * @return PointInterface
     */
    public function calculateCentroid()
    {
        $xSum = 0;
        $ySum = 0;

        $n = 0;

        foreach ($this->points as $point) {
            $xSum += $point->getX();
            $ySum += $point->getY();
            $n++;
        }

        return new Point($xSum / $n, $ySum / $n);
    }

    /**
     * @return float
     */
    public function minX()
    {
        return $this->calculateRegularBoundingBox()->minX();
    }

    /**
     * @return float
     */
    public function minY()
    {
        return $this->calculateRegularBoundingBox()->minY();
    }

    /**
     * @return float
     */
    public function maxX()
    {
        $boundingBox = $this->calculateRegularBoundingBox();

        return $boundingBox->minX() + $boundingBox->getWidth();
    }

    /**
     * @return float
     */
    public function maxY()
    {
        $boundingBox = $this->calculateRegularBoundingBox();

        return $boundingBox->minY() + $boundingBox->getHeight();
    }

    /**
     * @return float
     */
    public function getWidth()
    {
        return $this->maxX() - $this->minX();
    }

    /**
     * @return float
     */
    public function getHeight()
    {
        return $this->maxY() - $this->minY();
    }

    protected function getPointsHash()
    {
        $hash = '';
        foreach ($this->points as $point) {
            $hash .= $point->getX() . ',' . $point->getY() . ';';
        }

        return $hash;
    }
}
