<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-01-12T17:30:34+00:00
 * File:          app/code/Xtento/CustomAttributes/Controller/Adminhtml/Fields/Delete.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */
namespace Xtento\CustomAttributes\Controller\Adminhtml\Fields;

use Xtento\CustomAttributes\Api\FieldsRepositoryInterface;
use Xtento\CustomAttributes\Model\Fields;
use Xtento\CustomAttributes\Model\FieldsFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Delete extends Action
{
    const ACTION = 'Xtento_CustomAttributes::customattributes';

    public $resultFactory;

    private $fieldsRepository;

    private $fieldsFactory;

    public function __construct(
        Context $context,
        PageFactory $resultFactory,
        FieldsRepositoryInterface $fieldsRepository,
        FieldsFactory $fieldsFactory
    ) {
        $this->resultFactory    = $resultFactory;
        $this->fieldsRepository = $fieldsRepository;
        $this->fieldsFactory    = $fieldsFactory;
        parent::__construct($context);
    }

    public function execute()
    {

        $id = $this->getRequest()->getParam('entity_id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                /** @var Fields $field */
                $this->fieldsRepository->saveAndDeleteById($id);
                $this->messageManager->addSuccessMessage(__('The attribute has been deleted.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/');
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find an attribute to delete.'));
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(self::ACTION);
    }
}