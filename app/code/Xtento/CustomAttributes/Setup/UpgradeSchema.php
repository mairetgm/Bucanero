<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-03-27T20:28:24+00:00
 * File:          app/code/Xtento/CustomAttributes/Setup/UpgradeSchema.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Setup;

use Xtento\CustomAttributes\Api\Data\FieldsInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\UpgradeSchemaInterface;

//@codingStandardsIgnoreFile
/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '1.1.5', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable(InstallSchema::TABLE),
                FieldsInterface::SHOW_ON_PDF,
                [
                    'type' => Table::TYPE_TEXT,
                    'size' => 19,
                    'nullable' => false,
                    'comment' => 'Show on PDFs'
                ]
            );
        }
        if (version_compare($context->getVersion(), '1.2.0', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable(InstallSchema::TABLE),
                FieldsInterface::FRONTEND_OPTION,
                [
                    'type' => Table::TYPE_BOOLEAN,
                    'default' => 0,
                    'nullable' => false,
                    'comment' => 'Frontend option'
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable(InstallSchema::TABLE),
                FieldsInterface::MAX_LENGTH,
                [
                    'type' => Table::TYPE_INTEGER,
                    'size' => 255,
                    'nullable' => false,
                    'comment' => 'Max length'
                ]
            );
        }
        if (version_compare($context->getVersion(), '1.2.6', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable(InstallSchema::TABLE),
                FieldsInterface::SHOW_LAST_VALUE,
                [
                    'type' => Table::TYPE_BOOLEAN,
                    'default' => 0,
                    'nullable' => false,
                    'comment' => 'Populate with last value'
                ]
            );
        }
        if (version_compare($context->getVersion(), '1.3.2', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable(InstallSchema::TABLE),
                FieldsInterface::DISABLED_ON_FRONTEND,
                [
                    'type' => Table::TYPE_BOOLEAN,
                    'default' => 0,
                    'nullable' => false,
                    'comment' => 'Disabled on frontend'
                ]
            );
        }

        $setup->endSetup();
    }
}