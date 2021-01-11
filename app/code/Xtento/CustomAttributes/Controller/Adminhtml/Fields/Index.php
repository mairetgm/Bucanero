<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-01-12T17:30:34+00:00
 * File:          app/code/Xtento/CustomAttributes/Controller/Adminhtml/Fields/Index.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Controller\Adminhtml\Fields;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;
use Xtento\CustomAttributes\Helper\Module;

class Index extends Action
{
    const ACTION = 'Xtento_CustomAttributes::customattributes';

    /**
     * @var PageFactory
     */
    public $pageFactory;

    /**
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * @var Module
     */
    protected $moduleHelper;

    /**
     * Index constructor.
     *
     * @param Context $context
     * @param PageFactory $pageFactory
     * @param ResultFactory $resultFactory
     * @param Module $moduleHelper
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        ResultFactory $resultFactory,
        Module $moduleHelper
    ) {
        parent::__construct($context);
        $this->pageFactory = $pageFactory;
        $this->resultFactory = $resultFactory;
        $this->moduleHelper = $moduleHelper;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        // Check module status
        if (!$this->moduleHelper->confirmEnabled(true) || !$this->moduleHelper->isModuleEnabled()) {
            if ($this->getRequest()->getActionName() !== 'disabled') {
                $resultRedirect = $this->resultFactory->create(
                    \Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT
                );
                return $resultRedirect->setPath('*/fields/disabled');
            }
        }

        $resultPage = $this->pageFactory->create();
        $resultPage->setActiveMenu(self::ACTION);
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Custom Attributes'));

        return $resultPage;
    }

    /**
     * @return bool
     */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed(self::ACTION);
    }
}