<?php


/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Framework\Helper;


use Magento\Framework\ObjectManagerInterface;

/**
 * Class Module
 * @package Wyomind\Framework\Helper
 */
class Module extends \Wyomind\Framework\Helper\License
{
    /**
     * @var \Magento\Framework\Module\ModuleList
     */
    protected $moduleList;

    /**
     * Module constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Module\ModuleList $moduleList
     * @param \Magento\Framework\App\Helper\Context $context
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Module\ModuleList $moduleList,
        \Magento\Framework\App\Helper\Context $context)
    {
        parent::__construct($objectManager, $context);
        $this->moduleList = $moduleList;

    }

    /**
     * @param $moduleName
     * @return bool
     */
    public function moduleIsEnabled($moduleName)
    {
        return $this->moduleList->has($moduleName);
    }

    /**
     * @return array
     */
    public function getModuleList()
    {
        $list = $this->moduleList->getAll();
        $list = array_filter($list, function ($key) {
            return strpos($key, "Wyomind_") === 0 && $key !== "Wyomind_Framework" && $key !== "Wyomind_Core";
        }, ARRAY_FILTER_USE_KEY);
        return $list;
    }
}

