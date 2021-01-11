<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-01-18T15:29:25+00:00
 * File:          app/code/Xtento/CustomAttributes/Block/Customer/Form/Register.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Block\Customer\Form;

use Xtento\CustomAttributes\Api\Data\FieldsInterface;
use Xtento\CustomAttributes\Helper\Module as ConfigHelper;
use Xtento\CustomAttributes\Helper\Data as DataHelper;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Api\FilterBuilder;
use Magento\Customer\Block\Form\Register as CustomerRegister;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url;
use Magento\Directory\Helper\Data;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory as CountryCollectionFactory;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory;
use Magento\Framework\App\Cache\Type\Config;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Module\Manager;
use Magento\Framework\View\Element\Template\Context;

class Register extends CustomerRegister
{
    const CUSTOMER_LOCATION = 'customer_location';
    const CUSTOM_ATTRIBUTES_FIELDS = 'custom_attributes_customer_fields';
    const CUSTOM_ATTRIBUTES_ADDRESS_FIELDS = 'custom_attributes_customer_fields';

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

    /**
     * @var ConfigHelper
     */
    private $configHelper;

    /**
     * Register constructor.
     * @param Context $context
     * @param Data $directoryHelper
     * @param EncoderInterface $jsonEncoder
     * @param Config $configCacheType
     * @param CollectionFactory $regionCollectionFactory
     * @param CountryCollectionFactory $countryCollectionFactory
     * @param Manager $moduleManager
     * @param Session $customerSession
     * @param Url $customerUrl
     * @param DataHelper $dataHelper
     * @param DataObjectFactory $dataObject
     * @param FilterBuilder $filterBuilder
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $directoryHelper,
        EncoderInterface $jsonEncoder,
        Config $configCacheType,
        CollectionFactory $regionCollectionFactory,
        CountryCollectionFactory $countryCollectionFactory,
        Manager $moduleManager,
        Session $customerSession,
        Url $customerUrl,
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
            $directoryHelper,
            $jsonEncoder,
            $configCacheType,
            $regionCollectionFactory,
            $countryCollectionFactory,
            $moduleManager,
            $customerSession,
            $customerUrl,
            $data
        );
    }

    public function customAttributes()
    {
        $filters = [
            $this->filterBuilder
                ->setField(FieldsInterface::IS_VISIBLE_ON_FRONT)
                ->setValue('%registration_form%')
                ->setConditionType('like')
                ->create()
        ];

        $fields = $this->dataHelper->createFields($filters, self::CUSTOMER_LOCATION);

        $this->setData(self::CUSTOM_ATTRIBUTES_FIELDS, $fields);
        return $this;
    }

    public function getFormData()
    {
        $data = $this->getData('form_data');
        if ($data === null) {
            $formData = $this->_customerSession->getCustomerFormData(true);
            $data = new \Magento\Framework\DataObject();
            if ($formData) {
                $data->addData($formData);
                $data->setCustomerData(1);
            }
            if (isset($data['region_id'])) {
                $data['region_id'] = (int)$data['region_id'];
            }
            $this->setData('form_data', $data);
        }

        $this->customAttributes();
        return $data;
    }

    public function showAddress()
    {
        return $this->configHelper->isShowCustomerAddress();
    }
}
