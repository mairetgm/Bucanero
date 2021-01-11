<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-01-12T17:30:34+00:00
 * File:          app/code/Xtento/CustomAttributes/Controller/Adminhtml/Fields/Edit.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */
namespace Xtento\CustomAttributes\Controller\Adminhtml\Fields;

use Magento\Framework\App\Request\DataPersistorInterface;
use Xtento\CustomAttributes\Api\FieldsRepositoryInterface;
use Xtento\CustomAttributes\Model\CustomAttributes;
use Xtento\CustomAttributes\Model\Fields;
use Xtento\CustomAttributes\Model\FieldsFactory;
use Magento\Customer\Model\Customer;
use Magento\Eav\Model\AttributeRepository;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Backend\Model\Session;
use Magento\Framework\View\Result\PageFactory;

class Edit extends Action
{
    const ACTION = 'Xtento_CustomAttributes::customattributes';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var FieldsRepositoryInterface
     */
    private $fieldsRepository;

    /**
     * @var FieldsFactory
     */
    private $fieldsFactory;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var AttributeRepository
     */
    private $attributeRepository;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * Edit constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param FieldsRepositoryInterface $fieldsRepository
     * @param FieldsFactory $fieldsFactory
     * @param Registry $registry
     * @param AttributeRepository $attributeRepository
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        FieldsRepositoryInterface $fieldsRepository,
        FieldsFactory $fieldsFactory,
        Registry $registry,
        AttributeRepository $attributeRepository,
        DataPersistorInterface $dataPersistor
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->fieldsRepository = $fieldsRepository;
        $this->fieldsFactory = $fieldsFactory;
        $this->registry = $registry;
        $this->attributeRepository = $attributeRepository;
        $this->session = $context->getSession();
        $this->dataPersistor = $dataPersistor;
        parent::__construct($context);
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');

        if ($id) {
            /** @var Fields $model */
            $model = $this->fieldsRepository->getById($id);
            $model->setData('type_id_visible', $model->getData('type_id'));

            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This field no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultPageFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        } else {
            $model = $this->fieldsFactory->create();
            $model->setData('type_id', $this->getRequest()->getParam('type'));
            $model->setData('type_id_visible', $this->getRequest()->getParam('type'));
        }

        /** @var Session $data */
        $data = $this->dataPersistor->get('field_data');
        $this->dataPersistor->clear('field_data');

        if (!empty($data)) {
            $model->setData($data);
        }

        $this->registry->register('fields_data', $model);

        if ($model->getId()) {
            $this->getAttributeData();
        }

        $resultPage = $this->initAction();
        $resultPage->addBreadcrumb(
            $id ? __('Edit Attribute') : __('New Attribute'),
            $id ? __('Edit Attribute') : __('New Attribute')
        );

        $resultPage->getConfig()->getTitle()->prepend(__('Edit Attribute'));
        $resultPage->getConfig()->getTitle()
            ->prepend(
                $model->getData('attribute_id') ? __('Edit Attribute') : __('New Attribute')
            );

        return $resultPage;
    }

    private function initAction()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        return $resultPage;
    }

    private function getAttributeData()
    {
        /** @var Fields $model */
        $model = $this->registry->registry('fields_data');

        $attributeCode = $model->getAttributeCode();
        $attributeType = $model->getAttributeTypeId();

        if ($attributeType === CustomAttributes::ORDER_ENTITY) {
            $attributeType = Customer::ENTITY;
        }

        $attribute = $this->attributeRepository->get($attributeType, $attributeCode);

        $this->registry->register('custom_attribute_data', $attribute);

        return $this;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(self::ACTION);
    }
}
