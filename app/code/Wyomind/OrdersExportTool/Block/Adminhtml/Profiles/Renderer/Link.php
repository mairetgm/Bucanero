<?php

/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\OrdersExportTool\Block\Adminhtml\Profiles\Renderer;

/**
 * Render the link in the profile grid
 */
class Link extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    public function __construct(\Wyomind\OrdersExportTool\Helper\Delegate $wyomind, \Magento\Backend\Block\Context $context, array $data = [])
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        parent::__construct($context, $data);
    }
    /**
     * Render the column block
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $file = $this->_storageHelper->getFile($row);
        if (file_exists(BP . "/" . $file)) {
            return sprintf('<a href="%1$s?r=' . time() . '" target="_blank">%1$s</a>', $this->_storageHelper->getFileUrl($file));
        }
        return '-';
    }
}