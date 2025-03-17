<?php

namespace Etsy\Utils;

/**
 *  Date request utilities.
 *
 * @author Rhys Hall hello@rhyshall.com
 */
class Date
{
    /**
     * Creates a DateTime object for now.
     *
     * @return DateTime
     */
    public static function now()
    {
        return new \DateTime('now');
    }

}
