<?php

namespace union\GeometricObject;

class AetTree
{
    /**
     * @var EdgeNode
     */
    private $topNode;

    /**
     * @return EdgeNode
     */
    public function getTopNode()
    {
        return $this->topNode;
    }

    /**
     * @param EdgeNode $topNode
     *
     * @return $this
     */
    public function setTopNode($topNode)
    {
        $this->topNode = $topNode;

        return $this;
    }

    public function printTree()
    {
        for ($edge = $this->topNode; ($edge !== null); $edge = $edge->getNext()) {
            echo "edge.vertex.x=" . $edge->getVertex()->getX() . ', edge.vertex.y=' . $edge->getVertex()->getY() . PHP_EOL;
        }
    }

    public function getTree()
    {
        $r = '';
        for ($edge = $this->topNode; ($edge !== null); $edge = $edge->getNext()) {
            $r .= ("edge.vertex.x=" . $edge->getVertex()->getX() . ', edge.vertex.y=' . $edge->getVertex()->getY() . "\n");
        }

        return $r;
    }
}
