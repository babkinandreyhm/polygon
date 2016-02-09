<?php

namespace union;

class HorizontalEdgeStates
{
    const NH = 0;
    const BH = 1;
    const TH = 2;

    /**
     * @var array[array[int]]
     */
    public $nextState = [
        /*               ABOVE               BELOW               CROSS       */
        /*            L          R         L        R          L        R    */
        /* NH*/ [ self::BH, self::TH, self::TH, self::BH, self::NH, self::NH ],
        /* BH*/ [ self::NH, self::NH, self::NH, self::NH, self::TH, self::TH],
        /* TH*/ [ self::NH, self::NH, self::NH, self::NH, self::BH, self::BH ]
    ];
}