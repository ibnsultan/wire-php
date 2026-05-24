<?php

namespace Wireblob;

class WireInstance
{
    private static $instance = null;
    private static $app_id = '';
    private static $secret = '';
    private static $api_key = '';

    /**
     * Get the wire singleton instance.
     *
     * @return Wire
     * @throws WireException
     */
    public static function get_wire()
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        self::$instance = new Wire(
            self::$api_key,
            self::$secret,
            self::$app_id
        );

        return self::$instance;
    }
}
