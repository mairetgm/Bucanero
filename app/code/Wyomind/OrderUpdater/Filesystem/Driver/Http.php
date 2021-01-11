<?php
/**
 * Copyright © 2019 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wyomind\OrderUpdater\Filesystem\Driver;

/**
 * Origin filesystem driver modified so that we can simply get the result code
 * of an url
 */
class Http extends \Magento\Framework\Filesystem\Driver\Http
{
    protected $status = "";
    
    /**
     * Rewrites to store the status in a property
     * @param string $path
     * @return boolean
     */
    public function isExists($path)
    {
        $headers = array_change_key_case(get_headers($this->getScheme() . $path, 1), CASE_LOWER);

        $this->status = $headers[0];

        if (strpos($this->status, '200 OK') === false) {
            $result = false;
        } else {
            $result = true;
        }

        return $result;
    }

    /**
     * Retrieve status of the url
     * Doesn't load the url if not needed
     * @param string $path
     * @return string
     */
    public function getStatus($path = null)
    {
        if ($this->status == "") {
            if ($path != null) {
                $headers = array_change_key_case(get_headers($this->getScheme() . $path, 1), CASE_LOWER);
                $this->status = $headers[0];
            }
        }
        return $this->status;
    }
}