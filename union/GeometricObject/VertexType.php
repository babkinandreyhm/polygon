<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 29.01.16
 * Time: 18:53
 */

namespace union\GeometricObject;

class VertexType
{
    /**
     * Empty non-intersection
     */
    const NUL = 0;

    /**
     * External maximum
     */
    const EMX = 1;

    /**
     * External left intermediate
     */
    const ELI = 2;

    /**
     * Top edge
     */
    const TED = 3;

    /**
     * External right intermediate
     */
    const ERI = 4;

    /**
     * Right edge
     */
    const RED = 5;

    /**
     * Internal maximum and minimum
     */
    const IMM = 6;

    /**
     * Internal minimum
     */
    const IMN = 7;

    /**
     * External minimum
     */
    const EMN = 8;

    /**
     * External maximum and minimum
     */
    const EMM = 9;

    /**
     * Left edge
     */
    const LED = 10;

    /**
     * Internal left intermediate
     */
    const ILI = 11;

    /**
     * Bottom edge
     */
    const BED = 12;

    /**
     * Internal right intermediate
     */
    const IRI = 13;

    /**
     * Internal maximum
     */
    const IMX = 14;

    /**
     * Full non-intersection
     */
    const FUL = 15;

    /**
     * @param $tr
     * @param $tl
     * @param $br
     * @param $bl
     *
     * @return mixed
     */
    public static function getType($tr, $tl, $br, $bl)
    {
        return $tr + ($tl << 1) + ($br << 2) + ($bl << 3);
    }
}
