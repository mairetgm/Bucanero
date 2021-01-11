<?php

/**
 * Product:       Xtento_PdfCustomizer
 * ID:            TP2Z1gIjMryzjs+kTRDh6aWTwEp5w7T8imVFGAtG5js=
 * Last Modified: 2019-02-05T17:13:45+00:00
 * File:          app/code/Xtento/PdfCustomizer/Setup/UpgradeSchema.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\PdfCustomizer\Setup;

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

        if (version_compare($context->getVersion(), '2.2.4', '<')) {
            $this->addSource($setup);
        }

        if (version_compare($context->getVersion(), '2.2.3', '<')) {
            $this->addAttachments($setup);
        }

        if (version_compare($context->getVersion(), '2.3.2', '<')) {
            $this->addSaveFileOption($setup);
        }

        if (version_compare($context->getVersion(), '2.4.5', '<')) {
            $this->addAttachmentPdfUpload($setup);
        }

        $setup->endSetup();
    }

    public function addSource(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable(InstallSchema::PDF_TABLE),
            'source',
            [
                'type' => Table::TYPE_TEXT,
                'unsigned' => true,
                'nullable' => false,
                'comment' => 'Source ID'
            ]
        );
    }

    public function addAttachments(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable(InstallSchema::PDF_TABLE),
            'attachments',
            [
                'type' => Table::TYPE_TEXT,
                'unsigned' => true,
                'nullable' => false,
                'comment' => 'Attachments comma separated'
            ]
        );
    }

    public function addSaveFileOption(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable(InstallSchema::PDF_TABLE),
            'save_pdf_in_backup_folder',
            [
                'type' => Table::TYPE_BOOLEAN,
                'nullable' => false,
                'default' => true,
                'comment' => 'Save backup PDFs in folder'
            ]
        );
        $setup->getConnection()->addColumn(
            $setup->getTable(InstallSchema::PDF_TABLE),
            'read_pdf_from_backup_folder',
            [
                'type' => Table::TYPE_BOOLEAN,
                'nullable' => false,
                'default' => true,
                'comment' => 'If already generated, read PDF from backup folder'
            ]
        );
    }

    public function addAttachmentPdfUpload(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable(InstallSchema::PDF_TABLE),
            'attachment_pdf_file',
            [
                'type' => Table::TYPE_TEXT,
                'unsigned' => true,
                'nullable' => false,
                'comment' => 'Attachment PDF File Name'
            ]
        );
    }
}
