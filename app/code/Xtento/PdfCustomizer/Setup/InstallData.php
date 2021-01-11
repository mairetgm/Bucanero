<?php

/**
 * Product:       Xtento_PdfCustomizer
 * ID:            TP2Z1gIjMryzjs+kTRDh6aWTwEp5w7T8imVFGAtG5js=
 * Last Modified: 2019-02-05T17:13:45+00:00
 * File:          app/code/Xtento/PdfCustomizer/Setup/InstallData.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\PdfCustomizer\Setup;

use Xtento\PdfCustomizer\Model\Files\TemplateReader;
use Xtento\PdfCustomizer\Model\PdfTemplateFactory;
use Xtento\PdfCustomizer\Model\PdfTemplateRepository as TemplateRepository;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

/**
 * Class InstallData
 * @package Xtento\PdfCustomizer\Setup
 * Adds the templates default on module install
 * @codeCoverageIgnore
 * @SuppressWarnings(PHPMD)
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var PdfTemplateFactory
     */
    private $templateFactory;

    /**
     * @var TemplateRepository
     */
    private $templateRepository;

    /**
     * @var TemplateReader
     */
    private $templateReader;

    /**
     * InstallData constructor.
     *
     * @param PdfTemplateFactory $templateFactory
     * @param TemplateRepository $templateRepository
     * @param TemplateReader $templateReader
     */
    public function __construct(
        PdfTemplateFactory $templateFactory,
        TemplateRepository $templateRepository,
        TemplateReader $templateReader
    ) {
        $this->templateFactory = $templateFactory;
        $this->templateRepository = $templateRepository;
        $this->templateReader = $templateReader;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return $this
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $templates = $this->templateReader->directoryParser();

        if (empty($templates)) {
            return $this;
        }

        foreach ($templates as $template) {
            $tmpl = $this->templateFactory->create();
            $tmpl->setData($template);
            //@codingStandardsIgnoreLine
            $this->templateRepository->save($tmpl);
        }
    }
}