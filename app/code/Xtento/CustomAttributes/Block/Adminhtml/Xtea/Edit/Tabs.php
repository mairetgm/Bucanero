<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-01-12T17:30:34+00:00
 * File:          app/code/Xtento/CustomAttributes/Block/Adminhtml/Xtea/Edit/Tabs.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Block\Adminhtml\Xtea\Edit;

use Magento\Backend\Block\Widget\Tabs as WidgetTabs;

/**
 * Admin page left menu
 */
class Tabs extends WidgetTabs
{
    /**
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->setId('xtea_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Field Configuration'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab(
            'general',
            [
                'label' => __('Attribute Settings'),
                'title' => __('Attribute Settings'),
                'content' => $this->getChildHtml('general'),
                'active' => true
            ]
        );

        $this->addTab(
            'xtea_edit_tab_frontend',
            [
                'label' => __('Display Settings'),
                'title' => __('Display Settings'),
                'content' => $this->getChildHtml('xtea_edit_tab_frontend')
            ]
        );

        $this->addTab(
            'xtea_edit_tab_store',
            [
                'label' => __('Store Views'),
                'title' => __('Store Views'),
                'content' => $this->getChildHtml('xtea_edit_tab_store')
            ]
        );

        $this->addTab(
            'xtea_edit_tab_labels',
            [
                'label' => __('Labels / Translation'),
                'title' => __('Labels / Translation'),
                'content' => $this->getChildHtml('xtea_edit_tab_labels')
            ]
        );

        return parent::_beforeToHtml();
    }
}
