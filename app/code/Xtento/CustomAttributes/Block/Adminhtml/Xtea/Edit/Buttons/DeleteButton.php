<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-01-12T17:30:34+00:00
 * File:          app/code/Xtento/CustomAttributes/Block/Adminhtml/Xtea/Edit/Buttons/DeleteButton.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Block\Adminhtml\Xtea\Edit\Buttons;

use Xtento\CustomAttributes\Controller\Adminhtml\Fields\Index;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class DuplicateButton
 */
class DeleteButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->_isAllowedAction(Index::ACTION)) {
            $data = [];
            if ($this->getEntityId()) {
                $data = [
                    'label' => __('Delete'),
                    'class' => 'delete',
                    'on_click' => sprintf("if (window.confirm('".__('Are you sure? The attribute as well as values stored in the database will be deleted.')."')) location.href = '%s';", $this->getDeleteUrl()),
                    'sort_order' => 20,
                ];
            }
        }
        return $data;
    }

    /**
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->getUrl(
            '*/*/delete',
            ['entity_id' => $this->getEntityId()]
        );
    }
}
