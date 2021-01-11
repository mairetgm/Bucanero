<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrderUpdater\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Install schema for Simple Google Shopping
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @version 1.0.0
     * @param SchemaSetupInterface   $setup
     * @param ModuleContextInterface $context
     */
    public function install(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;
        $installer->startSetup();

        $installer->getConnection()->dropTable($installer->getTable('orderupdater_profiles')); // drop if exists

        $orderUpdater = $installer->getConnection()
            ->newTable($installer->getTable('orderupdater_profiles'))
            // usual columns
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [ 'identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true ],
                'ID'
            )
            ->addColumn(
                'name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                150,
                [ 'nullable' => false],
                'Name'
            )
            ->addColumn(
                'imported_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                [ 'nullable' => false],
                'Last update date'
            )
            ->addColumn(
                'order_identification',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                [ 'nullable' => true],
                'Order identification'
            )
            ->addColumn(
                'identifier_offset',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                [ 'nullable' => true],
                'Identifier offset'
            )
            ->addColumn(
                'mapping',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                [ 'nullable' => true],
                'Columns mapping'
            )
            ->addColumn(
                'rules',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                [ 'nullable' => true],
                'Rules'
            )
            ->addColumn(
                'cron_settings',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                900,
                [],
                'Cron Schedule'
            )
            
            // File location
            ->addColumn(
                'file_system_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                1,
                ['nullable'=>false,'default'=>0],
                'File System Type (local,ftp,url)'
            )
                
            // FTP File System
            ->addColumn(
                'use_sftp',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                1,
                ["default"=>"0"],
                'Profile Use Sftp ?'
            )
            ->addColumn(
                'ftp_host',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                300,
                [],
                'Profile Ftp Host'
            )
            ->addColumn(
                'ftp_port',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                5,
                [],
                'Profile Ftp Port'
            )
            ->addColumn(
                'ftp_password',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                300,
                [],
                'Profile Ftp Password'
            )
            ->addColumn(
                'ftp_login',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                300,
                [],
                'Profile Ftp Login'
            )
            ->addColumn(
                'ftp_active',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                1,
                ["default"=>"0"],
                'Profile Ftp Active Mode'
            )
            ->addColumn(
                'ftp_dir',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                300,
                [],
                'Profile Ftp Dir'
            )

            // dropbox
            ->addColumn(
                'dropbox_token',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                300,
                [],
                'Dropbox Token'
            )

                
            // common
                
            ->addColumn(
                'file_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                1,
                ['nullable'=>false,'default'=>0],
                'File Type (csv,xml)'
            )
            ->addColumn(
                'file_path',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                900,
                ['nullable'=>false],
                'File Path'
            )
                
            // CSV
                
            ->addColumn(
                'field_delimiter',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                3,
                [],
                'CSV Field Delimiter'
            )
            ->addColumn(
                'field_enclosure',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                3,
                [],
                'CSV Field enclosure'
            )
            ->addColumn(
                'has_header',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                1,
                [ 'nullable' => false, 'default'=> 0],
                'CSV file has a header'
            )

            ->addColumn(
                'line_filter',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                300,
                ['nullable' => false, 'default' => ''],
                'Line filter'
            )

            // XML
            ->addColumn(
                'xml_xpath_to_order',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                300,
                [ 'nullable' => false],
                'XML XPath To The Order'
            )

            ->addColumn(
                'preserve_xml_column_mapping',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                1,
                [ 'nullable' => false],
                'Preserve XML column mapping'
            )

            ->addColumn(
                'xml_column_mapping',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                [ 'nullable' => true],
                'XML column mapping'
            )

            ->addColumn(
                'post_process_action',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                1,
                [ 'nullable' => false, 'default'=> 0],
                'Post process action'
            )

            ->addColumn(
                'post_process_move_folder',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                [ 'nullable' => true],
                'Folder to move to after process'
            )

            //            // custom rules
//            ->addColumn(
//                'use_custom_rules',
//                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
//                1,
//                [ 'nullable' => false, 'default'=> 0],
//                'Does The CSV Import Use Custom Rules'
//            )
//            ->addColumn(
//                'custom_rules',
//                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
//                null,
//                [ 'nullable' => true],
//                'CSV Custom Rules'
//            )
//
            // last update report
            ->addColumn(
                'last_import_report',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                [ 'nullable' => true],
                'Last update report'
            )
//            // indexes
//            ->addIndex(
//                $installer->getIdxName('orderupdater_profiles', ['id']),
//                ['id']
//            )
//            ->setComment('OrderUpdater profiles table')
            ;

        $installer->getConnection()->createTable($orderUpdater);
        
        $installer->endSetup();
    }
}
