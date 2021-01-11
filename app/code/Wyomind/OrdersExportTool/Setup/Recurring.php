<?php

/*
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrdersExportTool\Setup;

class Recurring implements \Magento\Framework\Setup\InstallSchemaInterface
{

    
    private $_framework = null;

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
            "view/adminhtml/layout/sales_order_view.xml"
        ];
        $this->_framework->copyFilesByMagentoVersion(__FILE__, $files);
    }

}
