<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 28.01.16
 * Time: 16:10
 */

namespace union\GeometricObject;

use union\Point\Point;

class EdgeNode
{

    public function __construct()
    {
        $this->vertex = new Point();
        $this->bot = new Point();
        $this->top = new Point();
    }

    /**
     * @var Point Piggy-backed contour vertex data
     */
    private $vertex;

    /**
     * @var Point Edge lower (x, y) coordinate
     */
    private $bot;

    /**
     * @var Point Edge upper (x, y) coordinate
     */
    private $top;

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
     * @var int PolygonUtils / subject edge flag
     */
    private $type;

    /**
     * @var array[] Bundle edge flags array[2][2]
     */
    public $bundle;

    /**
     * @var int[] Bundle left / right indicators
     */
    public $bside;

    /**
     * @var BundleState[] Edge bundle state
     */
    public $bstate;

    /**
     * @var PolygonNode[] Output polygon / tristrip pointer
     */
    public $outp;

    /**
     * @var EdgeNode Previous edge in the AET
     */
    private $prev;

    /**
     * @var EdgeNode Next edge in the AET
     */
    private $next;

    /**
     * @var EdgeNode Edge connected at the lower end
     */
    private $pred;

    /**
     * @var EdgeNode Edge connected at the upped end
     */
    private $succ;

    /**
     * @var EdgeNode Pointer to next bound in LMT
     */
    private $nextBound;

    /**
     * @return Point
     */
    public function getVertex()
    {
        return $this->vertex;
    }

    /**
     * @param Point $vertex
     *
     * @return $this
     */
    public function setVertex($vertex)
    {
        $this->vertex = $vertex;

        return $this;
    }

    /**
     * @return Point
     */
    public function getBot()
    {
        return $this->bot;
    }

    /**
     * @param Point $bot
     *
     * @return $this
     */
    public function setBot($bot)
    {
        $this->bot = $bot;

        return $this;
    }

    /**
     * @return Point
     */
    public function getTop()
    {
        return $this->top;
    }

    /**
     * @param Point $top
     *
     * @return $this
     */
    public function setTop($top)
    {
        $this->top = $top;

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
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param int $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return array[]
     */
    public function getBundle()
    {
        return $this->bundle;
    }

    /**
     * @param mixed $key
     * @param array $bundle
     *
     * @return $this
     */
    public function addBundle($key, array $bundle)
    {
        $this->bundle[$key] = $bundle;

        return $this;
    }

    /**
     * @param \array[] $bundle
     *
     * @return $this
     */
    public function setBundle($bundle)
    {
        $this->bundle = $bundle;

        return $this;
    }

    /**
     * @return \int[]
     */
    public function getBside()
    {
        return $this->bside;
    }

    /**
     * @param \int[] $bside
     *
     * @return $this
     */
    public function setBside($bside)
    {
        $this->bside = $bside;

        return $this;
    }

    /**
     * @return BundleState[]
     */
    public function getBstate()
    {
        return $this->bstate;
    }

    /**
     * @param mixed $key
     * @param BundleState $state
     *
     * @return $this
     */
    public function addBstate($key, BundleState $state)
    {
        $this->bstate[$key] = $state;

        return $this;
    }


    /**
     * @param BundleState[] $bstates
     *
     * @return $this
     */
    public function setBstate($bstates)
    {
        $this->bstate = $bstates;

        return $this;
    }

    /**
     * @return PolygonNode[]
     */
    public function getOutp()
    {
        return $this->outp;
    }

    /**
     * @param PolygonNode[] $outp
     *
     * @return $this
     */
    public function setOutp(array $outp)
    {
        $this->outp = $outp;

        return $this;
    }

    /**
     * @return EdgeNode
     */
    public function getPrev()
    {
        return $this->prev;
    }

    /**
     * @param EdgeNode $prev
     *
     * @return $this
     */
    public function setPrev($prev)
    {
        $this->prev = $prev;

        return $this;
    }

    /**
     * @return EdgeNode
     */
    public function getNext()
    {
        return $this->next;
    }

    /**
     * @param EdgeNode $next
     *
     * @return $this
     */
    public function setNext($next)
    {
        $this->next = $next;

        return $this;
    }

    /**
     * @return EdgeNode
     */
    public function getPred()
    {
        return $this->pred;
    }

    /**
     * @param EdgeNode $pred
     *
     * @return $this
     */
    public function setPred($pred)
    {
        $this->pred = $pred;

        return $this;
    }

    /**
     * @return EdgeNode
     */
    public function getSucc()
    {
        return $this->succ;
    }

    /**
     * @param EdgeNode $succ
     *
     * @return $this
     */
    public function setSucc($succ)
    {
        $this->succ = $succ;

        return $this;
    }

    /**
     * @return EdgeNode
     */
    public function getNextBound()
    {
        return $this->nextBound;
    }

    /**
     * @param EdgeNode $nextBound
     *
     * @return $this
     */
    public function setNextBound($nextBound)
    {
        $this->nextBound = $nextBound;

        return $this;
    }
}
