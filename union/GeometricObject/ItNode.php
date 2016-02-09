<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 29.01.16
 * Time: 19:54
 */

namespace union\GeometricObject;

use union\Point\Point;

class ItNode
{
    /**
     * @var EdgeNode[]
     */
    private $ie = [];

    /**
     * @var Point
     */
    private $point;

    /**
     * @var ItNode
     */
    private $next;

    public function __construct(EdgeNode $edge0, EdgeNode $edge1, $x, $y, ItNode $next = null)
    {
        $this->ie[0] = $edge0;
        $this->ie[1] = $edge1;
        $this->point = new Point($x, $y);
        $this->next = $next;
    }

    /**
     * @return EdgeNode[]
     */
    public function getIe()
    {
        return $this->ie;
    }

    /**
     * @param EdgeNode[] $ie
     *
     * @return $this
     */
    public function setIe($ie)
    {
        $this->ie = $ie;

        return $this;
    }

    /**
     * @return Point
     */
    public function getPoint()
    {
        return $this->point;
    }

    /**
     * @param Point $point
     *
     * @return $this
     */
    public function setPoint($point)
    {
        $this->point = $point;

        return $this;
    }

    /**
     * @return ItNode
     */
    public function getNext()
    {
        return $this->next;
    }

    /**
     * @param ItNode $next
     *
     * @return $this
     */
    public function setNext($next)
    {
        $this->next = $next;

        return $this;
    }
}
