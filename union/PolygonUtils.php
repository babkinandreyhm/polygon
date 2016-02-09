<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 27.01.16
 * Time: 19:31
 */

namespace union;

use union\GeometricObject\AetTree;
use union\GeometricObject\BundleState;
use union\GeometricObject\EdgeNode;
use union\GeometricObject\EdgeTable;
use union\GeometricObject\ItNode;
use union\GeometricObject\ItNodeTable;
use union\GeometricObject\Polygon;
use union\GeometricObject\PolygonNode;
use union\GeometricObject\PolyInterface;
use union\GeometricObject\PolyDefault;
use union\GeometricObject\PolySimple;
use union\GeometricObject\StNode;
use union\GeometricObject\TopPolygonNode;
use union\GeometricObject\VertexType;

class PolygonUtils
{
    const LEFT = 0;
    const RIGHT = 1;
    const ABOVE = 0;
    const BELOW = 1;
    const CLIP = 0;
    const SUBJ = 1;

    const EPSILON = 2.2204460492503131e-016;

    private $contributions;

    /**
     * @param $polyClass
     *
     * @return PolyInterface
     */
    public static function createNewPoly($polyClass)
    {
        try {
            return new $polyClass;
        } catch (\Exception $e) {
            throw new \RuntimeException('Wrong poly class');
        }
    }

    public function union(PolyInterface $subject, PolyInterface $clip, $polyClass)
    {
        $hState = new HorizontalEdgeStates();
        $result = self::createNewPoly($polyClass);

        if ($subject->isEmpty() && $clip->isEmpty()) {
            return $result;
        }

        $lmtTable = new LocalMinimumTable();
        $sbte = new ScanBeamTreeEntries();
        $s_heap = null;
        $c_heap = null;

        if (!$subject->isEmpty()) {
            $sHeap = $this->buildLmt($lmtTable, $sbte, $subject, self::SUBJ);
        }

        if (!$clip->isEmpty()) {
            $cHeap = $this->buildLmt($lmtTable, $sbte, $clip, self::CLIP);
        }

        if ($lmtTable->getTopNode() === null) {
            return $result;
        }

        /* Build scanbeam table from scanbeam tree */
        $sbt = $sbte->buildSbt();

        $parity = [];
        $parity[0] = self::LEFT;
        $parity[1] = self::LEFT;

        $localMin = $lmtTable->getTopNode();
        $outPoly = new TopPolygonNode(); //used to create resulting polygon

        $aet = new AetTree();
        $scanbeam = 0;

        /* Process each scanbeam */
        while ($scanbeam < count($sbt)) {
            echo '$scanbeam : ' . $scanbeam . ', count($sbt): ' . count($sbt) . PHP_EOL;
            /* Set yb and yt to the bottom and top of the scanbeam */
            $yb = $sbt[$scanbeam++];
            $yt = 0.0;
            $dy = 0.0;

            if ($scanbeam < count($sbt)) {
                $yt = $sbt[$scanbeam];
                $dy = $yt - $yb;
            }

            /* === SCANBEAM BOUNDARY PROCESSING ================================ */

            /* If LMT node corresponding to yb exists */
            if ($localMin != null) {
                if ($localMin->getY() === $yb) {
                    /* Add edges starting at this local minimum to the AET */
                    for ($edge = $localMin->getFirstBound(); ($edge != null); $edge = $edge->getNextBound()) {
                        $aet = $this->addEdgeToAet($aet, $edge);
                    }
                    $localMin = $localMin->getNext();
                }
            }

            $aet->printTree();

            /* Set dummy previous x value */
            $px = -1e1000;

            /* Create bundles within AET */
            $e0 = $aet->getTopNode();
            $e1 = $aet->getTopNode();

            /* Set up bundle fields of first edge */
            $aet->getTopNode()->bundle[self::ABOVE][$aet->getTopNode()->getType()] =
                ($aet->getTopNode()->getTop()->getY() !== $yb) ? 1 : 0;
            $aet->getTopNode()->bundle[self::ABOVE][(($aet->getTopNode()->getType() === 0) ? 1 : 0)] = 0;
            $aet->getTopNode()->bstate[self::ABOVE] = BundleState::unbundled();

            for ($nextEdge = $aet->getTopNode()->getNext(); ($nextEdge != null); $nextEdge = $nextEdge->getNext()) {
                $neType = $nextEdge->getType();
                $neTypeOpp = (($nextEdge->getType() === 0) ? 1 : 0); //next edge type opposite

                /* Set up bundle fields of next edge */
                $nextEdge->bundle[self::ABOVE][$neType] = ($nextEdge->getTop()->getY() !== $yb) ? 1 : 0;
                $nextEdge->bundle[self::ABOVE][$neTypeOpp] = 0;
                $nextEdge->bstate[self::ABOVE] = BundleState::unbundled();

                /* Bundle edges above the scanbeam boundary if they coincide */
                if ($nextEdge->bundle[self::ABOVE][$neType] === 1) {
                    if (self::eq($e0->getXb(), $nextEdge->getXb())
                        && self::eq($e0->getDx(), $nextEdge->getDx())
                        && ($e0->getTop()->getY() != $yb)
                    ) {
                        $nextEdge->bundle[self::ABOVE][$neType] ^= $e0->bundle[self::ABOVE][$neType];
                        $nextEdge->bundle[self::ABOVE][$neTypeOpp] = $e0->bundle[self::ABOVE][$neTypeOpp];
                        $nextEdge->bstate[self::ABOVE] = BundleState::bundleHead();
                        $e0->bundle[self::ABOVE][self::CLIP] = 0;
                        $e0->bundle[self::ABOVE][self::SUBJ] = 0;
                        $e0->bstate[self::ABOVE] = BundleState::bundleTail();
                    }
                    $e0 = $nextEdge;
                }
            }

            $horiz = [];
            $horiz[self::CLIP] = HorizontalEdgeStates::NH;
            $horiz[self::SUBJ] = HorizontalEdgeStates::NH;

            $exists = [];
            $exists[self::CLIP] = 0;
            $exists[self::SUBJ] = 0;

            /** @var PolygonNode|null $cf */
            $cf = null;

            /* Process each edge at this scanbeam boundary */
            for ($edge = $aet->getTopNode(); ($edge != null); $edge = $edge->getNext()) {
//                echo $edge->getVertex() . PHP_EOL;
                $exists[self::CLIP] = $edge->bundle[self::ABOVE][self::CLIP]
                    + ($edge->bundle[self::BELOW][self::CLIP] << 1);
                $exists[self::SUBJ] = $edge->bundle[self::ABOVE][self::SUBJ]
                    + ($edge->bundle[self::BELOW][self::SUBJ] << 1);

                if (($exists[self::CLIP] !== 0) || ($exists[self::SUBJ] !== 0)) {
                    /* Set bundle side */
                    $edge->bside[self::CLIP] = $parity[self::CLIP];
                    $edge->bside[self::SUBJ] = $parity[self::SUBJ];

                    $contributing = false;
                    $br = 0;
                    $bl = 0;
                    $tr = 0;
                    $tl = 0;

                    $contributing =
                        (($exists[self::CLIP] !== 0) && (!($parity[self::SUBJ] !== 0) || ($horiz[self::SUBJ] !== 0)))
                        || (($exists[self::SUBJ] !== 0) && (!($parity[self::CLIP] !== 0) || ($horiz[self::CLIP] !== 0)))
                        || (($exists[self::CLIP] !== 0)
                            && ($exists[self::SUBJ] !== 0)
                            && ($parity[self::CLIP] === $parity[self::SUBJ]))
                    ;
                    $br = (($parity[self::CLIP] !== 0) || ($parity[self::SUBJ] !== 0)) ? 1 : 0;
                    $bl = ((($parity[self::CLIP] ^ $edge->bundle[self::ABOVE][self::CLIP]) !== 0)
                        || (($parity[self::SUBJ] ^ $edge->bundle[self::ABOVE][self::SUBJ]) !== 0)
                    ) ? 1: 0;
                    $tr = ((($parity[self::CLIP] ^ (($horiz[self::CLIP] !== HorizontalEdgeStates::NH) ? 1 : 0)) !== 0)
                        || (($parity[self::SUBJ] ^ (($horiz[self::SUBJ] !== HorizontalEdgeStates::NH) ? 1 : 0)) !== 0)
                    ) ? 1 : 0;
                    $tl = ((($parity[self::CLIP]
                            ^ (($horiz[self::CLIP] !== HorizontalEdgeStates::NH) ? 1 : 0)
                            ^ $edge->bundle[self::BELOW][self::CLIP]) !== 0)
                        || (($parity[self::SUBJ]
                            ^ (($horiz[self::SUBJ] !== HorizontalEdgeStates::NH) ? 1 : 0)
                            ^ $edge->bundle[self::BELOW][self::SUBJ]) !== 0)
                    ) ? 1 : 0;

                    /* Update parity */
                    $parity[self::CLIP] ^= $edge->bundle[self::ABOVE][self::CLIP];
                    $parity[self::SUBJ] ^= $edge->bundle[self::ABOVE][self::SUBJ];

                    /* Update horizontal state */
                    if ($exists[self::CLIP] !== 0) {
                        $horiz[self::CLIP] = $hState->nextState[$horiz[self::CLIP]][(($exists[self::CLIP] - 1) << 1)
                            + $parity[self::CLIP]];
                    }
                    if ($exists[self::SUBJ] !== 0) {
                        $horiz[self::SUBJ] = $hState->nextState[$horiz[self::SUBJ]][(($exists[self::SUBJ] - 1) << 1)
                            + $parity[self::SUBJ]];
                    }

                    if ($contributing) {
                        $xb = $edge->getXb();

                        $vClass = VertexType::getType($tr, $tl, $br, $bl);
                        switch ($vClass) {
                            case VertexType::EMN:
                            case VertexType::IMN:
                                $edge->outp[self::ABOVE] = $outPoly->addLocalMin($xb, $yb);
                                $px = $xb;
                                $cf = $edge->outp[self::ABOVE];
                                break;
                            case VertexType::ERI:
                                if ($xb !== $px) {
                                    $cf->addRight($xb, $yb);
                                    $px = $xb;
                                }
                                $edge->outp[self::ABOVE] = $cf;
                                $cf = null;
                                break;
                            case VertexType::ELI:
                                $edge->outp[self::BELOW]->addLeft($xb, $yb);
                                $px = $xb;
                                $cf = $edge->outp[self::BELOW];
                                break;
                            case VertexType::EMX:
                                if ($xb !== $px) {
                                    $cf->addLeft($xb, $yb);
                                    $px = $xb;
                                }
                                $outPoly->mergeRight($cf, $edge->outp[self::BELOW]);
                                $cf = null;
                                break;
                            case VertexType::ILI:
                                if ($xb !== $px) {
                                    $cf->addLeft($xb, $yb);
                                    $px = $xb;
                                }
                                $edge->outp[self::ABOVE] = $cf;
                                $cf = null;
                                break;
                            case VertexType::IRI:
                                $edge->outp[self::BELOW]->addRight($xb, $yb);
                                $px = $xb;
                                $cf = $edge->outp[self::BELOW];
                                $edge->outp[self::BELOW] = null;
                                break;
                            case VertexType::IMX:
                                if ($xb !== $px) {
                                    $cf->addRight($xb, $yb);
                                    $px = $xb;
                                }
                                $outPoly->mergeLeft($cf, $edge->outp[self::BELOW]);
                                $cf = null;
                                $edge->outp[self::BELOW] = null;
                                break;
                            case VertexType::IMM:
                                if ($xb !== $px) {
                                    $cf->addRight($xb, $yb);
                                    $px = $xb;
                                }
                                $outPoly->mergeLeft($cf, $edge->outp[self::BELOW]);
                                $edge->outp[self::BELOW] = null;
                                $edge->outp[self::ABOVE] = $outPoly->addLocalMin($xb, $yb);
                                $cf = $edge->outp[self::ABOVE];
                                break;
                            case VertexType::EMM:
                                if ($xb !== $px) {
                                    $cf->addLeft($xb, $yb);
                                    $px = $xb;
                                }
                                $outPoly->mergeRight($cf, $edge->outp[self::BELOW]);
                                $edge->outp[self::BELOW] = null;
                                $edge->outp[self::ABOVE] = $outPoly->addLocalMin($xb, $yb);
                                $cf = $edge->outp[self::ABOVE];
                                break;
                            case VertexType::LED:
                                if ($edge->getBot()->getY() === $yb) {
                                    $edge->outp[self::BELOW]->addLeft($xb, $yb);
                                }
                                $edge->outp[self::ABOVE] = $edge->outp[self::BELOW];
                                $px = $xb;
                                break;
                            case VertexType::RED:
                                if ($edge->getBot()->getY() === $yb) {
                                    $edge->outp[self::BELOW]->addRight($xb, $yb);
                                }
                                $edge->outp[self::ABOVE] = $edge->outp[self::BELOW];
                                $px = $xb;
                                break;
                            default:
                                break;
                        } /* End of switch */
                    } /* End of contributing conditional */
                } /* End of edge exists conditional */
            } /* End of AET loop */

            /* Delete terminating edges from the AET, otherwise compute xt */
            for ($edge = $aet->getTopNode(); ($edge != null); $edge = $edge->getNext()) {
                if ($edge->getTop()->getY() === $yb) {
                    $prevEdge = $edge->getPrev();
                    $nextEdge = $edge->getNext();

                    if ($prevEdge !== null) {
                        $prevEdge->setNext($nextEdge);
                    } else {
                        $aet->setTopNode($nextEdge);
                    }

                    if ($nextEdge !== null) {
                        $nextEdge->setPrev($prevEdge);
                    }

                    /* Copy bundle head state to the adjacent tail edge if required */
                    if (($edge->getBstate()[self::BELOW] === BundleState::bundleHead()) && $prevEdge != null) {
                        if ($prevEdge->getBstate()[self::BELOW] == BundleState::bundleTail()) {
                            $prevEdge->outp[self::BELOW] = $edge->outp[self::BELOW];
                            $prevEdge->addBstate(self::BELOW, BundleState::unbundled());
                            if ($prevEdge->getPrev() != null) {
                                if ($prevEdge->getPrev()->getBstate()[self::BELOW] == BundleState::bundleTail()) {
                                    $prevEdge->addBstate(self::BELOW, BundleState::bundleHead());
                                }
                            }
                        }
                    }
                } else {
                    if ($edge->getTop()->getY() === $yt) {
                        $edge->setXt($edge->getTop()->getX());
                    } else {
                        $edge->setXt($edge->getBot()->getX() + $edge->getDx() * ($yt - $edge->getBot()->getY()));
                    }
                }
            }

            if ($scanbeam < $sbte->getSbtEntries()) {
                /*
                 * === SCANBEAM INTERIOR PROCESSING
                 * ==============================
                 */

                /* Build intersection table for the current scanbeam */
                $itTable = new ItNodeTable();
                $this->buildIntersectionTable($aet, $dy, $itTable);

                /* Process each node in the intersection table */

                for ($insct = $itTable->getTopNode(); ($insct != null); $insct = $insct->getNext()) {
                    $e0 = $insct->getIe()[0];
                    $e1 = $insct->getIe()[1];

                    /* Only generate output for contributing intersections */

                    if ((($e0->bundle[self::ABOVE][self::CLIP] != 0) || ($e0->bundle[self::ABOVE][self::SUBJ] != 0))
                        && (($e1->bundle[self::ABOVE][self::CLIP] != 0) || ($e1->bundle[self::ABOVE][self::SUBJ] != 0))
                    ) {
                        $p = $e0->outp[self::ABOVE];
                        $q = $e1->outp[self::ABOVE];

                        $ix = $insct->getPoint()->getX();
                        $iy = $insct->getPoint()->getY() + $yb;

                        $inClip = ((($e0->bundle[self::ABOVE][self::CLIP] != 0)
                                && !($e0->getBside()[self::CLIP] != 0))
                            || (($e1->bundle[self::ABOVE][self::CLIP] != 0) && ($e1->getBside()[self::CLIP] != 0))
                                || (!($e0->bundle[self::ABOVE][self::CLIP] != 0)
                                    && !($e1->bundle[self::ABOVE][self::CLIP] != 0)
                                && ($e0->getBside()[self::CLIP] != 0) && ($e1->getBside()[self::CLIP] != 0))
                        ) ? 1 : 0;

                        $inSubj = ((($e0->bundle[self::ABOVE][self::SUBJ] != 0)
                                && !($e0->getBside()[self::SUBJ] != 0))
                            || (($e1->bundle[self::ABOVE][self::SUBJ] != 0) && ($e1->getBside()[self::SUBJ] != 0))
                                || (!($e0->bundle[self::ABOVE][self::SUBJ] != 0)
                                    && !($e1->bundle[self::ABOVE][self::SUBJ] != 0)
                                && ($e0->getBside()[self::SUBJ] != 0) && ($e1->getBside()[self::SUBJ] != 0))
                        ) ? 1 : 0;

                        $br = 0;
                        $bl = 0;
                        $tr = 0;
                        $tl = 0;

                        $tr = (($inClip != 0) || ($inSubj != 0)) ? 1 : 0;
                        $tl = ((($inClip ^ $e1->bundle[self::ABOVE][self::CLIP]) != 0)
                            || (($inSubj ^ $e1->bundle[self::ABOVE][self::SUBJ]) != 0)
                        ) ? 1 : 0;
                        $br = ((($inClip ^ $e0->bundle[self::ABOVE][self::CLIP]) != 0)
                            || (($inSubj ^ $e0->bundle[self::ABOVE][self::SUBJ]) != 0)
                        ) ? 1 : 0;
                        $bl = ((($inClip
                                ^ $e1->bundle[self::ABOVE][self::CLIP]
                                ^ $e0->bundle[self::ABOVE][self::CLIP]) != 0)
                            || (($inSubj
                                ^ $e1->bundle[self::ABOVE][self::SUBJ]
                                ^ $e0->bundle[self::ABOVE][self::SUBJ]) != 0)
                        ) ? 1: 0;

                        $vClass = VertexType::getType($tr, $tl, $br, $bl);
                        switch ($vClass) {
                            case VertexType::EMN:
                                $e0->outp[self::ABOVE] = $outPoly->addLocalMin($ix, $iy);
                                $e1->outp[self::ABOVE] = $e0->outp[self::ABOVE];
                                break;
                            case VertexType::ERI:
                                if ($p != null) {
                                    $p->addRight($ix, $iy);
                                    $e1->outp[self::ABOVE] = $p;
                                    $e0->outp[self::ABOVE] = null;
                                }
                                break;
                            case VertexType::ELI:
                                if ($q != null) {
                                    $q->addLeft($ix, $iy);
                                    $e0->outp[self::ABOVE] = $q;
                                    $e1->outp[self::ABOVE] = null;
                                }
                                break;
                            case VertexType::EMX:
                                if (($p != null) && ($q != null)) {
                                    $p->addLeft($ix, $iy);
                                    $outPoly->mergeRight($p, $q);
                                    $e0->outp[self::ABOVE] = null;
                                    $e1->outp[self::ABOVE] = null;
                                }
                                break;
                            case VertexType::IMN:
                                $e0->outp[self::ABOVE] = $outPoly->addLocalMin($ix, $iy);
                                $e1->outp[self::ABOVE] = $e0->outp[self::ABOVE];
                                break;
                            case VertexType::ILI:
                                if ($p != null) {
                                    $p->addLeft($ix, $iy);
                                    $e1->outp[self::ABOVE] = $p;
                                    $e0->outp[self::ABOVE] = null;
                                }
                                break;
                            case VertexType::IRI:
                                if ($q != null) {
                                    $q->addRight($ix, $iy);
                                    $e0->outp[self::ABOVE] = $q;
                                    $e1->outp[self::ABOVE] = null;
                                }
                                break;
                            case VertexType::IMX:
                                if (($p !== null) && ($q != null)) {
                                    $p->addRight($ix, $iy);
                                    $outPoly->mergeLeft($p, $q);
                                    $e0->outp[self::ABOVE] = null;
                                    $e1->outp[self::ABOVE] = null;
                                }
                                break;
                            case VertexType::IMM:
                                if (($p !== null) && ($q != null)) {
                                    $p->addRight($ix, $iy);
                                    $outPoly->mergeLeft($p, $q);
                                    $e0->outp[self::ABOVE] = $outPoly->addLocalMin($ix, $iy);
                                    $e1->outp[self::ABOVE] = $e0->outp[self::ABOVE];
                                }
                                break;
                            case VertexType::EMM:
                                if (($p !== null) && ($q != null)) {
                                    $p->addLeft($ix, $iy);
                                    $outPoly->mergeRight($p, $q);
                                    $e0->outp[self::ABOVE] = $outPoly->addLocalMin($ix, $iy);
                                    $e1->outp[self::ABOVE] = $e0->outp[self::ABOVE];
                                }
                                break;
                            default:
                                break;
                        } /* End of switch */
                    } /* End of contributing intersection conditional */

                    /* Swap bundle sides in response to edge crossing */
                    if ($e0->bundle[self::ABOVE][self::CLIP] != 0) {
                        $e1->bside[self::CLIP] = ($e1->bside[self::CLIP] === 0) ? 1 : 0;
                    }
                    if ($e1->bundle[self::ABOVE][self::CLIP] != 0) {
                        $e0->bside[self::CLIP] = ($e0->bside[self::CLIP] === 0) ? 1 : 0;
                    }
                    if ($e0->bundle[self::ABOVE][self::SUBJ] != 0) {
                        $e1->bside[self::SUBJ] = ($e1->bside[self::SUBJ] === 0) ? 1 : 0;
                    }
                    if ($e1->bundle[self::ABOVE][self::SUBJ] != 0) {
                        $e0->bside[self::SUBJ] = ($e0->bside[self::SUBJ] === 0) ? 1 : 0;
                    }

                    /* Swap e0 and e1 bundles in the AET */
                    $prevEdge = $e0->getPrev();
                    $nextEdge = $e1->getNext();

                    if ($nextEdge != null) {
                        $nextEdge->setPrev($e0);
                    }

                    if ($e0->getBstate()[self::ABOVE] == BundleState::bundleHead()) {
                        $search = true;
                        while ($search) {
                            $prevEdge = $prevEdge->getPrev();
                            if ($prevEdge != null) {
                                if ($prevEdge->getBstate()[self::ABOVE] != BundleState::bundleTail()) {
                                    $search = false;
                                }
                            } else {
                                $search = false;
                            }
                        }
                    }
                    if ($prevEdge === null) {
                        $aet->getTopNode()->setPrev($e1);
                        $e1->setNext($aet->getTopNode());
                        $aet->setTopNode($e0->getNext());
                    } else {
                        $prevEdge->getNext()->setPrev($e1);
                        $e1->setNext($prevEdge->getNext());
                        $prevEdge->setNext($e0->getNext());
                    }
                    $e0->getNext()->setPrev($prevEdge);
                    $e1->getNext()->setPrev($e1);
                    $e0->setNext($nextEdge);
                } /* End of IT loop */

                /* Prepare for next scanbeam */
                for ($edge = $aet->getTopNode(); ($edge != null); $edge = $edge->getNext()) {
                    $nextEdge = $edge->getNext();
                    $succEdge = $edge->getSucc();
                    if (($edge->getTop()->getY() === $yt) && ($succEdge !== null)) {
                        /* Replace AET edge by its successor */
                        $succEdge->outp[self::BELOW] = $edge->outp[self::ABOVE];
                        $succEdge->bstate[self::BELOW] = $edge->bstate[self::ABOVE];
                        $succEdge->bundle[self::BELOW][self::CLIP] = $edge->bundle[self::ABOVE][self::CLIP];
                        $succEdge->bundle[self::BELOW][self::SUBJ] = $edge->bundle[self::ABOVE][self::SUBJ];
                        $prevEdge = $edge->getPrev();
                        if ($prevEdge !== null) {
                            $prevEdge->setNext($succEdge);
                        } else {
                            $aet->setTopNode($succEdge);
                        }
                        if ($nextEdge !== null) {
                            $nextEdge->setPrev($succEdge);
                        }
                        $succEdge->setPrev($prevEdge);
                        $succEdge->setNext($nextEdge);
                    } else {
                        /* Update this edge */
                        $edge->outp[self::BELOW] = $edge->outp[self::ABOVE];
                        $edge->bstate[self::BELOW] = $edge->bstate[self::ABOVE];
                        $edge->bundle[self::BELOW][self::CLIP] = $edge->bundle[self::ABOVE][self::CLIP];
                        $edge->bundle[self::BELOW][self::SUBJ] = $edge->bundle[self::ABOVE][self::SUBJ];
                        $edge->setXb($edge->getXt());
                    }
                    $edge->outp[self::ABOVE] = null;
                }
            }
        } /* === END OF SCANBEAM PROCESSING ================================== */

        /* Generate result polygon from out_poly */
        $result = $outPoly->getResult($polyClass);

        return $result;
    }

    protected function buildIntersectionTable(AetTree $aet, $dy, $itNodeTable)
    {
        /** @var StNode $st */
        $st = null;

        /* Process each AET edge */
        for ($edge = $aet->getTopNode(); ($edge != null); $edge = $edge->getNext()) {
            if (($edge->getBstate()[PolygonUtils::ABOVE] == BundleState::bundleHead())
                || ($edge->bundle[PolygonUtils::ABOVE][PolygonUtils::CLIP] !== 0)
                || ($edge->bundle[PolygonUtils::ABOVE][PolygonUtils::SUBJ] !== 0)
            ) {
                $st = $this->addStEdge($st, $itNodeTable, $edge, $dy);
            }
        }
    }

    /**
     * @param StNode $st
     * @param ItNodeTable $it
     * @param EdgeNode $edge
     * @param float $dy
     *
     * @returns StNode
     */
    private function addStEdge($st, $it, $edge, $dy)
    {
        if ($st === null) {
            /* Append edge onto the tail end of the ST */
            $st = new StNode($edge, null);
        } else {
            $den = ($st->getXt() - $st->getXb()) - ($edge->getXt() - $edge->getXb());

            /* If new edge and ST edge don't cross */
            if (($edge->getXt() >= $st->getXt()) || ($edge->getDx() === $st->getDx()) || (abs($den) <= self::EPSILON)) {
                /* No intersection - insert edge here (before the ST edge) */
                $existingNode = $st;
                $st = new StNode($edge, $existingNode);
            } else {
                /* Compute intersection between new edge and ST edge */
                $r = ($edge->getXb() - $st->getXb()) / $den;
                $x = $st->getXb() + $r * ($st->getXt() - $st->getXb());
                $y = $r * $dy;

                /* Insert the edge pointers and the intersection point in the IT */
                $it->setTopNode($this->addIntersection($it->getTopNode(), $st->getEdge(), $edge, $x, $y));

                /* Head further into the ST */
                $st->setPrev($this->addStEdge($st->getPrev(), $it, $edge, $dy));
            }
        }

        return $st;
    }

    /**
     * @param ItNode $itNode
     * @param EdgeNode $edge0
     * @param EdgeNode $edge1
     * @param float $x
     * @param float $y
     *
     * @returns ItNode
     */
    private function addIntersection($itNode, $edge0, $edge1, $x, $y)
    {
        if ($itNode === null) {
            /* Append a new node to the tail of the list */
            $itNode = new ItNode($edge0, $edge1, $x, $y, null);
        } else {
            if ($itNode->getPoint()->getY() > $y) {
                /* Insert a new node mid-list */
                $existingNode = $itNode;
                $itNode = new ItNode($edge0, $edge1, $x, $y, $existingNode);
            } else {
                /* Head further down the list */
                $itNode->setNext($this->addIntersection($itNode->getNext(), $edge0, $edge1, $x, $y));
            }
        }

        return $itNode;
    }

    /**
     * @param LocalMinimumTable $lmtTable
     * @param ScanBeamTreeEntries $sbte
     * @param PolyInterface $polygon
     * @param int $type
     *
     * @return EdgeTable
     */
    protected function buildLmt(LocalMinimumTable $lmtTable, ScanBeamTreeEntries $sbte, PolyInterface $polygon, $type)
    {
        /* Create the entire input polygon edge table in one go */
        $edgeTable = new EdgeTable();

        for ($c = 0; $c < $polygon->getNumInnerPoly(); $c++) {
            $innerPolygon = $polygon->getInnerPolygon($c);
            if (!$innerPolygon->isContributing(0)) {
                $innerPolygon->setContributing(0, true);
            } else {
                /* Perform contour optimisation */
                $numVertices = 0;
                $eIndex = 0;
                $edgeTable = new EdgeTable();

                for ($i = 0; $i < $innerPolygon->getNumPoints(); $i++) {
                    if ($this->isOptimal($innerPolygon, $i)) {
                        $x = $innerPolygon->getX($i);
                        $y = $innerPolygon->getY($i);
                        $edgeTable->addNode($x, $y);

                        /* Perform contour optimisation */
                        self::addToSbtree($sbte, $y);
                        $numVertices++;
                    }
                }

                /* Do the contour forward pass */
                for ($min = 0; $min < $numVertices; $min++) {
                    /* If a forward local minimum */
                    if ($edgeTable->fwdMin($min)) {
                        /* Search for the next local maximum */
                        $numEdges = 1;
                        $max = $this->nextIndex($min, $numVertices);
                        while ($edgeTable->notFMax($max)) {
                            $numEdges++;
                            $max = $this->nextIndex($max, $numVertices);
                        }

                        /* Build the next edge list */
                        $v = $min;
                        $edgeNode = $edgeTable->getNode($eIndex);
                        $edgeNode->bstate[self::BELOW] = BundleState::unbundled();
                        $edgeNode->bundle[self::BELOW][self::CLIP] = 0;
                        $edgeNode->bundle[self::BELOW][self::SUBJ] = 0;

                        for ($i = 0; $i < $numEdges; $i++) {
                            $ei = $edgeTable->getNode($eIndex + $i);
                            $ev = $edgeTable->getNode($v);

                            $ei->setXb($ev->getVertex()->getX());
                            $ei->getBot()->setX($ev->getVertex()->getX());
                            $ei->getBot()->setY($ev->getVertex()->getY());

                            $v = $this->nextIndex($v, $numVertices);
                            $ev = $edgeTable->getNode($v);

                            $ei->getTop()->setX($ev->getVertex()->getX());
                            $ei->getTop()->setY($ev->getVertex()->getY());
                            $ei->setDx(
                                ($ev->getVertex()->getX() - $ei->getBot()->getX())
                                / ($ei->getTop()->getY() - $ei->getBot()->getY())
                            );
                            $ei->setType($type);
                            $ei->outp[self::ABOVE] = null;
                            $ei->outp[self::BELOW] = null;
                            $ei->setNext(null);
                            $ei->setPrev(null);
                            $succ = (($numEdges > 1) && ($i < ($numEdges - 1)))
                                ? $edgeTable->getNode($eIndex + $i + 1)
                                : null;
                            $ei->setSucc($succ);
//                            $pred = (($numEdges > 1) && ($i > 0))
//                                ? $edgeNode->getNode($eIndex + $i - 1)
//                                : null;
                            $ei->setNextBound(null);
                            $ei->setBside([
                                self::CLIP => self::LEFT,
                                self::SUBJ => self::LEFT
                            ]);
                        }

                        self::insertBound(
                            self::boundList($lmtTable, $edgeTable->getNode($min)->getVertex()->getY()),
                            $edgeNode
                        );

                        $eIndex += $numEdges;
                    }
                }

                /* Do the contour reverse pass */
                for ($min = 0; $min < $numVertices; $min++) {
                    /* If a reverse local minimum... */
                    if ($edgeTable->revMin($min)) {
                        /* Search for the previous local maximum */
                        $numEdges = 1;
                        $max = $this->prevIndex($min, $numVertices);
                        while ($edgeTable->notRMax($max)) {
                            $numEdges++;
                            $max = $this->prevIndex($max, $numVertices);
                        }

                        /* Build the previous edge list */
                        $v = $min;
                        $edgeNode = $edgeTable->getNode($eIndex);
                        $edgeNode->addBstate(self::BELOW, BundleState::unbundled());
                        $edgeNode->bundle[self::BELOW][self::CLIP] = 0;
                        $edgeNode->bundle[self::BELOW][self::SUBJ] = 0;

                        for ($i = 0; $i < $numEdges; $i++) {
                            $ei = $edgeTable->getNode($eIndex + $i);
                            $ev = $edgeTable->getNode($v);

                            $ei->setXb($ev->getVertex()->getX());
                            $ei->getBot()->setX($ev->getVertex()->getX());
                            $ei->getBot()->setY($ev->getVertex()->getY());

                            $v = $this->prevIndex($v, $numVertices);
                            $ev = $edgeTable->getNode($v);

                            $ei->getTop()->setX($ev->getVertex()->getX());
                            $ei->getTop()->setY($ev->getVertex()->getY());
                            $ei->setDx(
                                ($ev->getVertex()->getX() - $ei->getBot()->getX())
                                / ($ei->getTop()->getY() - $ei->getBot()->getY())
                            );
                            $ei->setType($type);
                            $ei->outp[self::ABOVE] = null;
                            $ei->outp[self::BELOW] = null;
                            $ei->setNext(null);
                            $ei->setPrev(null);
                            $succ = (($numEdges > 1) && ($i < ($numEdges - 1)))
                                ? $edgeTable->getNode($eIndex + $i + 1)
                                : null;
                            $ei->setSucc($succ);
//                            $pred = (($numEdges > 1) && ($i > 0))
//                                ? $edgeNode->getNode($eIndex + $i - 1)
//                                : null;
                            $ei->setNextBound(null);
                            $ei->setBside([
                                self::CLIP => self::LEFT,
                                self::SUBJ => self::LEFT
                            ]);
                        }

                        self::insertBound(
                            self::boundList($lmtTable, $edgeTable->getNode($min)->getVertex()->getY()),
                            $edgeNode
                        );

                        $eIndex += $numEdges;
                    }
                }
            }
        }

        return $edgeTable;
    }

    /**
     * @param PolyInterface $polygon
     * @param int $i
     *
     * @return bool
     */
    private function isOptimal(PolyInterface $polygon, $i)
    {
        return ($polygon->getY($this->prevIndex($i, $polygon->getNumPoints())) !== $polygon->getY($i)) ||
        ($polygon->getY($this->nextIndex($i, $polygon->getNumPoints())) !== $polygon->getY($i));
    }

    /**
     * @param LocalMinimumTable $lmtTable
     * @param float $y
     *
     * @return LocalMinimumNode
     */
    private static function boundList(LocalMinimumTable $lmtTable, $y)
    {
        if ($lmtTable->getTopNode() === null) {
            $lmtTable->setTopNode(new LocalMinimumNode($y));
            return $lmtTable->getTopNode();
        } else {
            /** @var LocalMinimumNode $prev */
            $prev = null;
            $node = $lmtTable->getTopNode();
            $done = false;
            while (!$done) {
                if ($y < $node->getY()) {
                    /* Insert a new LMT node before the current node */
                    $existingNode = $node;
                    $node = new LocalMinimumNode($y);
                    $node->setNext($existingNode);
                    if ($prev === null) {
                        $lmtTable->setTopNode($node);
                    } else {
                        $prev->setNext($node);
                    }

//                    if ($existingNode === $lmtTable->getTopNode()) {
//                        $lmtTable->setTopNode($node);
//                    }
                    $done = true;
                } elseif ($y > $node->getY()) {
                    /* Head further up the LMT */
                    if ($node->getNext() === null) {
                        $node->setNext(new LocalMinimumNode($y));
                        $node = $node->getNext();
                        $done = true;
                    } else {
                        $prev = $node;
                        $node = $node->getNext();
                    }
                } else {
                    /* Use this existing LMT node */
                    $done = true;
                }
            }
            return $node;
        }
    }

    /**
     * @param LocalMinimumNode $lmtNode
     * @param EdgeNode $e
     */
    private static function insertBound(LocalMinimumNode $lmtNode, EdgeNode $e)
    {
        if ($lmtNode->getFirstBound() === null) {
            /* Link node e to the tail of the list */
            $lmtNode->setFirstBound($e);
        } else {
            $done = false;
            /** @var EdgeNode|null $prevBound */
            $prevBound = null;
            $currentBound = $lmtNode->getFirstBound();
            while (!$done) {
                /* Do primary sort on the x field */
                if ($e->getBot()->getX() < $currentBound->getBot()->getX()) {
                    /* Insert a new node mid-list */
                    if ($prevBound === null) {
                        $lmtNode->setFirstBound($e);
                    } else {
                        $prevBound->setNextBound($e);
                    }
                    $e->setNextBound($currentBound);

                    $existingBound = $currentBound;
                    $currentBound = $e;
                    $currentBound->setNextBound($existingBound);
                    if ($lmtNode->getFirstBound() === $existingBound) {
                        $lmtNode->setFirstBound($currentBound);
                    }
                    $done = true;
                } elseif ($e->getBot()->getX() === $currentBound->getBot()->getX()) {
                    /* Do secondary sort on the dx field */
                    if ($e->getDx() < $currentBound->getDx()) {
                        /* Insert a new node mid-list */
                        if ($prevBound === null) {
                            $lmtNode->setFirstBound($e);
                        } else {
                            $prevBound->setNextBound($e);
                        }
                        $e->setNextBound($currentBound);
                        $existingBound = $currentBound;
                        $currentBound = $e;
                        $currentBound->setNextBound($existingBound);
                        if ($lmtNode->getFirstBound() === $existingBound) {
                            $lmtNode->setFirstBound($currentBound);
                        }
                        $done = true;
                    } else {
                        /* Head further down the list */
                        if ($currentBound->getNextBound() === null) {
                            $currentBound->setNextBound($e);
                            $done = true;
                        } else {
                            $prevBound = $currentBound;
                            $currentBound = $currentBound->getNextBound();
                        }
                    }
                } else {
                    /* Head further down the list */
                    if ($currentBound->getNextBound() === null) {
                        $currentBound->setNextBound($e);
                        $done = true;
                    } else {
                        $prevBound = $currentBound;
                        $currentBound = $currentBound->getNextBound();
                    }
                }
            }
        }
    }

    private function addEdgeToAet(AetTree $aet, EdgeNode $edge)
    {
//        echo $aet->getTree();
        if ($aet->getTopNode() === null) {
            /* Append edge onto the tail end of the AET */
            $aet->setTopNode($edge);
            $edge->setPrev(null);
            $edge->setNext(null);
        } else {
            $currentEdge = $aet->getTopNode();
            /** @var EdgeNode $prev */
            $prev = null;
            $done = false;
            while (!$done) {
                /* Do primary sort on the xb field */
                if ($edge->getXb() < $currentEdge->getXb()) {
                    /* Insert edge here (before the AET edge) */
                    $edge->setPrev($prev);
                    $edge->setNext($currentEdge);
                    $currentEdge->setPrev($edge);
                    if ($prev === null) {
                        $aet->setTopNode($edge);
                    } else {
                        $prev->setNext($edge);
                    }

//                    if ($currentEdge === $aet->getTopNode()) {
//                        $aet->setTopNode($edge);
//                    }
//                    $currentEdge = $edge;
                    $done = true;
                } elseif ($edge->getXb() == $currentEdge->getXb()) {
                    /* Do secondary sort on the dx field */
                    if ($edge->getDx() < $currentEdge->getDx()) {
                        /* Insert edge here (before the AET edge) */
                        $edge->setPrev($prev);
                        $edge->setNext($currentEdge);
                        $currentEdge->setPrev($edge);
                        if ($prev === null) {
                            $aet->setTopNode($edge);
                        } else {
                            $prev->setNext($edge);
                        }
//                        if ($currentEdge === $aet->getTopNode() ) {
//                            $aet->setTopNode($edge);
//                        }
//                        $currentEdge = $edge;
                        $done = true;
                    } else {
                        /* Head further into the AET */
                        $prev = $currentEdge;
                        if ($currentEdge->getNext() === null) {
                            $currentEdge->setNext($edge);
                            $edge->setPrev($currentEdge);
                            $edge->setNext(null);
                            $done = true;
                        } else {
                            $currentEdge = $currentEdge->getNext();
                        }
                    }
                } else {
                    /* Head further into the AET */
                    $prev = $currentEdge;
                    if ($currentEdge->getNext() === null) {
                        $currentEdge->setNext($edge);
                        $edge->setPrev($currentEdge);
                        $edge->setNext(null);
                        $done = true;
                    } else {
                        $currentEdge = $currentEdge->getNext();
                    }
                }
            }
        }

        return $aet;
    }

    /**
     * @param ScanBeamTreeEntries $sbte
     * @param int $y
     */
    private static function addToSbtree(ScanBeamTreeEntries $sbte, $y)
    {
        if ($sbte->getSbTree() == null) {
            $sbte->setSbTree(new ScanBeamTree($y));
            $sbte->setSbtEntries($sbte->getSbtEntries() + 1);
            return;
        }

        $treeNode = $sbte->getSbTree();
        $done = false;
        while (!$done) {
            if ($treeNode->getY() > $y) {
                if ($treeNode->getLess() === null) {
                    $treeNode->setLess(new ScanBeamTree($y));
                    $sbte->setSbtEntries($sbte->getSbtEntries() + 1);
                    $done = true;
                } else {
                    $treeNode = $treeNode->getLess();
                }
            } elseif ($treeNode->getY() < $y) {
                if ($treeNode->getMore() === null) {
                    $treeNode->setMore(new ScanBeamTree($y));
                    $sbte->setSbtEntries($sbte->getSbtEntries() + 1);
                    $done = true;
                } else {
                    $treeNode = $treeNode->getMore();
                }
            } else {
                $done = true;
            }
        }
    }

    /**
     * @param int $i
     * @param int $n
     *
     * @return int
     */
    public static function prevIndex($i, $n)
    {
        return (($i - 1 + $n) % $n);
    }

    /**
     * @param int $i
     * @param int $n
     *
     * @return int
     */
    public static function nextIndex($i, $n)
    {
        return (($i + 1) % $n);
    }

    /**
     * @param number $a
     * @param number $b
     *
     * @return bool
     */
    public static function eq($a, $b)
    {
        return abs($a - $b) <= self::EPSILON;
    }
}
