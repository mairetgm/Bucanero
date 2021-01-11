<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Wyomind\OrderUpdater\Helper;

class Config extends \Magento\Framework\App\Helper\AbstractHelper
{
    const SETTINGS_LOG = "orderupdater/settings/log";
    const SETTINGS_NB_PREVIEW = "orderupdater/settings/nb_preview";
    public function __construct(\Wyomind\OrderUpdater\Helper\Delegate $wyomind, \Magento\Framework\App\Helper\Context $context)
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        parent::__construct($context);
    }
    public function getSettingsLog()
    {
        return $this->framework->getDefaultConfig(self::SETTINGS_LOG);
    }
    public function getSettingsNbPreview()
    {
        return $this->framework->getDefaultConfig(self::SETTINGS_NB_PREVIEW);
    }
}