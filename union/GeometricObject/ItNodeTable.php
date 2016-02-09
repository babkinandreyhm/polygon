<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 29.01.16
 * Time: 19:52
 */

namespace union\GeometricObject;

use union\PolygonUtils;

class ItNodeTable
{
    /**
     * @var ItNode
     */
    private $topNode;

    /**
     * @return ItNode
     */
    public function getTopNode()
    {
        return $this->topNode;
    }

    /**
     * @param ItNode $topNode
     *
     * @return $this
     */
    public function setTopNode($topNode)
    {
        $this->topNode = $topNode;

        return $this;
    }
}
