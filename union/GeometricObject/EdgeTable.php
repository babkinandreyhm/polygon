<?php

namespace union\GeometricObject;

use union\PolygonUtils;

class EdgeTable
{
    /**
     * @var EdgeNode[]
     */
    private $mList;

    public function addNode($x, $y)
    {
        $node = new EdgeNode();
        $node->getVertex()->setX($x);
        $node->getVertex()->setY($y);
        $this->mList[] = $node;
    }

    /**
     * @param int $index
     *
     * @return EdgeNode
     */
    public function getNode($index)
    {
        return $this->mList[$index];
    }

    /**
     * @param int $i
     *
     * @return bool
     */
    public function fwdMin($i)
    {
        $prev = $this->mList[PolygonUtils::prevIndex($i, $this->nodesCount())];
        $next = $this->mList[PolygonUtils::nextIndex($i, $this->nodesCount())];
        $ith = $this->mList[$i];

        return (($prev->getVertex()->getY() >= $ith->getVertex()->getY())
            && ($next->getVertex()->getY() > $ith->getVertex()->getY()));
    }

    /**
     * @param int $i
     *
     * @return bool
     */
    public function notFMax($i)
    {
        $next = $this->mList[PolygonUtils::nextIndex($i, $this->nodesCount())];
        $ith = $this->mList[$i];

        return ($next->getVertex()->getY() > $ith->getVertex()->getY());
    }

    /**
     * @param int $i
     *
     * @return bool
     */
    public function revMin($i)
    {
        $prev = $this->mList[PolygonUtils::prevIndex($i, $this->nodesCount())];
        $next = $this->mList[PolygonUtils::nextIndex($i, $this->nodesCount())];
        $ith = $this->mList[$i];

        return (($prev->getVertex()->getY() > $ith->getVertex()->getY())
            && ($next->getVertex()->getY() >= $ith->getVertex()->getY()));
    }

    /**
     * @param int $i
     *
     * @return bool
     */
    public function notRMax($i)
    {
        $prev = $this->mList[PolygonUtils::prevIndex($i, $this->nodesCount())];
        $ith = $this->mList[$i];

        return ($prev->getVertex()->getY() > $ith->getVertex()->getY());
    }

    /**
     * @return int
     */
    protected function nodesCount()
    {
        return count($this->mList);
    }
}
