<?php
/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Framework\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Module\ModuleList;
use Magento\Framework\ObjectManagerInterface;
use Wyomind\Framework\Model\ResourceModel\ScheduleFactory;

/**
 * Class Heartbeat
 * @package Wyomind\Framework\Helper
 */
class Msi extends \Wyomind\Framework\Helper\Module
{

    /** @var \Magento\Inventory\Model\ResourceModel\Source\Collection */
    protected $sourceCollection = null;

    /**
     * Msi constructor.
     * @param ObjectManagerInterface $objectManager
     * @param ModuleList $moduleList
     * @param Context $context
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ModuleList $moduleList,
        Context $context
    )
    {
        parent::__construct($objectManager, $moduleList, $context);

        if ($this->moduleIsEnabled("Magento_Inventory") && $this->moduleIsEnabled("Magento_InventorySales")) {
            if (class_exists("\Magento\Inventory\Model\ResourceModel\Source\Collection")) {
                $this->sourceCollection = $objectManager->get("\Magento\Inventory\Model\ResourceModel\Source\Collection");
            }
        }


    }

    public function isMultiSource()
    {
        $sourcesCount = 0;
        $sourceCollection = $this->sourceCollection;
        $sourceCollection->addFieldToFilter('enabled', ['eq' => 1]);
        if ($sourceCollection != null) {
            $sourcesCount = $sourceCollection->getSize();
        }
        return $sourcesCount > 1;

    }

}