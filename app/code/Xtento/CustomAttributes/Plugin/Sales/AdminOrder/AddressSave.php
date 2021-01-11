<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2020-08-01T20:24:39+00:00
 * File:          app/code/Xtento/CustomAttributes/Plugin/Sales/AdminOrder/AddressSave.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Plugin\Sales\AdminOrder;

class AddressSave
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * AddressSave constructor.
     *
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->request = $request;
    }

    public function beforeExecute()
    {
        $postValues = $this->request->getPostValue();

        foreach ($postValues as $key => $postValue) {
            if (is_array($postValue)) {
                $this->request->setPostValue($key, implode(',', $postValue));
            }
        }

        return $this;
    }
}
