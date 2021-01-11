<?php

/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\OrdersExportTool\Block\Adminhtml\Profiles\Renderer;

class Storeviews extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var bool
     */
    protected $_skipAllStoresLabel = false;
    /**
     * @var bool
     */
    protected $_skipEmptyStoresLabel = false;
    public function __construct(\Wyomind\OrdersExportTool\Helper\Delegate $wyomind, \Magento\Backend\Block\Context $context, array $data = [])
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        parent::__construct($context, $data);
    }
    /**
     * Retrieve System Store model
     *
     * @return \Magento\Store\Model\System\Store
     */
    protected function _getStoreModel()
    {
        return $this->_systemStore;
    }
    /**
     * Retrieve 'show all stores label' flag
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    protected function _getShowAllStoresLabelFlag()
    {
        return $this->getColumn()->getData('skipAllStoresLabel') ? $this->getColumn()->getData('skipAllStoresLabel') : $this->_skipAllStoresLabel;
    }
    /**
     * Retrieve 'show empty stores label' flag
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    protected function _getShowEmptyStoresLabelFlag()
    {
        return $this->getColumn()->getData('skipEmptyStoresLabel') ? $this->getColumn()->getData('skipEmptyStoresLabel') : $this->_skipEmptyStoresLabel;
    }
    /**
     * Render row store views
     *
     * @param \Magento\Framework\DataObject $row
     * @return \Magento\Framework\Phrase|string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $out = '';
        $skipAllStoresLabel = $this->_getShowAllStoresLabelFlag();
        $skipEmptyStoresLabel = $this->_getShowEmptyStoresLabelFlag();
        $origStores = $row->getData($this->getColumn()->getIndex());
        if ($origStores === null && $row->getStoreName()) {
            $scopes = [];
            foreach (explode("
", $row->getStoreName()) as $k => $label) {
                $scopes[] = str_repeat('&nbsp;', $k * 3) . $label;
            }
            $out .= implode('<br/>', $scopes) . __(' [deleted]');
            return $out;
        }
        if (empty($origStores) && !$skipEmptyStoresLabel) {
            return '';
        }
        if (!is_array($origStores)) {
            $origStores = explode(",", $origStores);
        }
        if (empty($origStores)) {
            return '';
        } elseif (in_array(0, $origStores) && count($origStores) == 1 && !$skipAllStoresLabel) {
            return __('All Store Views');
        }
        $data = $this->_getStoreModel()->getStoresStructure(false, $origStores);
        foreach ($data as $website) {
            $out .= $website['label'] . '<br/>';
            foreach ($website['children'] as $group) {
                $out .= str_repeat('&nbsp;', 3) . $group['label'] . '<br/>';
                foreach ($group['children'] as $store) {
                    $out .= str_repeat('&nbsp;', 6) . $store['label'] . '<br/>';
                }
            }
        }
        return $out;
    }
}