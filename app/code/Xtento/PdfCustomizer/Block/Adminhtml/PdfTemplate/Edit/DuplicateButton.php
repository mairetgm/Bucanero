<?php

/**
 * Product:       Xtento_PdfCustomizer
 * ID:            TP2Z1gIjMryzjs+kTRDh6aWTwEp5w7T8imVFGAtG5js=
 * Last Modified: 2019-02-05T17:13:45+00:00
 * File:          app/code/Xtento/PdfCustomizer/Block/Adminhtml/PdfTemplate/Edit/DuplicateButton.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\PdfCustomizer\Block\Adminhtml\PdfTemplate\Edit;

use Xtento\PdfCustomizer\Controller\Adminhtml\Templates;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class DuplicateButton
 */
class DuplicateButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->_isAllowedAction(Templates::ADMIN_RESOURCE_SAVE)) {
            $data = [];
            if ($this->getTemplateId()) {
                $data = [
                    'label' => __('Duplicate Template'),
                    'class' => 'delete',
                    'on_click' => sprintf("location.href = '%s';", $this->getDuplicateUrl()),
                    'sort_order' => 20,
                ];
            }
        }
        return $data;
    }

    /**
     * @return string
     */
    public function getDuplicateUrl()
    {
        return $this->getUrl(
            '*/*/duplicate',
            ['template_id' => $this->getTemplateId()]
        );
    }
}
