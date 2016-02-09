<?php

namespace union;

class ScanBeamTree
{
    /**
     * @var float Scanbeam node y value
     */
    private $y;

    /**
     * @var ScanBeamTree Pointer to nodes with lower y
     */
    private $less;

    /**
     * @var ScanBeamTree Pointer to nodes with higher y
     */
    private $more;

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
     * @return ScanBeamTree
     */
    public function getLess()
    {
        return $this->less;
    }

    /**
     * @param ScanBeamTree $less
     *
     * @return $this
     */
    public function setLess($less)
    {
        $this->less = $less;

        return $this;
    }

    /**
     * @return ScanBeamTree
     */
    public function getMore()
    {
        return $this->more;
    }

    /**
     * @param ScanBeamTree $more
     *
     * @return $this
     */
    public function setMore($more)
    {
        $this->more = $more;

        return $this;
    }
}
