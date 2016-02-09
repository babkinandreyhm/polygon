<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 28.01.16
 * Time: 2:53
 */

namespace union;

use union\GeometricObject\EdgeNode;

class LocalMinimumNode
{
    /**
     * @var float y coordinate at local minimum
     */
    private $y;

    /**
     * @var EdgeNode Pointer to bound list
     */
    private $firstBound;

    /**
     * @var LocalMinimumNode Pointer to next local minimum
     */
    private $next;

    /**
     * @param float $yValue
     */
    public function __construct($yValue)
    {
        $this->y = $yValue;
    }

    /**
     * @return float
     */
    public function getY()
    {
        return $this->y;
    }

    /**
     * @return EdgeNode
     */
    public function getFirstBound()
    {
        return $this->firstBound;
    }

    /**
     * @param EdgeNode $firstBound
     *
     * @return $this
     */
    public function setFirstBound($firstBound)
    {
        $this->firstBound = $firstBound;

        return $this;
    }

    /**
     * @return LocalMinimumNode
     */
    public function getNext()
    {
        return $this->next;
    }

    /**
     * @param LocalMinimumNode $next
     *
     * @return $this
     */
    public function setNext($next)
    {
        $this->next = $next;

        return $this;
    }
}
