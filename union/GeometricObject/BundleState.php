<?php

namespace union\GeometricObject;

final class BundleState
{
    const UNBUNDLED = 'UNBUNDLED';
    const BUNDLE_HEAD = 'BUNDLE_HEAD';
    const BUNDLE_TAIL = 'BUNDLE_TAIL';

    /**
     * @var string
     */
    private $mState;

    /**
     * @param string $state
     */
    public function __construct($state)
    {
        $this->mState = $state;
    }

    /**
     * @return BundleState
     */
    public static function unbundled()
    {
        return new self(self::UNBUNDLED);
    }

    /**
     * @return BundleState
     */
    public static function bundleHead()
    {
        return new self(self::BUNDLE_HEAD);
    }

    /**
     * @return BundleState
     */
    public static function bundleTail()
    {
        return new self(self::BUNDLE_TAIL);
    }
}
