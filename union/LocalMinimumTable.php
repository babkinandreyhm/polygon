<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 28.01.16
 * Time: 2:51
 */

namespace union;

class LocalMinimumTable
{
    /**
     * @var LocalMinimumNode
     */
    private $topNode;

    /**
     * @return LocalMinimumNode
     */
    public function getTopNode()
    {
        return $this->topNode;
    }

    /**
     * @param LocalMinimumNode $topNode
     *
     * @return $this
     */
    public function setTopNode(LocalMinimumNode $topNode)
    {
        $this->topNode = $topNode;

        return $this;
    }
}
