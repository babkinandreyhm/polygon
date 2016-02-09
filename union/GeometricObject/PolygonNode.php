<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 28.01.16
 * Time: 15:22
 */

namespace union\GeometricObject;

use union\PolygonUtils;

class PolygonNode
{
    /**
     * @var int Active flag / vertex count
     */
    private $active;

    /**
     * @var boolean
     */
    private $hole;

    /**
     * @var VertexNode[] Left and right vertex list pointers
     */
    public $vertexList;

    /**
     * @var PolygonNode Pointer to next polygon contour
     */
    private $next;

    /**
     * @var PolygonNode Pointer to actual structure used
     */
    private $proxy;

    public function __construct($next, $x, $y)
    {
        $vertexNode = new VertexNode($x, $y);
        $this->vertexList[PolygonUtils::LEFT] = $vertexNode;
        $this->vertexList[PolygonUtils::RIGHT] = $vertexNode;

        $this->next = $next;
        $this->proxy = $this; // Initialise proxy to point to p itself
        $this->active = 1; //true
    }

    /**
     * @param float $x
     * @param float $y
     */
    public function addRight($x, $y)
    {
        $vertexNode = new VertexNode($x, $y);
        $this->proxy->vertexList[PolygonUtils::RIGHT]->setNext($vertexNode);
        $this->proxy->vertexList[PolygonUtils::RIGHT] = $vertexNode;
    }

    /**
     * @param float $x
     * @param float $y
     */
    public function addLeft($x, $y)
    {
        $vertexNode = new VertexNode($x, $y);
        $vertexNode->setNext($this->proxy->vertexList[PolygonUtils::LEFT]);
        $this->proxy->vertexList[PolygonUtils::LEFT] = $vertexNode;
    }

    /**
     * @return PolygonNode
     */
    public function getProxy()
    {
        return $this->proxy;
    }

    /**
     * @param PolygonNode $proxy
     *
     * @return $this
     */
    public function setProxy($proxy)
    {
        $this->proxy = $proxy;

        return $this;
    }

    /**
     * @return int
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param int $active
     *
     * @return $this
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isHole()
    {
        return $this->hole;
    }

    /**
     * @param boolean $hole
     *
     * @return $this
     */
    public function setHole($hole)
    {
        $this->hole = $hole;

        return $this;
    }

    /**
     * @return VertexNode[]
     */
    public function getVertexList()
    {
        return $this->vertexList;
    }

    /**
     * @param VertexNode[] $vertexList
     *
     * @return $this
     */
    public function setVertexList($vertexList)
    {
        $this->vertexList = $vertexList;

        return $this;
    }

    /**
     * @return PolygonNode
     */
    public function getNext()
    {
        return $this->next;
    }

    /**
     * @param PolygonNode $next
     *
     * @return $this
     */
    public function setNext($next)
    {
        $this->next = $next;

        return $this;
    }
}
