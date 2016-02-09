<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 28.01.16
 * Time: 15:17
 */

namespace union\GeometricObject;

class VertexNode
{
    /**
     * @var float
     */
    private $x;

    /**
     * @var float
     */
    private $y;

    /**
     * @var VertexNode
     */
    private $next;

    public function __construct($x, $y)
    {
        $this->x = $x;
        $this->y = $y;
        $this->next = null;
    }

    /**
     * @return float
     */
    public function getX()
    {
        return $this->x;
    }

    /**
     * @return float
     */
    public function getY()
    {
        return $this->y;
    }

    /**
     * @return VertexNode
     */
    public function getNext()
    {
        return $this->next;
    }

    /**
     * @param VertexNode $next
     *
     * @return $this
     */
    public function setNext($next)
    {
        $this->next = $next;

        return $this;
    }
}
