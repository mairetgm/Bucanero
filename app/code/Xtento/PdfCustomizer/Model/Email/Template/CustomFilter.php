<?php

/**
 * Product:       Xtento_PdfCustomizer
 * ID:            TP2Z1gIjMryzjs+kTRDh6aWTwEp5w7T8imVFGAtG5js=
 * Last Modified: 2019-02-13T21:41:22+00:00
 * File:          app/code/Xtento/PdfCustomizer/Model/Email/Template/CustomFilter.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\PdfCustomizer\Model\Email\Template;

use Magento\Directory\Model\RegionFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Xtento\XtCore\Helper\Utils;

class CustomFilter extends \Magento\Email\Model\Template\Filter
{
    /**
     * @var RegionFactory
     */
    protected $regionFactory;

    protected $filesystem;

    /**
     * CustomFilter constructor.
     *
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Variable\Model\VariableFactory $coreVariableFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Framework\UrlInterface $urlModel
     * @param \Pelago\Emogrifier $emogrifier
     * @param RegionFactory $regionFactory
     * @param Utils $utilsHelper
     * @param \Magento\Framework\Filesystem $filesystem
     * @param array $variables
     * @param null $cssInliner
     */
    public function __construct(
        \Magento\Framework\Stdlib\StringUtils $string,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Variable\Model\VariableFactory $coreVariableFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\App\State $appState,
        \Magento\Framework\UrlInterface $urlModel,
        \Pelago\Emogrifier $emogrifier,
        RegionFactory $regionFactory,
        Utils $utilsHelper,
        \Magento\Framework\Filesystem $filesystem,
        array $variables = [],
        $cssInliner = null
    ) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        if (version_compare($utilsHelper->getMagentoVersion(), '2.3', '>=')) {
            // 2.3+
            $configVariables = $objectManager->get('\Magento\Variable\Model\Source\Variables');
        } else {
            $configVariables = $objectManager->get('\Magento\Email\Model\Source\Variables');
        }
        if (version_compare($utilsHelper->getMagentoVersion(), '2.2', '<')) {
            // 2.0/2.1
            parent::__construct($string, $logger, $escaper, $assetRepo, $scopeConfig, $coreVariableFactory, $storeManager, $layout, $layoutFactory, $appState, $urlModel, $emogrifier, $configVariables, $variables);
        } else {
            parent::__construct($string, $logger, $escaper, $assetRepo, $scopeConfig, $coreVariableFactory, $storeManager, $layout, $layoutFactory, $appState, $urlModel, $emogrifier, $configVariables, $variables, $cssInliner);
        }
        $this->regionFactory = $regionFactory;
        $this->filesystem = $filesystem;
    }

    /**
     * Store config directive
     *
     * @param string[] $construction
     * @return string
     */
    public function configDirective($construction)
    {
        if (isset($construction[2]) && $construction[2] == ' path="general/store_information/region_id"') {
            $regionCode = parent::configDirective($construction);
            $region = $this->regionFactory->create()->load($regionCode);
            if ($region->getId()) {
                $regionCode = $region->getCode();
            }
            return $regionCode;
        } else {
            return parent::configDirective($construction);
        }
    }

    /**
     * Retrieve media file URL directive
     *
     * @param string[] $construction
     * @return string
     */
    public function mediaDirective($construction)
    {
        $params = $this->getParameters(html_entity_decode($construction[2], ENT_QUOTES));
        $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
        if ($mediaDirectory->isFile($params['url'])) {
            return $mediaDirectory->getAbsolutePath($params['url']);
        } else {
            return parent::mediaDirective($construction);
        }
    }
}