<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-08-27T12:30:21+00:00
 * File:          app/code/Xtento/CustomAttributes/Block/Address/Edit.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Block\Address;

use Xtento\CustomAttributes\Block\Customer\Form\Register;
use Xtento\CustomAttributes\Helper\Data as DataHelper;
use Xtento\CustomAttributes\Helper\Module as ConfigHelper;
use Xtento\CustomAttributes\Api\Data\FieldsInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Customer\Model\Session;
use Magento\Directory\Helper\Data;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\App\Cache\Type\Config;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\View\Element\Template\Context;

class Edit extends \Magento\Customer\Block\Address\Edit
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

    /**
     * @var ConfigHelper
     */
    private $configHelper;

    /**
     * Edit constructor.
     *
     * @param Context $context
     * @param Data $directoryHelper
     * @param EncoderInterface $jsonEncoder
     * @param Config $configCacheType
     * @param RegionCollectionFactory $regionCollectionFactory
     * @param CollectionFactory $countryCollectionFactory
     * @param Session $customerSession
     * @param AddressRepositoryInterface $addressRepository
     * @param AddressInterfaceFactory $addressDataFactory
     * @param CurrentCustomer $currentCustomer
     * @param DataObjectHelper $dataObjectHelper
     * @param DataHelper $dataHelper
     * @param DataObjectFactory $dataObject
     * @param FilterBuilder $filterBuilder
     * @param ConfigHelper $configHelper
     * @param array $data
     * @param null $attributeChecker
     */
    public function __construct(
        Context $context,
        Data $directoryHelper,
        EncoderInterface $jsonEncoder,
        Config $configCacheType,
        RegionCollectionFactory $regionCollectionFactory,
        CollectionFactory $countryCollectionFactory,
        Session $customerSession,
        AddressRepositoryInterface $addressRepository,
        AddressInterfaceFactory $addressDataFactory,
        CurrentCustomer $currentCustomer,
        DataObjectHelper $dataObjectHelper,
        DataHelper $dataHelper,
        DataObjectFactory $dataObject,
        FilterBuilder $filterBuilder,
        ConfigHelper $configHelper,
        array $data = [],
        $attributeChecker = null
    ) {
        $this->dataHelper    = $dataHelper;
        $this->dataObject    = $dataObject;
        $this->filterBuilder = $filterBuilder;
        $this->configHelper  = $configHelper;

        if (!class_exists(\Magento\Customer\Model\AttributeChecker::class)) {
            parent::__construct(
                $context,
                $directoryHelper,
                $jsonEncoder,
                $configCacheType,
                $regionCollectionFactory,
                $countryCollectionFactory,
                $customerSession,
                $addressRepository,
                $addressDataFactory,
                $currentCustomer,
                $dataObjectHelper,
                $data
            );
        } else {
            parent::__construct(
                $context,
                $directoryHelper,
                $jsonEncoder,
                $configCacheType,
                $regionCollectionFactory,
                $countryCollectionFactory,
                $customerSession,
                $addressRepository,
                $addressDataFactory,
                $currentCustomer,
                $dataObjectHelper,
                $data,
                $attributeChecker
            );
        }
    }

    public function customAttributes()
    {
        $filters = [
            $this->filterBuilder
                ->setField(FieldsInterface::IS_VISIBLE_ON_FRONT)
                ->setValue('%customer_account%')
                ->setConditionType('like')
                ->create()
        ];

        $fields = $this->dataHelper->createFields($filters, Register::CUSTOMER_LOCATION);

        $this->setData(Register::CUSTOM_ATTRIBUTES_FIELDS, $fields);

        return $fields;
    }
}
