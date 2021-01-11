<?php
/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrdersExportTool\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var \Wyomind\Framework\Helper\ModuleFactory
     */
    public $license;

    /**
     * UpgradeSchema constructor.
     * @param \Wyomind\Framework\Helper\License\UpdateFactory $license
     */
    public function __construct(\Wyomind\Framework\Helper\License\UpdateFactory $license)
    {
        $this->license = $license;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    )
    {
        $this->license->create()->update(__CLASS__, $context);
        if (version_compare($context->getVersion(), '5.2.0') < 0) {
            $installer = $setup;
            $installer->startSetup();

            $installer->getConnection()->addColumn(
                $installer->getTable('ordersexporttool_profiles'),
                'escaper',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'length' => 2, 'nullable' => true, "comment" => 'Delimiter escaper']

            );

            $installer->endSetup();
        }
        if (version_compare($context->getVersion(), '8.0.0') < 0) {
            $installer = $setup;
            $installer->startSetup();

            $installer->getConnection()->addColumn(

                $installer->getTable('ordersexporttool_profiles'),
                'extra_footer',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'nullable' => true, "comment" => 'Additional footer']

            );
            $installer->getConnection()->addColumn(

                $installer->getTable('ordersexporttool_profiles'),
                'format',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 'length' => 1, 'nullable' => false, "default" => 1, "comment" => 'Format of the csv file']
            );
            $installer->getConnection()->addColumn(

                $installer->getTable('ordersexporttool_profiles'),
                'scope',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'length' => 255, 'nullable' => false, "default" => \Wyomind\OrdersExportTool\Helper\Data::ORDER, "comment" => 'Scope of the export']
            );

            $installer->getConnection()->addColumn(

                $installer->getTable('ordersexporttool_profiles'),
                'ftp_port',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'length' => 4, 'nullable' => true, "default" => null, "comment" => 'FTP port number']
            );

            $installer->getConnection()->addColumn(

                $installer->getTable('ordersexporttool_profiles'),
                'mail_sender',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'length' => 255, 'nullable' => true, "default" => null, "comment" => 'Email sender']
            );
            $installer->endSetup();
        }

    }
}