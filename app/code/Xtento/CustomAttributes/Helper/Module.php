<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-12-16T11:00:45+00:00
 * File:          app/code/Xtento/CustomAttributes/Helper/Module.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class Module extends \Xtento\XtCore\Helper\AbstractModule
{
    protected $edition = 'CE';
    protected $module = 'Xtento_CustomAttributes';
    protected $extId = 'MTWOXtento_CustomAttributes223410';
    protected $configPath = 'customattributes/general/';

    const REGISTRATION_ADDRESS = 'customattributes/general/registration';

    /**
     * @var ScopeConfigInterface
     */
    public $config;

    /**
     * Module constructor.
     *
     * @param Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Xtento\XtCore\Helper\Server $serverHelper
     * @param \Xtento\XtCore\Helper\Utils $utilsHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Registry $registry,
        \Xtento\XtCore\Helper\Server $serverHelper,
        \Xtento\XtCore\Helper\Utils $utilsHelper
    ) {
        $this->config = $context->getScopeConfig();
        parent::__construct($context, $registry, $serverHelper, $utilsHelper);
    }

    /**
     * @return bool
     */
    public function isModuleEnabled()
    {
        return parent::isModuleEnabled();
    }

    /**
     * @param string $configPath
     * @return bool
     */
    public function getConfig($configPath)
    {
        return $this->config->getValue(
            $configPath,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param $disable
     * @return bool
     */
    public function isShowCustomerAddress()
    {
        return $this->getConfig(self::REGISTRATION_ADDRESS);
    }
}


