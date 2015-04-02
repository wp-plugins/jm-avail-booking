<?php

class AvailabilityBookingAutoloader {

    public static function loader($class) {
        $filename = strtolower($class) . '.php';
        $file = plugin_dir_path(__FILE__) . '/' . $filename;
        if (!file_exists($file)) {
            return false;
        }
        include $file;
    }

}
