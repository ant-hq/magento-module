<?php

class Ant_Api_Helper_Io_File extends Varien_Io_File
{
    /**
     * Check source is file
     *
     * @param string $src
     * @return bool
     */
    protected function _checkSrcIsFile($src)
    {
        $result = false;
        set_error_handler(function() { /* ignore errors */ });
        if (is_string($src) && is_file($src) && is_readable($src)) {
            $result = true;
        }
        restore_error_handler();
        return $result;
    }

}