<?php

/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Framework\Setup;

/**
 * Class Recurring
 * @package Wyomind\Framework\Setup
 */
class Recurring implements \Magento\Framework\Setup\InstallSchemaInterface
{


    /**
     * @var null|\Wyomind\Framework\Helper\Module
     */
    private $_framework = null;

    /**
     * Recurring constructor.
     * @param \Wyomind\Framework\Helper\Install $framework
     */
    public function __construct(
    \Wyomind\Framework\Helper\Install $framework
    )
    {
        $this->_framework = $framework;
    }

    /**
     * {@inheritdoc}
     */
    public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup,
        \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        $files = [
            "Magento/Ui/TemplateEngine/Xhtml/Result.php"
        ];
        $this->_framework->copyFilesByMagentoVersion(__FILE__, $files);
    }

}
