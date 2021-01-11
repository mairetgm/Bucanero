<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-01-12T17:30:34+00:00
 * File:          app/code/Xtento/CustomAttributes/Setup/InstallSchema.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Setup;

use Xtento\CustomAttributes\Api\Data\FieldsInterface;
use Xtento\CustomAttributes\Helper\Data;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;

class InstallSchema implements InstallSchemaInterface
{

    const TABLE = 'xtento_attributes_field_data';

    const STORE_TABLE = 'xtento_attributes_field_store';

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $table = $installer->getConnection()->newTable(
            $installer->getTable(self::TABLE)
        )->addColumn(
            FieldsInterface::ENTITY_ID,
            Table::TYPE_SMALLINT,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Entity Id'
        )->addColumn(
            FieldsInterface::ATTRIBUTE_ID,
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'default' => null],
            'Attribute Id'
        )->addColumn(
            FieldsInterface::IS_ACTIVE,
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '1'],
            'Is Active'
        )->addColumn(
            Data::TYPE_ID,
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Type Id'
        )->addColumn(
            Data::FIELD_IDENTIFIER,
            Table::TYPE_TEXT,
            255,
            ['nullable' => true, ],
            'The field unique identifier'

        /** Field specific columns start */

        )->addColumn(
            FieldsInterface::FRONTEND_INPUT,
            Table::TYPE_TEXT,
            255,
            ['nullable' => true, ],
            'The field type (text and so on)'
        )->addColumn(
            FieldsInterface::STORE_ID,
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Store ID'
        )->addColumn(
            FieldsInterface::CREATED_AT,
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
            'Created At'
        )->addColumn(
            FieldsInterface::UPDATED_AT,
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
            'Updated At'
        )->addColumn(
            FieldsInterface::ATTRIBUTE_CODE,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false ],
            'Attribute code'
        )->addColumn(
            FieldsInterface::FRONTEND_CLASS,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false ],
            'Input validation for Store Owner'
        )->addColumn(
            FieldsInterface::ATTRIBUTE_POSITION,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false ],
            'Attribute position'
        )->addColumn(
            FieldsInterface::IS_USED_IN_GRID,
            Table::TYPE_INTEGER,
            1,
            ['nullable' => false, 'default' => 1],
            'Shown in admin grids'
        )->addColumn(
            FieldsInterface::USED_IN_FORMS,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false ],
            'Used in forms'
        )->addColumn(
            FieldsInterface::CHECKOUT_POSITION,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false ],
            'Checkout position'
        )->addColumn(
            FieldsInterface::IS_VISIBLE_ON_FRONT,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false ],
            'Visible on Front-end'
        )->addColumn(
            FieldsInterface::IS_VISIBLE_ON_BACK,
            Table::TYPE_INTEGER,
            1,
            ['nullable' => false ],
            'Visible on Back-end'
        )->addColumn(
            FieldsInterface::CUSTOMER_GROUPS,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false ],
            'Customer groups'
        )->addColumn(
            FieldsInterface::TOOLTIP,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false ],
            'Tooltip'
        )->addColumn(
            FieldsInterface::SAVE_SELECTED,
            Table::TYPE_INTEGER,
            19,
            ['nullable' => false ],
            'Save selected'
        )->addColumn(
            FieldsInterface::APPLY_DEFAULT,
            Table::TYPE_INTEGER,
            19,
            ['nullable' => false ],
            'Automatically Apply Default Value'
        )->addColumn(

            FieldsInterface::FIELD_REQUIRED,
            Table::TYPE_INTEGER,
            19,
            ['nullable' => false ],
            'Required'
        )->addColumn(
            FieldsInterface::AVAILABLE_ON,
            Table::TYPE_INTEGER,
            19,
            ['nullable' => false ],
            'Checkout address'
        )->addIndex(
            $installer->getIdxName(
                $installer->getTable(self::TABLE),
                [FieldsInterface::ENTITY_ID, Data::FIELD_IDENTIFIER, FieldsInterface::STORE_ID],
                AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            [FieldsInterface::ENTITY_ID, Data::FIELD_IDENTIFIER, FieldsInterface::STORE_ID],
            ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addIndex(
            $installer->getIdxName(
                self::TABLE,
                [FieldsInterface::ATTRIBUTE_CODE],
                AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            FieldsInterface::ATTRIBUTE_CODE,
            ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addIndex(
            $installer->getIdxName(
                $installer->getTable(self::TABLE),
                [
                    FieldsInterface::ENTITY_ID,
                    Data::FIELD_IDENTIFIER,
                    FieldsInterface::STORE_ID
                ]
            ),
            [FieldsInterface::ENTITY_ID, Data::FIELD_IDENTIFIER, FieldsInterface::STORE_ID]
        )->addForeignKey(
            $installer->getFkName(
                self::TABLE,
                'attribute_id',
                'eav_attribute',
                'attribute_id'
            ),
            'attribute_id',
            $installer->getTable('eav_attribute'),
            'attribute_id',
            Table::ACTION_CASCADE
        )->setComment(
            'Attribute Entity'
        );
        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()->newTable(
            $installer->getTable(self::STORE_TABLE)
        )->addColumn(
            FieldsInterface::ENTITY_ID,
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'primary' => true],
            'Entity Id'
        )->addColumn(
            FieldsInterface::STORE_ID,
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Store ID'
        )->addIndex(
            $installer->getIdxName('xtento_attributes_field_store', [FieldsInterface::STORE_ID]),
            [FieldsInterface::STORE_ID]
//        )->addForeignKey(
//            $installer->getFkName(
//                'xtento_attributes_field_store',
//                FieldsInterface::ENTITY_ID,
//                'xtento_attributes_field_data',
//                FieldsInterface::ENTITY_ID
//            ),
//            FieldsInterface::ENTITY_ID,
//            $installer->getTable('xtento_attributes_field_data'),
//            FieldsInterface::ENTITY_ID,
//            Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName(
                self::STORE_TABLE,
                FieldsInterface::STORE_ID,
                'store',
                FieldsInterface::STORE_ID
            ),
            FieldsInterface::STORE_ID,
            $installer->getTable('store'),
            FieldsInterface::STORE_ID,
            Table::ACTION_CASCADE
        )->setComment(
            'Custom fields Store Linkage Table'
        );
        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()->newTable(
            $installer->getTable('xtento_attributes_field_store')
        )->addColumn(
            FieldsInterface::ENTITY_ID,
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'primary' => true],
            'Template ID'
        )->addColumn(
            FieldsInterface::STORE_ID,
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Store ID'
        )->addIndex(
            $installer->getIdxName('xtento_attributes_field_store', [FieldsInterface::STORE_ID]),
            [FieldsInterface::STORE_ID]
        )->addForeignKey(
            $installer->getFkName('xtento_attributes_field_store', FieldsInterface::ENTITY_ID, 'xtento_attributes_field_data', FieldsInterface::ENTITY_ID),
            FieldsInterface::ENTITY_ID,
            $installer->getTable('xtento_attributes_field_data'),
            FieldsInterface::ENTITY_ID,
            Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('xtento_attributes_field_store', FieldsInterface::STORE_ID, 'store', FieldsInterface::STORE_ID),
            FieldsInterface::STORE_ID,
            $installer->getTable('store'),
            FieldsInterface::STORE_ID,
            Table::ACTION_CASCADE
        )->setComment(
            'Custom Attributes Table'
        );
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
