<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\OrdersExportTool\Helper;

/**
 * Class Config
 * @package Wyomind\OrdersExportTool\Helper
 */
class Config extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     *
     */
    const SETTINGS_LOG = "ordersexporttool/advanced/enable_log";
    public function __construct(\Wyomind\OrdersExportTool\Helper\Delegate $wyomind, \Magento\Framework\App\Helper\Context $context)
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        parent::__construct($context);
    }
    /**
     * @return string
     */
    public function getSettingsLog()
    {
        return $this->_framework->getDefaultConfig($this::SETTINGS_LOG);
    }
}