<?php

/**
 * Product:       Xtento_PdfCustomizer
 * ID:            TP2Z1gIjMryzjs+kTRDh6aWTwEp5w7T8imVFGAtG5js=
 * Last Modified: 2019-02-19T17:03:40+00:00
 * File:          app/code/Xtento/PdfCustomizer/Controller/Adminhtml/Templates/MassAction.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\PdfCustomizer\Controller\Adminhtml\Templates;

use Xtento\PdfCustomizer\Controller\Adminhtml\Templates;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Xtento\PdfCustomizer\Model\ResourceModel\PdfTemplate\CollectionFactory as templateCollectionFactory;

abstract class MassAction extends Action
{
    /**
     * @var Filter
     */
    public $filter;

    /**
     * @var CollectionFactory
     */
    public $templateCollectionFactory;
    
    /**
     * @param Context $context
     * @param Filter $filter
     * @param templateCollectionFactory $templateCollectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        templateCollectionFactory $templateCollectionFactory
    ) {
        $this->filter = $filter;
        $this->templateCollectionFactory = $templateCollectionFactory;
        parent::__construct($context);
    }

    /**
     * Check the permission to run it
     *
     * @return boolean
     */
    //@codingStandardsIgnoreLine
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(
            Templates::ADMIN_RESOURCE_SAVE
        );
    }
}
