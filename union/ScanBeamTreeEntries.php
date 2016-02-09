<?php

namespace union;

class ScanBeamTreeEntries
{
    /**
     * @var int
     */
    private $sbtEntries;

    /**
     * @var ScanBeamTree
     */
    private $sbTree;

    /**
     * @return int
     */
    public function getSbtEntries()
    {
        return $this->sbtEntries;
    }

    /**
     * @param int $sbtEntries
     *
     * @return $this
     */
    public function setSbtEntries($sbtEntries)
    {
        $this->sbtEntries = $sbtEntries;

        return $this;
    }

    /**
     * @return ScanBeamTree
     */
    public function getSbTree()
    {
        return $this->sbTree;
    }

    /**
     * @param ScanBeamTree $sbTree
     *
     * @return $this
     */
    public function setSbTree($sbTree)
    {
        $this->sbTree = $sbTree;

        return $this;
    }

    /**
     * @throws \Exception
     *
     * @return array
     */
    public function buildSbt()
    {
        $sbt = [];
        $entries = 0;

        $entries = $this->innerBuildSbt($entries, $sbt, $this->sbTree);
        if ($entries !== $this->sbtEntries) {
            throw new \Exception("Something went wrong buildign sbt from tree.");
        }

        return $sbt;
    }

    /**
     * @param int $entries
     * @param array $sbt
     * @param ScanBeamTree $sbtNode
     *
     * @return int
     */
    public function innerBuildSbt($entries, array &$sbt, ScanBeamTree $sbtNode)
    {
        if ($sbtNode->getLess() != null) {
            $entries = $this->innerBuildSbt($entries, $sbt, $sbtNode->getLess());
        }
        $sbt[$entries] = $sbtNode->getY();
        $entries++;
        if ($sbtNode->getMore() != null) {
            $entries = $this->innerBuildSbt($entries, $sbt, $sbtNode->getMore());
        }

        return $entries;
    }
}
