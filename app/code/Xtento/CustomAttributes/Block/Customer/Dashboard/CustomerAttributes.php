<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-06-14T13:14:57+00:00
 * File:          app/code/Xtento/CustomAttributes/Block/Customer/Dashboard/CustomerAttributes.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Block\Customer\Dashboard;

use Xtento\CustomAttributes\Api\Data\FieldsInterface;
use Xtento\CustomAttributes\Helper\Attribute;
use Xtento\CustomAttributes\Helper\Data as DataHelper;
use Magento\Customer\Block\Account\Dashboard\Info;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Customer\Helper\View;
use Magento\Customer\Model\Customer;
use Magento\Framework\View\Element\Template\Context;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\UrlInterface;

class CustomerAttributes extends Info
{
    const CUSTOMER_ACCOUNT = 'customer_account';

    private $dataHelper;

    private $filterBuilder;

    private $url;

    private $attributeHelper;

    /**
     * CustomerAttributes constructor.
     *
     * @param Context $context
     * @param CurrentCustomer $currentCustomer
     * @param SubscriberFactory $subscriberFactory
     * @param View $helperView
     * @param DataHelper $dataHelper
     * @param FilterBuilder $filterBuilder
     * @param UrlInterface $url
     * @param Attribute $attributeHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        CurrentCustomer $currentCustomer,
        SubscriberFactory $subscriberFactory,
        View $helperView,
        DataHelper $dataHelper,
        FilterBuilder $filterBuilder,
        UrlInterface $url,
        Attribute $attributeHelper,
        array $data = []
    ) {
        $this->dataHelper        = $dataHelper;
        $this->filterBuilder     = $filterBuilder;
        $this->url               = $url;
        $this->attributeHelper   = $attributeHelper;

        parent::__construct(
            $context,
            $currentCustomer,
            $subscriberFactory,
            $helperView,
            $data
        );
    }

    /**
     * @return array|bool
     */
    public function fields()
    {
        $filters = [
            $this->filterBuilder
                ->setField(FieldsInterface::ATTRIBUTE_TYPE)
                ->setValue(Customer::ENTITY)
                ->create(),
            $this->filterBuilder
                ->setField(FieldsInterface::IS_VISIBLE_ON_FRONT)
                ->setValue('%customer_account%')
                ->setConditionType('like')
                ->create()
        ];

        /** @var array $fields */
        $fields = $this->dataHelper->createFields($filters, self::CUSTOMER_ACCOUNT);

        if (empty($fields)) {
            return false;
        }

        return $fields;
    }

    /**
     * @param Attribute $attribute
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addValues($attribute)
    {
        $customer = $this->currentCustomer->getCustomer();
        return $this->attributeHelper->getCustomerAttributeText($customer, $attribute);
    }

    public function getMediaDownloadLink()
    {
        return $this->url->getUrl('xtento_customattributes/index/download/file/');
    }
}
