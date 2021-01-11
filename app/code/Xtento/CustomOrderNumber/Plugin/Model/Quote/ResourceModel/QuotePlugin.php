<?php

/**
 * Product:       Xtento_CustomOrderNumber
 * ID:            TP2Z1gIjMryzjs+kTRDh6aWTwEp5w7T8imVFGAtG5js=
 * Last Modified: 2020-07-20T14:22:51+00:00
 * File:          app/code/Xtento/CustomOrderNumber/Plugin/Model/Quote/ResourceModel/QuotePlugin.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomOrderNumber\Plugin\Model\Quote\ResourceModel;

use Magento\Quote\Model\ResourceModel\Quote;

class QuotePlugin
{
    /**
     * @var \Xtento\CustomOrderNumber\Helper\Generator
     */
    protected $incrementIdGenerator;

    /**
     * QuotePlugin constructor.
     *
     * @param \Xtento\CustomOrderNumber\Helper\Generator $incrementIdGenerator
     */
    public function __construct(
        \Xtento\CustomOrderNumber\Helper\Generator $incrementIdGenerator
    ) {
        $this->incrementIdGenerator = $incrementIdGenerator;
    }

    /**
     * @param Quote $subject
     * @param \Closure $proceed
     * @param \Magento\Quote\Model\Quote $quote
     * @return mixed
     */
    public function aroundGetReservedOrderId(Quote $subject, \Closure $proceed, $quote)
    {
        $originalSequence = $proceed($quote);
        // Generate new increment ID
        $incrementId = $this->incrementIdGenerator->generateIncrementIdWithLock($quote, \Magento\Sales\Model\Order::ENTITY, $originalSequence);
        return $incrementId;
    }
}
