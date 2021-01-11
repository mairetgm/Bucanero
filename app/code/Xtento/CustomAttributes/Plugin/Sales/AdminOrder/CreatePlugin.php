<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2020-05-23T19:14:07+00:00
 * File:          app/code/Xtento/CustomAttributes/Plugin/Sales/AdminOrder/CreatePlugin.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Plugin\Sales\AdminOrder;

use Xtento\CustomAttributes\Model\FileUpload;
use Xtento\CustomAttributes\Helper\Data as DataHelper;
use Xtento\CustomAttributes\Api\Data\FieldsInterface;
use Xtento\CustomAttributes\Block\Adminhtml\Sales\Order\Create\OrderAttributes;
use Xtento\CustomAttributes\Model\Fields;
use Xtento\CustomAttributes\Model\Sales\AdminOrder\Create;
use Xtento\CustomAttributes\Model\Sources\InputType;
use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Attribute;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Quote\Model\Quote;

class CreatePlugin
{

    /**
     * @var DataHelper
     */
    private $dataHelper;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var FileUpload
     */
    private $fileUpload;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * CreatePlugin constructor.
     *
     * @param DataHelper $dataHelper
     * @param FilterBuilder $filterBuilder
     * @param FileUpload $fileUpload
     * @param DataObjectFactory $dataObjectFactory
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        DataHelper $dataHelper,
        FilterBuilder $filterBuilder,
        FileUpload $fileUpload,
        DataObjectFactory $dataObjectFactory,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->dataHelper = $dataHelper;
        $this->filterBuilder = $filterBuilder;
        $this->fileUpload = $fileUpload;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->request = $request;
    }

    /**
     * @param Create $subject
     * @param $data
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function beforeImportPostData(Create $subject, $data)
    {
        $fieldCodes = $this->fields();
        $addressFieldsUpload = $this->addressUploadFields();

        $processedFiles = $this->request->getFiles();
        //var_dump($processedFiles);
        if ($this->request->getFiles('order')) {
            foreach ($this->request->getFiles('order') as $key => $subFiles) {
                foreach ($subFiles as $subKey => $subValue) {
                    if ($subKey === 'billing_address' || $subKey === 'shipping_address') {
                        continue;
                    }
                    $processedFiles->set('order', array_merge($processedFiles->get('order'), [$key => $subFiles]));
                    //$processedFiles['order'][$key][$subKey] = $subValue;
                }
            }
        }
        //var_dump($processedFiles);
        //die();
        $this->request->setFiles($processedFiles);

        if (isset($data['account'])) {
            foreach ($data['account'] as $accountField => $value) {
                if (is_array($value)) {
                    $value = implode(',', $value);
                    $data['account'][$accountField] = $value;
                }
                if (in_array($accountField, $fieldCodes)) {
                    $data['billing_address'][$accountField] = $value;
                }
                $data['billing_address']['email'] = $data['account']['email'];
            }
        }

        if (isset($data['billing_address'])) {
            $billingAddressValues = $data['billing_address'];
            foreach ($billingAddressValues as $accountField => $value) {
                if (is_array($value) && isset($value['value']) && in_array($accountField, $addressFieldsUpload)) {
                    $data['billing_address'][$accountField] = $value['value'];
                }
            }
        }

        if (isset($data['shipping_address'])) {
            $shippingAddressValues = $data['shipping_address'];
            foreach ($shippingAddressValues as $accountField => $value) {
                if (is_array($value) && isset($value['value']) && in_array($accountField, $addressFieldsUpload)) {
                    $data['shipping_address'][$accountField] = $value['value'];
                }
            }
        }

        if (isset($data['order_field'])) {
            foreach ($data['order_field'] as $orderField => $value) {
                if (is_array($value)) {
                    $value = implode(',', $value);
                    $data['order_field'][$orderField] = $value;
                }
            }
        }

        return [$data];
    }

    /**
     * @param Create $subject
     * @param Quote $quote
     *
     * @return mixed
     */
    public function afterGetQuote(Create $subject, $quote)
    {

        $allFields = $this->allFields();

        $billingAddress = $quote->getBillingAddress();
        $shippingAddress = $quote->getShippingAddress();

        foreach ($allFields as $type => $fields) {
            /** @var Fields $field */
            foreach ($fields as $field) {
                /** @var Attribute $attribute */
                $attribute = $field->getData(DataHelper::ATTRIBUTE_DATA);
                $attributeCode = $field->getAttributeCode();
                if (!$billingAddress->getData($attributeCode)) {
                    $billingAddress->setData($attributeCode, $attribute->getDefaultValue());
                }
                if (!$shippingAddress->getData($attributeCode)) {
                    $shippingAddress->setData($attributeCode, $attribute->getDefaultValue());
                }

                $dataObject = $this->dataObjectFactory->create();

                $dataObject->setData('field_object', $field);
                $dataObject->setData('quote_id', $quote->getId());

                $upload = $this->addFileUpload($dataObject);
                if ($upload) {
                    $billingAddress->setData($attributeCode, $upload);
                    $quote->setData($attributeCode, $upload);
                }
            }
        }

        $quote->setBillingAddress($billingAddress);
        $quote->setShippingAddress($shippingAddress);

        return $quote;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function fields()
    {
        $filters = [
            $this->filterBuilder
                ->setField(FieldsInterface::ATTRIBUTE_TYPE)
                ->setValue(Customer::ENTITY)
                ->create(),
        ];

        $fields = $this->dataHelper->createFields($filters, OrderAttributes::ADMIN_ORDER_LOCATION);

        if (empty($fields[Customer::ENTITY])) {
            return [];
        }

        return array_keys($fields[Customer::ENTITY]);
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function allFields()
    {
        $fields = $this->dataHelper->createFields([], OrderAttributes::ADMIN_ORDER_LOCATION);

        return $fields;
    }

    /**
     * @param DataObject $dataObject
     *
     * @return bool|string
     */
    private function addFileUpload($dataObject)
    {
        $field = $dataObject->getData('field_object');
        if ($field->getFrontendInput() === InputType::FILE) {
            $fileUploaded = $this->fileUpload->processInputFieldValue($dataObject);
            return $fileUploaded;
        }

        return false;
    }

    private function addressUploadFields()
    {
        $allFields = $this->allFields();

        if (isset($allFields['customer_address'])) {
            $allAddressFields = $allFields['customer_address'];
            return array_keys($allAddressFields);
        }

        return [];
    }
}