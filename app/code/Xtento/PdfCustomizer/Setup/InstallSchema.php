<?php

/**
 * Product:       Xtento_PdfCustomizer
 * ID:            TP2Z1gIjMryzjs+kTRDh6aWTwEp5w7T8imVFGAtG5js=
 * Last Modified: 2019-02-05T17:13:45+00:00
 * File:          app/code/Xtento/PdfCustomizer/Setup/InstallSchema.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\PdfCustomizer\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
//@codingStandardsIgnoreFile
/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    const PDF_TABLE = 'xtento_pdf_templates';

    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $pdfTemplateTable = $setup->getTable(self::PDF_TABLE);

        if (!$setup->tableExists($pdfTemplateTable)) {
            $this->installBefore($setup);
        }

        $setup->getConnection()->addColumn(
            $pdfTemplateTable,
            'customer_group_id',
            [
                'type' => Table::TYPE_SMALLINT,
                'unsigned' => true,
                'nullable' => false,
                'default' => '0',
                'comment' => 'Customer group id the template is for'
            ]
        );

        $setup->endSetup();
    }

    /**
     * @param $installer
     */
    private function installBefore($installer)
    {
        $table = $installer->getConnection()
            ->newTable($installer->getTable('xtento_pdf_templates'))
            ->addColumn(
                'template_id',
                Table::TYPE_SMALLINT,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Template Id'
            )
            ->addColumn('is_active', Table::TYPE_SMALLINT, null, ['nullable' => false, 'default' => '1'], 'Template active?')
            ->addColumn('template_name', Table::TYPE_TEXT, 100, ['nullable' => false], 'Template name')
            ->addColumn('template_description', Table::TYPE_TEXT, 500, ['nullable' => false], 'Template description')
            ->addColumn('template_default', Table::TYPE_BOOLEAN, null, ['nullable' => false, 'default' => '0'], 'Template default')
            ->addColumn('template_type', Table::TYPE_SMALLINT, null, ['nullable' => false, 'default' => '1'], 'Template type')
            ->addColumn('template_html', Table::TYPE_TEXT, '2M', [], 'Template HTML')
            ->addColumn('template_css', Table::TYPE_TEXT, '1M', ['nullable' => false], 'Template css')
            ->addColumn('template_file_name', Table::TYPE_TEXT, 100, ['nullable' => false], 'Template file name')
            ->addColumn('template_paper_form', Table::TYPE_SMALLINT, null, ['nullable' => false, 'default' => '0'], 'Paper format')
            ->addColumn('template_custom_form', Table::TYPE_SMALLINT, null, ['nullable' => false, 'default' => '0'], 'Paper custom format')
            ->addColumn('template_custom_h', Table::TYPE_DECIMAL, null, ['nullable' => false, 'default' => '1'], 'Custom template height')
            ->addColumn('template_custom_w', Table::TYPE_DECIMAL, null, ['nullable' => false, 'default' => '1'], 'Custom template width')
            ->addColumn('template_custom_t', Table::TYPE_DECIMAL, null, ['nullable' => false, 'default' => '1'], 'Custom template top margin')
            ->addColumn('template_custom_b', Table::TYPE_DECIMAL, null, ['nullable' => false, 'default' => '1'], 'Custom template bottom margin')
            ->addColumn('template_custom_l', Table::TYPE_DECIMAL, null, ['nullable' => false, 'default' => '1'], 'Custom template left margin')
            ->addColumn('template_custom_r', Table::TYPE_DECIMAL, null, ['nullable' => false, 'default' => '1'], 'Custom template right margin')
            ->addColumn('template_paper_ori', Table::TYPE_SMALLINT, null, ['nullable' => false, 'default' => '0'], 'Paper orientation')
            ->addColumn('creation_time', Table::TYPE_TIMESTAMP, null, ['nullable' => false], 'Creation Time')
            ->addColumn('update_time', Table::TYPE_TIMESTAMP, null, ['nullable' => false], 'Update Time')
            ->addIndex($installer->getIdxName('template_id', ['template_id']), ['template_id'])
            ->setComment('XTENTO PDF Customizer Templates');

        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()->newTable(
            $installer->getTable('xtento_pdf_store')
        )->addColumn(
            'template_id',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'primary' => true],
            'Template ID'
        )->addColumn(
            'store_id',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Store ID'
        )->addIndex(
            $installer->getIdxName('xtento_pdf_store', ['store_id']),
            ['store_id']
        )->addForeignKey(
            $installer->getFkName('xtento_pdf_store', 'template_id', 'xtento_pdf_templates', 'template_id'),
            'template_id',
            $installer->getTable('xtento_pdf_templates'),
            'template_id',
            Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('xtento_pdf_store', 'store_id', 'store', 'store_id'),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            Table::ACTION_CASCADE
        )->setComment(
            'XTENTO PDF Generator to store linkage table'
        );
        $installer->getConnection()->createTable($table);
    }

}
