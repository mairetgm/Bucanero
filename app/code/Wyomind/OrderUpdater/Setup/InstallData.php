<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrderUpdater\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Install Data needed for Simple Google Shopping
 */
class InstallData implements InstallDataInterface
{
    /**
     * @version 1.0.0
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $sampleTemplates = [
            [
                'name' => 'Preparation status csv',
                'imported_at' => '',
                'order_identification' => 'entity_id',
                'identifier_offset' => '1',
                'mapping' => NULL,
                'rules' => '{"0":{"disabled":false,"name":"Add comment","conditions":{},"actions":{"0":{"atitle":"","acolor":"","action":"add_comment","action-option-1":"_custom_","action-option-1-custom":"","action-option-1-script":"<?php__LINE_BREAK__ /* Generate comment */__LINE_BREAK__$comment = \'\';__LINE_BREAK__if($cell[2] == \'Received\') $comment = \'Your order has been received by the warehouse\';__LINE_BREAK__elseif($cell[2] == \'Shipped\') $comment = \'Your order has been shipped\';__LINE_BREAK__elseif($cell[2] == \'Picking\') $comment = \'Your order is being picked\';__LINE_BREAK__elseif($cell[2] == \'Restock\') $comment = \'Your order is waiting for a restock, it will be prepared on \' . $cell[5];__LINE_BREAK__return $comment;__LINE_BREAK__","action-option-2":"0","action-option-2-custom":"","action-option-2-script":"","action-option-3":"1","action-option-3-custom":"","action-option-3-script":""}}},"1":{"disabled":false,"name":"Create shipping","conditions":{"0":{"operand":"","condition":"2","condition-operand":"eq","value":"Shipped"}},"actions":{"0":{"atitle":"","acolor":"","action":"ship","action-option-1":"dhl","action-option-1-custom":"","action-option-1-script":"","action-option-2":"_file_4","action-option-2-custom":"","action-option-2-script":"","action-option-3":"1","action-option-3-custom":"","action-option-3-script":"","action-option-4":"default","action-option-4-custom":"","action-option-4-script":""}}}}',
                'cron_settings' => '{"days":[],"hours":[]}',
                'file_system_type' => '3',
                'use_sftp' => '0',
                'ftp_host' => NULL,
                'ftp_port' => NULL,
                'ftp_password' => NULL,
                'ftp_login' => NULL,
                'ftp_active' => '0',
                'ftp_dir' => NULL,
                'file_type' => '1',
                'file_path' => 'http://sample.wyomind.com/massorderupdate/E_OrderStatus.csv',
                'field_delimiter' => '	',
                'field_enclosure' => 'non',
                'has_header' => '1',
                'line_filter' => '',
                'xml_xpath_to_order' => '',
                'xml_column_mapping' => NULL,
                'post_process_action' => '0',
                'post_process_move_folder' => NULL,
                'last_import_report' => '',
                'dropbox_token' => NULL,
                'preserve_xml_column_mapping' => ''
            ],
            [
                'name' => 'Create shipping from csv',
                'imported_at' => '',
                'order_identification' => 'entity_id',
                'identifier_offset' => '1',
                'mapping' => NULL,
                'rules' => '{"0":{"disabled":false,"name":"Ship order","conditions":{"0":{"operand":"","condition":"order.getState","condition-operand":"eq","value":"processing"},"1":{"operand":"and","condition":"3","condition-operand":"eq","value":"2"},"2":{"operand":"and","condition":"5","condition-operand":"notnull","value":""}},"actions":{"0":{"atitle":"","acolor":"","action":"ship","action-option-1":"_file_4","action-option-1-custom":"","action-option-1-script":"<?php__LINE_BREAK__ /* Generate carrier code */__LINE_BREAK__if ($self == \'WEB\') return \'dhl\';__LINE_BREAK__elseif($self == \'WEB\') return \'ups\';__LINE_BREAK__else return \'fedex\';__LINE_BREAK__","action-option-2":"_file_5","action-option-2-custom":"","action-option-2-script":"","action-option-3":"1","action-option-3-custom":"","action-option-3-script":"","action-option-4":"default","action-option-4-custom":"","action-option-4-script":""}}}}',
                'cron_settings' => '{"days":[],"hours":[]}',
                'file_system_type' => '3',
                'use_sftp' => '0',
                'ftp_host' => NULL,
                'ftp_port' => NULL,
                'ftp_password' => NULL,
                'ftp_login' => NULL,
                'ftp_active' => '0',
                'ftp_dir' => NULL,
                'file_type' => '1',
                'file_path' => 'http://sample.wyomind.com/massorderupdate/FAS_STATUS_20200221_1030_09033.CSV',
                'field_delimiter' => '	',
                'field_enclosure' => 'non',
                'has_header' => '1',
                'line_filter' => '',
                'xml_xpath_to_order' => '',
                'xml_column_mapping' => NULL,
                'post_process_action' => '0',
                'post_process_move_folder' => NULL,
                'last_import_report' => '',
                'dropbox_token' => NULL,
                'preserve_xml_column_mapping' => ''
            ],
            [
                'name' => 'Create shipping from XML',
                'imported_at' => '',
                'order_identification' => 'entity_id',
                'identifier_offset' => '0',
                'mapping' => NULL,
                'rules' => '{"0":{"disabled":false,"name":"Create shipment","conditions":{},"actions":{"0":{"atitle":"","acolor":"","action":"ship","action-option-1":"_file_2","action-option-1-custom":"","action-option-1-script":"","action-option-2":"_file_1","action-option-2-custom":"","action-option-2-script":"","action-option-3":"1","action-option-3-custom":"","action-option-3-script":"","action-option-4":"default","action-option-4-custom":"","action-option-4-script":""}}}}',
                'cron_settings' => '{"days":[],"hours":[]}',
                'file_system_type' => '3',
                'use_sftp' => '0',
                'ftp_host' => NULL,
                'ftp_port' => NULL,
                'ftp_password' => NULL,
                'ftp_login' => NULL,
                'ftp_active' => '0',
                'ftp_dir' => NULL,
                'file_type' => '2',
                'file_path' => 'http://sample.wyomind.com/massorderupdate/EXPB2CEPK20200220164001.xml',
                'field_delimiter' => ';',
                'field_enclosure' => 'non',
                'has_header' => '0',
                'line_filter' => '',
                'xml_xpath_to_order' => '/orders/order',
                'xml_column_mapping' => '{ "incrementid":"CustomerOrderId", "tracking_number":"order-shipment/OrderTrackingNumber", "carrier":"order-shipment/OrderCarrier"}',
                'post_process_action' => '0',
                'post_process_move_folder' => NULL,
                'last_import_report' => '',
                'dropbox_token' => NULL,
                'preserve_xml_column_mapping' => '1'
            ]
        ];

        foreach ($sampleTemplates as $sampleTemplate) {
            $installer->getConnection()->insert($installer->getTable("orderupdater_profiles"), $sampleTemplate);
        }
        $installer->endSetup();
    }
}
