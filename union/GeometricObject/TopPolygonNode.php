<?php

namespace union\GeometricObject;

use union\Point\Point;
use union\PolygonUtils;

class TopPolygonNode
{
    /**
     * @var PolygonNode
     */
    private $topNode;

    /**
     * @return PolygonNode
     */
    public function getTopNode()
    {
        return $this->topNode;
    }

    /**
     * @param PolygonNode $topNode
     *
     * @return $this
     */
    public function setTopNode($topNode)
    {
        $this->topNode = $topNode;

        return $this;
    }

    /**
     * @param float $x
     * @param float $y
     *
     * @return PolygonNode
     */
    public function addLocalMin($x, $y)
    {
        $existingMin = $this->topNode;

        $this->topNode = new PolygonNode($existingMin, $x, $y);

        return $this->topNode;
    }

    /**
     * @param PolygonNode $p
     * @param PolygonNode $q
     */
    public function mergeLeft(PolygonNode $p, PolygonNode $q)
    {
        /* Label contour as a hole */
        $q->getProxy()->setHole(true);
        $topNode = $this->topNode;

        if ($p->getProxy() !== $q->getProxy()) {
            /* Assign p's vertex list to the left end of q's list */
            $p->getProxy()->vertexList[PolygonUtils::RIGHT]->setNext(
                $q->getProxy()->vertexList[PolygonUtils::LEFT]
            );
            $q->getProxy()->vertexList[PolygonUtils::LEFT] =
                $p->getProxy()->vertexList[PolygonUtils::LEFT];

            /* Redirect any p.proxy references to q.proxy */

            $target = $p->getProxy();
            for ($node = $topNode; ($node != null); $node = $node->getNext()) {
                if ($node->getProxy() === $target) {
                    $node->setActive(0);
                    $node->setProxy($q->getProxy());
                }
            }
        }
    }

    /**
     * @param PolygonNode $p
     * @param PolygonNode $q
     */
    public function mergeRight(PolygonNode $p, PolygonNode $q)
    {
        /* Label contour as external */
        $q->getProxy()->setHole(false);

        if ($p->getProxy() !== $q->getProxy()) {
            /* Assign p's vertex list to the left end of q's list */
            $q->getProxy()->vertexList[PolygonUtils::RIGHT]->setNext(
                $p->getProxy()->vertexList[PolygonUtils::LEFT]
            );
            $q->getProxy()->vertexList[PolygonUtils::RIGHT] =
                $p->getProxy()->vertexList[PolygonUtils::RIGHT];

            /* Redirect any p->proxy references to q->proxy */
            $target = $p->getProxy();
            for ($node = $this->topNode; ($node != null); $node = $node->getNext()) {
                if ($node->getProxy() === $target) {
                    $node->setActive(0);
                    $node->setProxy($q->getProxy());
                }
            }
        }
    }

    /**
     * @return int
     */
    public function countContours()
    {
        $nc = 0;
        for ($polygon = $this->topNode; ($polygon != null); $polygon = $polygon->getNext()) {
            if ($polygon->getActive() != 0) {
                /* Count the vertices in the current contour */
                $nv = 0;
                for ($v = $polygon->getProxy()->vertexList[PolygonUtils::LEFT]; ($v != null); $v = $v->getNext()) {
                    $nv++;
                }

                /* Record valid vertex counts in the active field */
                if ($nv > 2) {
                    $polygon->setActive($nv);
                    $nc++;
                } else {
                    /* Invalid contour: just free the heap */
                    // VertexNode nextv = null ;
					// for (VertexNode v= polygon.proxy.v[LEFT]; (v != null); v
					// = nextv)
					// {
					// nextv= v.next;
					// v = null ;
					// }
                    $polygon->setActive(0);
                }
            }
        }

        return $nc;
    }

    /**
     * @return PolyInterface
     */
    public function getResult($polyClass)
    {
        $topNode = $this->getTopNode();
        $result = PolygonUtils::createNewPoly($polyClass);
        $numContours = $this->countContours();
        if ($numContours > 0) {
            $c = 0;
            /** @var PolygonNode $nPolyNode */
            $nPolyNode = null;
            for ($polyNode = $this->topNode; ($polyNode != null); $polyNode = $nPolyNode) {
                $nPolyNode = $polyNode->getNext();
                if ($polyNode->getActive() != 0) {
                    $polygon = $result;

                    if ($numContours > 1) {
                        $polygon = PolygonUtils::createNewPoly($polyClass);
                    }

                    if ($polyNode->getProxy()->isHole()) {
                        $polygon->setIsHole($polyNode->getProxy()->isHole());
                    }

                    // --- This algorithm puts the verticies into the Polygon in
                    // reverse order ---

                    for ($vtx = $polyNode->getProxy()->getVertexList()[PolygonUtils::LEFT]; ($vtx != null); $vtx = $vtx->getNext()) {
                        $polygon->add(new Point($vtx->getX(), $vtx->getY()));
                    }

                    if ($numContours > 1) {
                        $result->addPoly($polygon);
                    }
                    $c++;
                }
            }

            // --- Sort holes to the end of the list ---
            $orig = $result;
            $result = PolygonUtils::createNewPoly($polyClass);
            for ($i = 0; $i < $orig->getNumInnerPoly(); $i++) {
                $inner = $orig->getInnerPolygon($i);
                if (!$inner->isHole()) {
                    $result->addPoly($inner);
                }
            }
            for ($i = 0; $i < $orig->getNumInnerPoly(); $i++) {
                $inner = $orig->getInnerPolygon($i);
                if ($inner->isHole()) {
                    $result->addPoly($inner);
                }
            }
        }

        return $result;
    }
}
