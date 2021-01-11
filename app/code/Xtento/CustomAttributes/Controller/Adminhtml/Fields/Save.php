<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2020-05-10T13:51:01+00:00
 * File:          app/code/Xtento/CustomAttributes/Controller/Adminhtml/Fields/Save.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Controller\Adminhtml\Fields;

use Magento\Framework\Serialize\Serializer\Json;
use Xtento\CustomAttributes\Api\Data\FieldsInterface;
use Xtento\CustomAttributes\Model\Fields;
use Xtento\CustomAttributes\Model\FieldsRepository;
use Xtento\CustomAttributes\Model\FieldsFactory;
use Xtento\CustomAttributes\Helper\Data as DataHelper;
use Xtento\CustomAttributes\Model\Sources\InputType;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Request\DataPersistorInterface;
use Xtento\CustomAttributes\Model\Customer\Attribute\Backend\File;

class Save extends Action
{
    /**
     * @var PostDataProcessor
     */
    private $dataProcessor;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var FieldsRepository
     */
    private $fieldsRepository;

    /**
     * @var FieldsFactory
     */
    private $fieldsFactory;

    /**
     * @var DataHelper
     */
    private $dataHelper;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * Save constructor.
     *
     * @param Context $context
     * @param PostDataProcessor $dataProcessor
     * @param DataPersistorInterface $dataPersistor
     * @param FieldsRepository $fieldsRepository
     * @param FieldsFactory $fieldsFactory
     * @param DataHelper $dataHelper
     * @param Json $serializer
     */
    public function __construct(
        Context $context,
        PostDataProcessor $dataProcessor,
        DataPersistorInterface $dataPersistor,
        FieldsRepository $fieldsRepository,
        FieldsFactory $fieldsFactory,
        DataHelper $dataHelper,
        Json $serializer
    ) {
        $this->dataProcessor = $dataProcessor;
        $this->dataPersistor = $dataPersistor;
        $this->fieldsRepository = $fieldsRepository;
        $this->fieldsFactory = $fieldsFactory;
        $this->dataHelper = $dataHelper;
        $this->serializer = $serializer;

        parent::__construct($context);
    }

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $data = $this->dataHelper->implodeExplodeData($data);

        $serializedOptions = $this->getRequest()->getParam('serialized_options', '[]');

        $optionData = $this->xtUnserialize($serializedOptions);
        $data = array_replace_recursive(
            $data,
            $optionData
        );

        if (!isset($data['is_visible_on_front'])) {
            $data['is_visible_on_front'] = '';
        }

        if (!isset($data['customer_groups'])) {
            $data['customer_groups'] = '';
        }

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data) {
            $id = $this->getRequest()->getParam('entity_id');
            if ($id) {
                /** @var Fields $model */
                $model = $this->fieldsRepository->getById($id);
            } else {
                unset($data['entity_id']);
                /** @var Fields $model */
                $model = $this->fieldsFactory->create();
            }

            if ($model->getData(FieldsInterface::FRONTEND_INPUT) == InputType::BOOLEAN) {
                $data['frontend_option'] = $data['frontend_option_yesno'];
            }
            if ($model->getData(FieldsInterface::FRONTEND_INPUT) == InputType::SELECT) {
                $data['frontend_option'] = $data['frontend_option_radio_yesno'];
            }
            if ($model->getData(FieldsInterface::FRONTEND_INPUT) == InputType::DATE) {
                $data['frontend_option'] = $data['frontend_option_datetime_yesno'];
            }
            if ($model->getData(FieldsInterface::FRONTEND_INPUT) == InputType::MULTI_SELECT) {
                $data['frontend_option'] = $data['frontend_option_multiplecheckbox_yesno'];
            }
            if (in_array(FieldsInterface::FRONTEND_INPUT, $data) &&
                $data[FieldsInterface::FRONTEND_INPUT] === InputType::FILE ||
                $model->getData(FieldsInterface::FRONTEND_INPUT) === InputType::FILE
            ) {
                $data['backend_model'] = File::class;
            }

            $attributeCode = $this->getRequest()->getParam('attribute_code');
            if (strlen($attributeCode) > 0) {
                $validatorAttrCode =
                    new \Zend_Validate_Regex(
                        ['pattern' => '/^[a-z\x{600}-\x{6FF}][a-z\x{600}-\x{6FF}_0-9]{0,30}$/u']
                    );
                if (!$validatorAttrCode->isValid($attributeCode)) {
                    $this->messageManager->addErrorMessage(
                        __(
                            'Attribute code "%1" is invalid. Please use only lowercase letters (a-z), ' .
                            'numbers (0-9) or underscore(_) in this field, first character should be a letter.',
                            $attributeCode
                        )
                    );
                    return $resultRedirect
                        ->setPath('*/*/edit', [
                            'type' => $data['type_id'] ?? 'order_field'
                        ]);
                }
            }

            //$model->setData($data);
            $model->addData($data);
            if ($model->getData(FieldsInterface::FRONTEND_INPUT) === InputType::FILE) {
                $model->setData('backend_model', File::class);
            }
            $model->setData('update_time', time());
            //$this->dataPersistor->set('field_data', $data);

            try {
                $this->fieldsRepository->save($model);
                $this->messageManager->addSuccessMessage(__('Custom attribute saved.'));
                $this->dataPersistor->clear('field_data');
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect
                        ->setPath('*/*/edit', [
                            'id' => $model->getId(), '_current' => true,
                            'type' => $model->getAttributeTypeId()
                        ]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('There was a fatal error while saving the field: %1', $e->getMessage())
                );
                return $resultRedirect->setPath('*/*/index', ['type' => $model->getAttributeTypeId()]);
            }

            return $resultRedirect
                ->setPath('*/*/', ['id' => $this->getRequest()->getParam('id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(Index::ACTION);
    }

    /**
     * Provides form data from the serialized data.
     *
     * @param string $serializedData
     * @return array
     */
    protected function xtUnserialize($serializedData)
    {
        $encodedFields = $this->serializer->unserialize($serializedData);

        if (!is_array($encodedFields)) {
            return [];
        }

        $formData = [];
        foreach ($encodedFields as $item) {
            $decodedFieldData = [];
            parse_str($item, $decodedFieldData);
            $formData = array_replace_recursive($formData, $decodedFieldData);
        }

        return $formData;
    }
}
