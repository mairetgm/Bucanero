<?php

namespace Wyomind\OrderUpdater\Block\Adminhtml\Profiles;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    public $module = "OrderUpdater";
    protected $collectionFactory;
    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Backend\Helper\Data $backendHelper, \Wyomind\OrderUpdater\Model\ResourceModel\Profiles\CollectionFactory $collectionFactory, array $data = [])
    {
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }
    protected function _construct()
    {
        parent::_construct();
        $this->setId('OrderUpdaterGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
    }
    protected function _prepareCollection()
    {
        $collection = $this->collectionFactory->create();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    protected function _prepareColumns()
    {
        $this->addColumn('id', ['header' => __('ID'), 'align' => 'right', 'width' => '50px', 'index' => 'id', 'filter' => false]);
        $this->addColumn('name', ['header' => __('Profile name'), 'align' => 'left', 'index' => 'name']);
        $classHelper = "\\Wyomind\\" . $this->module . "\\Helper\\Data";
        $this->addColumn('file_system_type', array('header' => __('File location'), 'align' => 'left', 'index' => 'file_system_type', 'type' => 'options', 'options' => array(
            $classHelper::LOCATION_MAGENTO => __('Magento file system'),
            $classHelper::LOCATION_FTP => __('Ftp server'),
            $classHelper::LOCATION_URL => __('Url'),
            //                $classHelper::LOCATION_WEBSERVICE => __('Web service'),
            $classHelper::LOCATION_DROPBOX => __('Dropbox'),
        )));
        $this->addColumn('file_type', array('header' => __('File type'), 'align' => 'left', 'index' => 'file_type', 'type' => 'options', 'options' => array($classHelper::CSV => __('csv'), $classHelper::XML => __('xml'))));
        $this->addColumn('status', array('header' => __('Status'), 'align' => 'left', 'index' => 'status', 'renderer' => 'Wyomind\\' . $this->module . '\\Block\\Adminhtml\\Progress\\Status'));
        $this->addColumn('imported_at', ['header' => __('Last update'), 'align' => 'left', 'index' => 'imported_at', 'width' => '80px', 'type' => "datetime", 'renderer' => 'Wyomind\\' . $this->module . '\\Block\\Adminhtml\\Profiles\\Renderer\\Datetime']);
        $this->addColumn('action', ['header' => __('Action'), 'align' => 'left', 'index' => 'action', 'filter' => false, 'sortable' => false, 'width' => '120px', 'renderer' => 'Wyomind\\' . $this->module . '\\Block\\Adminhtml\\Profiles\\Renderer\\Action']);
        return parent::_prepareColumns();
    }
    public function getRowUrl($row)
    {
        return "";
    }
}