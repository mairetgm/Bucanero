<?php

namespace Wyomind\OrderUpdater\Block\Adminhtml\Profiles\Edit\Tab;

/**
 * Class Main
 * @package Wyomind\OrderUpdater\Block\Adminhtml\Profiles\Edit\Tab
 */
class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var string
     */
    public $module = "OrderUpdater";
    /**
     * @var \Magento\Framework\Data\Form\FormKey|null
     */
    protected $formkey = null;
    public function __construct(\Wyomind\OrderUpdater\Helper\Delegate $wyomind, \Magento\Backend\Block\Template\Context $context, \Magento\Framework\Registry $registry, \Magento\Framework\Data\FormFactory $formFactory, array $data = [])
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        parent::__construct($context, $registry, $formFactory, $data);
        $this->formkey = $context->getFormKey();
    }
    /**
     * @return \Magento\Backend\Block\Widget\Form\Generic
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('profile');
        $form = $this->_formFactory->create();
        $class = "\\Wyomind\\" . $this->module . "\\Helper\\Data";
        $fieldset = $form->addFieldset($this->module . '_general_settings', ['legend' => __('Import Profile Settings')]);
        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }
        $fieldset->addField('name', 'text', ['name' => 'name', 'label' => __('Profile name'), 'required' => true]);
        /*
                $fieldset->addField(
                    'test', 'select', [
                        'name' => 'test',
                        'label' => __('Test mode'),
                        'required' => true,
                        'values' => [
                            '1' => __('Yes'),
                            '0' => __('No')
                        ],
                        'note' => __("When test mode is enabled, profile execution processes the file but takes no action on orders")
                    ]
                );
        */
        $fieldset->addField('line_filter', 'text', array('name' => 'line_filter', 'label' => __('Filter lines'), 'note' => __('<ul><li>Leave empty to import/preview all lines</li>' . '<li>Type the numbers of the lines you want to import<br/>' . '<i>e.g: 5,8  means that only lines number 5 and 8 will be imported</i></li>' . '<li>Use a dash (-) to denote a range of lines<br/>' . '<i>e.g: 8-10 means lines 8,9,10 will be imported</i></li>' . '<li>Use a plus (+) to import all lines from a line number<br/>' . '<i>e.g: 4+ means all lines from line 4 will be imported</i></li>' . '<li> Separate each line or range with a comma (,)<br/>' . '<i>e.g: 2,6-10,15+ means lines 2,6,7,8,9,10,15,16,17,... will be imported</i></li>' . '<li>Use regular expressions surrounded by a # before and after to indicate a particular group of identifiers to import<br/>' . '<i>e.g: #ABC-[0-9]+# all lines with an identifier matching the regular expression will be imported</i></li></ul>'), 'class' => 'updateOnChange'));
        $fieldset = $form->addFieldset($this->module . '_file_location', ['legend' => __('File Location')]);
        //        $session = Mage::getSingleton('core/session');
        //        $SID = $session->getEncryptedSessionId();
        $SID = $this->sessionManager->getSessionId();
        $formKey = $this->formkey->getFormKey();
        $fieldset->addField('file_system_type', 'select', ['name' => 'file_system_type', 'label' => __('File location'), 'class' => 'updateOnChange', 'required' => true, 'values' => [$class::LOCATION_MAGENTO => __('Magento file system'), $class::LOCATION_FTP => __('Ftp server'), $class::LOCATION_URL => __('Url'), $class::LOCATION_WEBSERVICE => __('Web service'), $class::LOCATION_DROPBOX => __('Dropbox')], 'note' => " <div id='uploader'>
                            <div id='holder' class='holder'>
                                <div> Drag files from your desktop <br>txt, csv or xml files only</div>
                                <div> " . __("Maximum size") . " " . $this->dataHelper->getMaxFileSize() . "</div>

                            </div> 

                            <progress id='uploadprogress' max='100' value='0'>0</progress>
                        </div>
                        <script>
                            require(['jquery'],function(\$){
                                \$('#file_system_type').on('change',function(){updateFileSystemType()});
                                \$(document).ready(function(){updateFileSystemType()});
                                function updateFileSystemType(){

                                     if(\$('#file_system_type').val()!=1){
                                         \$('#uploader').css('display','none')
                                     }
                                     else{
                                         \$('#uploader').css('display','block')
                                     }
                                 }
                             })
                             require(['wyomind_uploader_plugin'], function(uploader){
                                 var holder = document.getElementById('holder');
                            var progress = document.getElementById('uploadprogress');
                            var uploadUrl = '" . $this->getUrl('*/*/upload') . "?SID=" . $SID . "';
                            uploader.initialize(holder, progress,uploadUrl,'" . $formKey . "');
                             })
                            
                        </script>"]);
        /* FTP */
        $fieldset->addField('use_sftp', 'select', ['label' => __('Use SFTP'), 'name' => 'use_sftp', 'id' => 'use_sftp', 'class' => 'updateOnChange', 'required' => true, 'values' => ["1" => __('Yes'), '0' => __('No')]]);
        $fieldset->addField('ftp_active', 'select', ['label' => __('Use active mode'), 'name' => 'ftp_active', 'class' => 'updateOnChange', 'id' => 'ftp_active', 'required' => true, 'values' => ["1" => __('Yes'), '0' => __('No')]]);
        $fieldset->addField('ftp_host', 'text', ['label' => __('Host'), 'name' => 'ftp_host', 'class' => 'updateOnChange', 'id' => 'ftp_host']);
        $fieldset->addField('ftp_port', 'text', ['label' => __('Port'), 'name' => 'ftp_port', 'class' => 'updateOnChange', 'id' => 'ftp_port']);
        $fieldset->addField('ftp_login', 'text', ['label' => __('Login'), 'name' => 'ftp_login', 'class' => 'updateOnChange', 'id' => 'ftp_login']);
        $fieldset->addField('ftp_password', 'password', ['label' => __('Password'), 'name' => 'ftp_password', 'class' => 'updateOnChange', 'id' => 'ftp_password']);
        $fieldset->addField('ftp_dir', 'text', ['label' => __('Directory'), 'name' => 'ftp_dir', 'class' => 'updateOnChange', 'id' => 'ftp_dir', 'note' => __("<a style='margin:10px; display:block;' href='javascript:void(require([\"wyomind_OrderUpdater_ftp\"], function (ftp) { ftp.test(\"%1\")}))'>Test Connection</a>", $this->getUrl('*/*/ftp'))]);
        /* Common */
        $fieldset->addField('file_path', 'text', ['name' => 'file_path', 'class' => 'updateOnChange', 'label' => __('File Path'), 'required' => true, 'note' => __("- <b>Magento file system</b> : File path relative to Magento root folder</i><br/>") . __("- <b>FTP server</b> : File path relative to ftp user root folder<br/>") . __("- <b>URL</b> : Url of the file<br/>") . __("- <b>Web service</b> : Url of the web service<br/>") . __("- <b>Dropbox</b> : File path in the dropbox<br/>")]);
        /* Dropbox */
        $fieldset->addField('dropbox_token', 'text', ['name' => 'dropbox_token', 'class' => 'updateOnChange', 'label' => __('Access token'), 'required' => false, 'note' => __("You can generate your token from your Dropbox account https://www.dropbox.com/developers/apps")]);
        /* Web service */
        $fieldset->addField('webservice_params', 'textarea', ['label' => __('Parameters'), 'name' => 'webservice_params', 'class' => 'updateOnChange', 'id' => 'webservice_params']);
        $fieldset->addField('webservice_login', 'text', ['label' => __('Login'), 'class' => 'updateOnChange', 'name' => 'webservice_login', 'id' => 'webservice_login']);
        $fieldset->addField('webservice_password', 'password', ['label' => __('Password'), 'class' => 'updateOnChange', 'name' => 'webservice_password', 'id' => 'webservice_password']);
        $configUrl = $this->getUrl('adminhtml/system_config/edit', ['section' => strtolower($this->module)]);
        $fieldset = $form->addFieldset($this->module . '_order_identification', ['legend' => __('Order identification')]);
        $fieldset->addField('order_identification', 'select', ['label' => __('Order identification'), 'name' => 'order_identification', 'class' => 'updateOnChange', 'id' => '_order_identification', 'required' => true, 'values' => $this->dataHelper->getOrderIdentifiers(), 'note' => __("Choose the way your orders will be identified by in the imported file")]);
        $fileHeader = $this->dataHelper->getFileHeader();
        if (count($fileHeader) > 0) {
            $fieldset->addField('identifier_offset', 'select', ['label' => __('Identifier offset'), 'name' => 'identifier_offset', 'class' => 'updateOnChange', 'id' => '_identifier_offset', 'required' => true, 'values' => $fileHeader, 'note' => __("Select the field which contains the order identifier")]);
        } else {
            $fieldset->addField('identifier_offset', 'hidden', ['name' => 'identifier_offset', 'value' => '']);
        }
        $fieldset = $form->addFieldset($this->module . '_file_type', ['legend' => __('File Type')]);
        $fieldset->addField('file_type', 'select', ['name' => 'file_type', 'class' => 'updateOnChange', 'label' => __('File type'), 'required' => true, 'values' => [$class::CSV => __('CSV'), $class::XML => __('XML')]]);
        /* CSV */
        $fieldset->addField('field_delimiter', 'select', ['name' => 'field_delimiter', 'class' => 'updateOnChange', 'label' => __('Column separator'), 'values' => $this->dataHelper->getFieldDelimiters()]);
        $fieldset->addField('field_enclosure', 'select', ['name' => 'field_enclosure', 'class' => 'updateOnChange', 'label' => __('Text delimiter'), 'values' => $this->dataHelper->getFieldEnclosures()]);
        $fieldset->addField('has_header', 'select', array('name' => 'has_header', 'label' => __('The first line is a header'), 'options' => array(1 => 'Yes', 0 => 'No'), 'class' => 'updateOnChange'));
        /* XML */
        $fieldset->addField('xml_xpath_to_order', 'text', ['name' => 'xml_xpath_to_order', 'class' => 'updateOnChange', 'label' => __('Xpath to orders'), 'required' => true, 'note' => __("xPath where the order data is stored in the XML file, e.g.:/orders/order")]);
        $fieldset->addField('preserve_xml_column_mapping', 'select', ['label' => __('XML structure'), 'name' => 'preserve_xml_column_mapping', 'class' => 'updateOnChange', 'id' => 'preserve_xml_column_mapping', 'required' => true, 'values' => ['1' => __('Predefined structure'), '0' => __('Automatic detection')], 'note' => __("The automatic detection of the XML structure fits for simple files made of only one nesting level. ")]);
        $fieldset->addField('xml_column_mapping', 'textarea', ['label' => __('Predefined XML structure'), 'name' => 'xml_column_mapping', 'class' => "updateOnChange hidden", 'id' => 'xml_column_mapping', 'note' => __("The predefined XML structure must be a valid Json string made of a key/value list that define the column names and the Xpath associated to the column")]);
        $fieldset->addField('run', 'hidden', ['name' => 'run', 'class' => 'debug', 'value' => '']);
        $fieldset->addField('run_i', 'hidden', ['name' => 'run_i', 'value' => '']);
        $fieldset = $form->addFieldset($this->module . '_post_process', ['legend' => __('Post Process Action')]);
        $fieldset->addField('post_process_action', 'select', ['label' => __('Action on import file'), 'name' => 'post_process_action', 'id' => 'post_process_action', 'required' => true, 'values' => [$class::POST_PROCESS_ACTION_NOTHING => __('Do Nothing'), $class::POST_PROCESS_ACTION_DELETE => __('Delete the import file'), $class::POST_PROCESS_ACTION_MOVE => __('Move import file')]]);
        $fieldset->addField('post_process_move_folder', 'text', ['label' => __('Move to folder'), 'name' => 'post_process_move_folder', 'id' => 'post_process_move_folder', 'required' => true, 'note' => "File path relative to Magento root folder"]);
        $fieldset->addField('identifier', 'hidden', ['name' => 'identifier']);
        $fieldset->addField('mapping', 'hidden', ['name' => 'mapping']);
        $fieldset->addField('rules', 'hidden', ['name' => 'rules']);
        $block = $this->getLayout()->createBlock('Magento\\Backend\\Block\\Widget\\Form\\Element\\Dependence');
        $this->setChild('form_after', $block->addFieldMap('file_system_type', 'file_system_type')->addFieldMap('file_type', 'file_type')->addFieldMap('use_sftp', 'use_sftp')->addFieldMap('ftp_host', 'ftp_host')->addFieldMap('ftp_login', 'ftp_login')->addFieldMap('ftp_password', 'ftp_password')->addFieldMap('ftp_dir', 'ftp_dir')->addFieldMap('ftp_port', 'ftp_port')->addFieldMap('ftp_active', 'ftp_active')->addFieldDependence('ftp_host', 'file_system_type', $class::LOCATION_FTP)->addFieldDependence('use_sftp', 'file_system_type', $class::LOCATION_FTP)->addFieldDependence('ftp_login', 'file_system_type', $class::LOCATION_FTP)->addFieldDependence('ftp_password', 'file_system_type', $class::LOCATION_FTP)->addFieldDependence('ftp_active', 'file_system_type', $class::LOCATION_FTP)->addFieldDependence('ftp_dir', 'file_system_type', $class::LOCATION_FTP)->addFieldDependence('ftp_port', 'file_system_type', $class::LOCATION_FTP)->addFieldDependence('ftp_active', 'use_sftp', $class::NO)->addFieldMap('dropbox_token', 'dropbox_token')->addFieldDependence('dropbox_token', 'file_system_type', $class::LOCATION_DROPBOX)->addFieldMap('webservice_params', 'webservice_params')->addFieldMap('webservice_login', 'webservice_login')->addFieldMap('webservice_password', 'webservice_password')->addFieldDependence('webservice_params', 'file_system_type', $class::LOCATION_WEBSERVICE)->addFieldDependence('webservice_login', 'file_system_type', $class::LOCATION_WEBSERVICE)->addFieldDependence('webservice_password', 'file_system_type', $class::LOCATION_WEBSERVICE)->addFieldMap('use_custom_rules', 'use_custom_rules')->addFieldMap('custom_rules', 'custom_rules')->addFieldDependence('custom_rules', 'use_custom_rules', $class::YES)->addFieldMap('field_delimiter', 'field_delimiter')->addFieldMap('has_header', 'has_header')->addFieldMap('field_enclosure', 'field_enclosure')->addFieldMap('is_magento_export', 'is_magento_export')->addFieldDependence('field_enclosure', 'file_type', $class::CSV)->addFieldDependence('field_delimiter', 'file_type', $class::CSV)->addFieldDependence('has_header', 'file_type', $class::CSV)->addFieldDependence('is_magento_export', 'file_type', $class::CSV)->addFieldMap('xml_xpath_to_order', 'xml_xpath_to_order')->addFieldMap('xml_column_mapping', 'xml_column_mapping')->addFieldMap('preserve_xml_column_mapping', 'preserve_xml_column_mapping')->addFieldDependence('xml_xpath_to_order', 'file_type', $class::XML)->addFieldDependence('preserve_xml_column_mapping', 'file_type', $class::XML)->addFieldDependence('xml_column_mapping', 'file_type', $class::XML)->addFieldDependence('xml_column_mapping', 'preserve_xml_column_mapping', 1)->addFieldMap('post_process_action', 'post_process_action')->addFieldMap('post_process_move_folder', 'post_process_move_folder')->addFieldDependence('post_process_action', 'file_system_type', $class::LOCATION_MAGENTO)->addFieldDependence('post_process_move_folder', 'file_system_type', $class::LOCATION_MAGENTO)->addFieldDependence('post_process_move_folder', 'post_process_action', $class::POST_PROCESS_ACTION_MOVE));
        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }
    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getTabLabel()
    {
        return __('Settings');
    }
    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getTabTitle()
    {
        return __('Settings');
    }
    /**
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }
    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }
}