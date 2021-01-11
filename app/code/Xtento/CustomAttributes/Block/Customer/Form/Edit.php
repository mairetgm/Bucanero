<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2020-03-28T15:54:29+00:00
 * File:          app/code/Xtento/CustomAttributes/Block/Customer/Form/Edit.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Block\Customer\Form;

use Xtento\CustomAttributes\Api\Data\FieldsInterface;
use Xtento\CustomAttributes\Helper\Data;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\DataObjectFactory;
use Xtento\CustomAttributes\Helper\Data as DataHelper;
use Xtento\CustomAttributes\Helper\Module as ConfigHelper;
use Magento\Framework\View\Element\Template\Context;
use Magento\Newsletter\Model\SubscriberFactory;

class Edit extends \Magento\Customer\Block\Form\Edit
{
    /**
     * @var DataHelper
     */
    private $dataHelper;

    /**
     * @var DataObjectFactory
     */
    private $dataObject;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    private $configHelper;

    public function __construct(
        Context $context,
        Session $customerSession,
        SubscriberFactory $subscriberFactory,
        CustomerRepositoryInterface $customerRepository,
        AccountManagementInterface $customerAccountManagement,
        DataHelper $dataHelper,
        DataObjectFactory $dataObject,
        FilterBuilder $filterBuilder,
        ConfigHelper $configHelper,
        array $data = []
    ) {
        $this->dataHelper    = $dataHelper;
        $this->dataObject    = $dataObject;
        $this->filterBuilder = $filterBuilder;
        $this->configHelper  = $configHelper;

        parent::__construct(
            $context,
            $customerSession,
            $subscriberFactory,
            $customerRepository,
            $customerAccountManagement,
            $data
        );
    }

    public function customAttributes()
    {
        $filters = [
            $this->filterBuilder
                ->setField(FieldsInterface::USED_IN_FORMS)
                ->setValue('%customer_account_edit%')
                ->setConditionType('like')
                ->create()
        ];

        $fields = $this->dataHelper->createFields($filters, Register::CUSTOMER_LOCATION);

        $this->setData(Register::CUSTOM_ATTRIBUTES_FIELDS, $fields);

        return $fields;
    }

    public function getFormData()
    {
        $data = $this->getData('form_data');
        if ($data === null) {
            $formData = $this->customerSession->getCustomerFormData(true);
            $data = [];
            if ($formData) {
                $data['data'] = $formData;
                $data['customer_data'] = 1;
            }
            $this->setData('form_data', $data);
        }
        return $data;
    }
}
