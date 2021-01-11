<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-01-11T23:10:04+00:00
 * File:          app/code/Xtento/CustomAttributes/Plugin/Checkout/RegistrationPlugin.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Plugin\Checkout;

use Magento\Framework\UrlInterface;

/**
 * Class RegistrationPlugin
 * @package Xtento\CustomAttributes\Plugin\Checkout
 */
class RegistrationPlugin
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * RegistrationPlugin constructor.
     *
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        UrlInterface $urlBuilder,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata

    ) {
        $this->urlBuilder = $urlBuilder;
        $this->productMetadata = $productMetadata;
    }

    /**
     * @param \Magento\Checkout\Block\Registration $subject
     * @param $result
     *
     * @return string
     */
    public function afterGetCreateAccountUrl(\Magento\Checkout\Block\Registration $subject, $result)
    {
        $version = $this->productMetadata->getVersion();
        if (version_compare($version, '2.2.6', '<')) {
            return $result;
        }
        return $this->urlBuilder->getUrl('xtento_customattributes/index/delegateCreate');
    }
}