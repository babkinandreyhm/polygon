<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 29.01.16
 * Time: 19:59
 */

namespace union\GeometricObject;

class StNode
{
    /**
     * @var EdgeNode Pointer to AET edge
     */
    private $edge;

    /**
     * @var float Scanbeam bottom x coordinate
     */
    private $xb;

    /**
     * @var float Scanbeam top x coordinate
     */
    private $xt;

    /**
     * @var float Change in x for a unit y increase
     */
    private $dx;

    /**
     * @var StNode Previous edge in sorted list
     */
    private $prev;

    /**
     * @param EdgeNode $edge
     * @param StNode $prev
     */
    public function __construct(EdgeNode $edge, StNode $prev = null)
    {
        $this->edge = $edge;
        $this->xb = $edge->getXb();
        $this->xt = $edge->getXt();
        $this->dx = $edge->getDx();
        $this->prev = $prev;
    }

    /**
     * @return EdgeNode
     */
    public function getEdge()
    {
        return $this->edge;
    }

    /**
     * @param EdgeNode $edge
     *
     * @return $this
     */
    public function setEdge($edge)
    {
        $this->edge = $edge;

        return $this;
    }

    /**
     * @return float
     */
    public function getXb()
    {
        return $this->xb;
    }

    /**
     * @param float $xb
     *
     * @return $this
     */
    public function setXb($xb)
    {
        $this->xb = $xb;

        return $this;
    }

    /**
     * @return float
     */
    public function getXt()
    {
        return $this->xt;
    }

    /**
     * @param float $xt
     *
     * @return $this
     */
    public function setXt($xt)
    {
        $this->xt = $xt;

        return $this;
    }

    /**
     * @return float
     */
    public function getDx()
    {
        return $this->dx;
    }

    /**
     * @param float $dx
     *
     * @return $this
     */
    public function setDx($dx)
    {
        $this->dx = $dx;

        return $this;
    }

    /**
     * @return StNode
     */
    public function getPrev()
    {
        return $this->prev;
    }

    /**
     * @param StNode $prev
     *
     * @return $this
     */
    public function setPrev($prev)
    {
        $this->prev = $prev;

        return $this;
    }
}
