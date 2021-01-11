<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2020-09-17T16:40:52+00:00
 * File:          app/code/Xtento/CustomAttributes/Helper/Data.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Helper;

use Magento\Config\Model\Config\Source\Yesno;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\State;
use Xtento\CustomAttributes\Api\Data\FieldsInterface;
use Xtento\CustomAttributes\Block\Adminhtml\Sales\Order\Create\OrderAttributes;
use Xtento\CustomAttributes\Model\CustomAttributes;
use Xtento\CustomAttributes\Model\Fields;
use Xtento\CustomAttributes\Model\FieldsRepository;
use Xtento\CustomAttributes\Model\Sources\InputType;
use Magento\Backend\Model\Session\Quote;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Data\OptionFactory as WidgetOption;
use Magento\Customer\Model\SessionFactory as Session;
use Magento\Eav\Model\Attribute;
use Magento\Eav\Model\AttributeRepository;
use Magento\Eav\Model\Entity\Attribute\Option;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\App\Area;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Xtento\CustomAttributes\Block\Customer\Dashboard\CustomerAttributes as CustomerAttributesDashboard;

class Data
{
    /**
     * This is the field id and attribute id, the
     * field should not have other character then letters
     * and numbers and _. This will be validated as such.
     */
    const FIELD_IDENTIFIER = FieldsInterface::ATTRIBUTE_CODE;
    const AVAILABLE_ON = FieldsInterface::AVAILABLE_ON;
    const FIELD_VALUES = 'field_values';
    const FIELD_SPECIFIC_POSITION = 'specific_position';
    const DB_DEFINITION = 'db_values';
    const LOCATION = 'location';
    const PARENT_LOCATION = 'parent_location';
    const LOCATION_ID = 'id';
    const STEP = 'step';
    const AVAILABLE_HIDDEN = 99;
    const AVAILABLE_ON_SHIPPING = 0;
    const AVAILABLE_ON_BILLING = 1;
    const AVAILABLE_ON_BOTH = 2;
    const ACTION = 'special_field_action';
    const ADD = 1;
    const EDIT = 2;
    const DELETE = 3;
    const TYPE_ID = 'type_id';
    const ATTRIBUTE_DATA = 'attribute_data_values';
    const ATTRIBUTE_OPTIONS_DATA = 'attribute_option_values';
    const MULTISELECTS = [
        'is_visible_on_front',
        'used_in_forms',
        'is_visible_on_front',
        'customer_groups'
    ];

    // Visibilities
    const CHECKOUT = 'checkout';
    const ORDER_VIEW = 'order_view';
    const REGISTRATION_FORM = 'registration_form';
    const CUSTOMER_ACCOUNT = 'customer_account';

    /**
     * @var FieldsRepository
     */
    private $fieldsRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteria;

    /**
     * @var AttributeRepository
     */
    private $attributeRepository;

    /**
     * @var CollectionFactory
     */
    private $optionCollectionFactory;

    /**
     * @var EavSetupFactory
     */
    private $eavSetup;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var FilterGroupBuilder
     */
    private $filterGroupBuilder;

    /**
     * @var WidgetOption
     */
    private $widgetOption;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var Quote
     */
    private $adminQuoteSession;

    /**
     * @var State
     */
    private $state;

    /**
     * @var CustomAttributes
     */
    private $customAttributes;

    /**
     * @var Module
     */
    private $moduleHelper;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Yesno
     */
    private $yesNo;

    /**
     * Data constructor.
     *
     * @param FieldsRepository $fieldsRepository
     * @param SearchCriteriaBuilder $searchCriteria
     * @param AttributeRepository $attributeRepository
     * @param CollectionFactory $optionCollectionFactory
     * @param EavSetupFactory $eavSetup
     * @param StoreManagerInterface $storeManager
     * @param FilterBuilder $filterBuilder
     * @param FilterGroupBuilder $filterGroupBuilder
     * @param WidgetOption $widgetOption
     * @param Session $customerSession
     * @param Quote $adminQuoteSession
     * @param State $state
     * @param CustomAttributes $customAttributes
     * @param Module $moduleHelper
     * @param RequestInterface $request
     * @param Yesno $yesNo
     */
    public function __construct(
        FieldsRepository $fieldsRepository,
        SearchCriteriaBuilder $searchCriteria,
        AttributeRepository $attributeRepository,
        CollectionFactory $optionCollectionFactory,
        EavSetupFactory $eavSetup,
        StoreManagerInterface $storeManager,
        FilterBuilder $filterBuilder,
        FilterGroupBuilder $filterGroupBuilder,
        WidgetOption $widgetOption,
        Session $customerSession,
        Quote $adminQuoteSession,
        State $state,
        CustomAttributes $customAttributes,
        Module $moduleHelper,
        RequestInterface $request,
        Yesno $yesNo,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
    ) {
        $this->fieldsRepository = $fieldsRepository;
        $this->searchCriteria = $searchCriteria;
        $this->attributeRepository = $attributeRepository;
        $this->optionCollectionFactory = $optionCollectionFactory;
        $this->eavSetup = $eavSetup;
        $this->storeManager = $storeManager;
        $this->filterBuilder = $filterBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->widgetOption = $widgetOption;
        $this->customerSession = $customerSession;
        $this->adminQuoteSession = $adminQuoteSession;
        $this->state = $state;
        $this->customAttributes = $customAttributes;
        $this->moduleHelper = $moduleHelper;
        $this->request = $request;
        $this->yesNo = $yesNo;
        $this->timezone = $timezone;
    }

    /**
     * We will use the param location to create the criteria search to filter
     * the list so we only get the active, location related filters.
     *
     * @param array $filters
     * @param string $location
     * @param null $group
     * @param null $store
     * @param bool $runEvenIfModuleDisabled
     *
     * @return array
     */
    public function createFields($filters = [], $location = 'checkout', $group = null, $store = null, $runEvenIfModuleDisabled = false)
    {
        if (!$runEvenIfModuleDisabled && !$this->moduleHelper->isModuleEnabled()) {
            return [];
        }

        $fields = $this->fieldsData($filters, $location, $group, $store);
        return $fields;
    }

    /**
     * @param $filters
     * @param $location
     * @param null $group
     * @param null $store
     *
     * @return array
     */
    private function fieldsData($filters, $location, $group = null, $store = null)
    {
        $searchCriteriaBuilder = $this->searchCriteria;

        $adminQuote = $this->adminQuoteSession;

        /** @var \Magento\Customer\Model\Session $session */
        $session = $this->customerSession->create();
        if ($group === null) {
            $group = $session->getCustomerGroupId();
        }

        $areaCode = '';
        try {
            $areaCode = $this->state->getAreaCode();
        } catch (\Exception $e) {}

        $applyStoreFilter = true;
        if ($areaCode === Area::AREA_ADMINHTML && $location !== OrderAttributes::ADMIN_GRID_LOCATION && $store !== false) {
            $getStore = $adminQuote->getStore();
            if ($location !== 'checkout') {
                $quote = $adminQuote->getQuote();
                $group = $quote->getCustomerGroupId();
            }
            $store = $getStore->getStoreId();
            // If customer is being edited, don't apply a store filter - does not apply there
            $customerPost = $this->request->getPost('customer');
            if (is_array($customerPost) && array_key_exists('store_id', $customerPost)) {
                $applyStoreFilter = false;
            }
            $this->adminAreaFilter();
        }
        if ($store === false) {
            $applyStoreFilter = false;
        }

        // Add filters to searchCriteriaBuilder
        $this->activeTypeFilter();
        $this->filtersFields($filters);

        if ($applyStoreFilter) {
            $this->storeFilter($store);
        }

        $this->visibilityFilter($location);

        $searchCriteria = $searchCriteriaBuilder->create();

        $items = $this->fieldsRepository->getList($searchCriteria);
        $items = $items->getItems();

        $fieldsTemplates = $this->iterator($location, $group, $items);

        return $fieldsTemplates;
    }

    /**
     * @param Fields $item
     *
     * @return Fields
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function addAttributeData(Fields $item)
    {
        $attributeType = $item->getAttributeTypeId();
        if ($attributeType === CustomAttributes::ORDER_ENTITY) {
            $attributeType = Customer::ENTITY;
        }

        $entityTypeCode = $this->eavSetup->create()->getEntityTypeId(
            $attributeType
        );

        $attributeCode = $item->getAttributeCode();

        $attribute = $this->attributeRepository->get($entityTypeCode, $attributeCode);

        $attributeDefaultValue = $attribute->getDefaultValue();
        $attributeInputType = $attribute->getFrontendInput();

        if ($item->getShowLastValue()) {
            $attributeDefaultValue = $this->getLastAttributeValue(
                $item->getAttributeCode()
            );
            $attribute->setDefaultValue($attributeDefaultValue);
        }

        $item->setData(self::ATTRIBUTE_DATA, $attribute);
        $item->setData('default_value', $attributeDefaultValue);
        $item->setData('default_value_text', ($attributeInputType != InputType::DATE && $attributeInputType != InputType::DATETIME) ? $attributeDefaultValue : $this->timezone->date($attributeDefaultValue)->format('Y-m-d'));
        $item->setData('default_value_yesno', $attributeDefaultValue);
        $item->setData('default_value_date', ($attributeInputType != InputType::DATE && $attributeInputType != InputType::DATETIME) ? $attributeDefaultValue : null);
        $item->setData('default_value_textarea', $attributeDefaultValue);

        $storeId = $this->storeManager->getStore()->getId();
        $rowOptions = $this->getStoreOptionValues($storeId, $item->getAttributeId());
        if (!empty($rowOptions)) {
            $options = $this->options($rowOptions);
            $item->setData(self::ATTRIBUTE_OPTIONS_DATA, $options);
        }

        return $item;
    }

    private function getLastAttributeValue($attributeCode)
    {
        $lastOrder = $this->customAttributes->lastOrderData();
        if ($lastOrder) {
            return $lastOrder->getData($attributeCode);
        }
    }

    private function options($rowOptions)
    {
        $blank = ['label' => '', 'value' => ''];
        array_unshift($rowOptions, $blank);
        foreach ($rowOptions as $option) {
            $options[] = $this->widgetOption->create()
                ->setLabel($option['label'])
                ->setValue($option['value']);
        }
        return $options;
    }

    /**
     * @param $storeId
     * @param $attributeId
     *
     * @return array
     */
    public function getStoreOptionValues($storeId, $attributeId)
    {
        $values = [];
        $valuesCollection = $this->optionCollectionFactory->create()
            ->setAttributeFilter($attributeId)
            ->setOrder('sort_order', 'asc')
            ->setStoreFilter($storeId, false)
            ->load();

        /** @var Option $item */
        foreach ($valuesCollection as $item) {
            $values[] = [
                'value' => $item->getId(),
                'label' => $item->getValue(),
                'sort_order' => $item->getSortOrder()
            ];
        }

        $values = $this->getFallbackOptionValues($values, $attributeId);

        // Sort values by sort_order
        usort($values, [$this, 'sortBySortOrder']);
        $values = array_diff_key($values, array_flip(['sort_order'])); // Remove sort_order again

        return $values;
    }

    protected function sortBySortOrder($a, $b)
    {
        if ($a['sort_order'] == $b['sort_order']) {
            return 0;
        }
        return ($a['sort_order'] < $b['sort_order']) ? -1 : 1;
    }

    /**
     * Get option values for admin store
     *
     * @param $values
     * @param $attributeId
     *
     * @return array
     */
    protected function getFallbackOptionValues($values, $attributeId)
    {
        $adminStoreValuesCollection = $this->optionCollectionFactory->create()
            ->setAttributeFilter($attributeId)
            ->setOrder('sort_order', 'asc')
            ->setStoreFilter(0, false)
            ->load();

        /** @var Option $item */
        foreach ($adminStoreValuesCollection as $item) {
            $itemFound = false;
            foreach ($values as $value) {
                if ($value['value'] == $item->getId()) {
                    $itemFound = true;
                    break 1;
                }
            }
            if ($itemFound === false) {
                $values[] = [
                    'value' => $item->getId(),
                    'label' => $item->getValue(),
                    'sort_order' => $item->getSortOrder()
                ];
            }
        }
        return $values;
    }

    /**
     * @param $storeId
     * @param Attribute $attribute
     *
     * @return array
     */
    public function getAdminOptionValues($storeId, $attribute)
    {
        $attributeId = $attribute->getAttributeId();

        $values = [];
        $valuesCollection = $this->optionCollectionFactory->create()
            ->setAttributeFilter($attributeId)
            ->setOrder('sort_order', 'asc')
            ->setStoreFilter($storeId, false)
            ->load();

        /** @var Option $item */
        foreach ($valuesCollection as $item) {
            $values[$item->getId()] = [
                'value' => $item->getId(),
                'label' => $item->getValue()
            ];
        }

        if (empty($values)) {
            // Is this a special attribute?
            if ($attribute->getFrontendInput() === InputType::BOOLEAN) {
                $values = $this->yesNo->toOptionArray();
            }
        }

        return $values;
    }

    /**
     * @param $data
     *
     * @return array
     */
    public function implodeExplodeData($data)
    {
        $result = [];
        foreach ($data as $key => $value) {
            if (in_array($key, self::MULTISELECTS) && is_array($value)) {
                $result[$key] = implode(',', $value);
                continue;
            }

            if (in_array($key, self::MULTISELECTS) && !is_array($value)) {
                $result[$key] = explode(',', $value);
                continue;
            }

            $result[$key] = $value;
        }

        return $result;
    }

    public function yesNoCheckout()
    {
        return [
            [
                'value' => '',
                'label' => __('Select'),
            ],
            [
                'value' => 0,
                'label' => __('No')
            ],
            [
                'value' => 1,
                'label' => __('Yes')
            ]
        ];
    }

    public function getModuleHelper()
    {
        return $this->moduleHelper;
    }

    private function adminAreaFilter()
    {
        $searchCriteriaBuilder = $this->searchCriteria;

        $backendFilter [] = $this->filterBuilder
            ->setField(FieldsInterface::IS_VISIBLE_ON_BACK)
            ->setValue(1)
            ->setConditionType('eq')
            ->create();

        $searchCriteriaBuilder
            ->addFilters($backendFilter);
    }

    private function activeTypeFilter()
    {
        $searchCriteriaBuilder = $this->searchCriteria;

        $active[] = $this->filterBuilder
            ->setField(FieldsInterface::IS_ACTIVE)
            ->setValue(1)
            ->setConditionType('eq')
            ->create();

        $searchCriteriaBuilder
            ->addFilters($active);
    }

    private function filtersFields($filters)
    {
        $searchCriteriaBuilder = $this->searchCriteria;

        if (!empty($filters)) {
            $filterGroups = [];
            foreach ($filters as $filter) {
                $filterGroups[] = $this->filterGroupBuilder->setFilters([$filter])->create();
            }
            //$searchCriteriaBuilder->addFilters($filters);
            // Required so filters are chained using "and"
            $searchCriteriaBuilder->setFilterGroups($filterGroups);
        }
    }

    private function storeFilter($store)
    {
        $searchCriteriaBuilder = $this->searchCriteria;
        if ($store === null) {
            $store = $this->storeManager->getStore()->getId();
        }

        $storeFilter[] = $this->filterBuilder
            ->setField(FieldsInterface::STORE_ID)
            ->setValue($store)
            ->setConditionType('in')
            ->create();
        $searchCriteriaBuilder
            ->addFilters($storeFilter);
    }

    /**
     * @param $location
     */
    private function visibilityFilter($location)
    {
        $visibilityFilter = [];
        if ($location == CustomerAttributesDashboard::CUSTOMER_ACCOUNT) {
            $visibilityFilter[] = $this->filterBuilder
                ->setField(FieldsInterface::IS_VISIBLE_ON_FRONT)
                ->setValue('%' . $location . '%')
                ->setConditionType('like')
                ->create();
        }
        if ($location == Data::ORDER_VIEW) {
            $visibilityFilter[] = $this->filterBuilder
                ->setField(FieldsInterface::IS_VISIBLE_ON_FRONT)
                ->setValue('%' . $location . '%')
                ->setConditionType('like')
                ->create();
        }
        if (!empty($visibilityFilter)) {
            $searchCriteriaBuilder = $this->searchCriteria;
            $searchCriteriaBuilder->addFilters($visibilityFilter);
        }
    }

    /**
     * @param $location
     * @param $group
     * @param $items
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function iterator($location, $group, $items)
    {
        $fieldsTemplates = [];

        $areaCode = '';
        try {
            $areaCode = $this->state->getAreaCode();
        } catch (\Exception $e) {}

        /** @var Fields $item */
        foreach ($items as $item) {
            if (!$item->getAttributeId() || !$item->getAttributeTypeId()) {
                continue;
            }

            $customerGroups = explode(',', $item->getCustomerGroups());
            if (isset($customerGroups[0]) && $customerGroups[0] == '') {
                // "All customer groups" - no checking
            } else {
                if (!in_array($group, $customerGroups)) {
                    continue;
                }
            }

            if ($areaCode !== Area::AREA_ADMINHTML && (int)$item->getAvailableOn() == Data::AVAILABLE_HIDDEN && $location !== 'api') {
                continue;
            }

            $itemWithAttribute = $this->addAttributeData($item);

            if ($location !== 'checkout') {
                $fieldsTemplates[$item->getAttributeTypeId()][$item->getAttributeCode()] =
                    $itemWithAttribute;
                continue;
            }

            $fieldsTemplates[$item->getAttributeTypeId()][$item->getAttributeCode()] =
                $this->createCheckoutFieldTemplate($itemWithAttribute);
        }
        return $fieldsTemplates;
    }

    /**
     * @param Fields $item
     *
     * @return array
     */
    private function createCheckoutFieldTemplate(Fields $item)
    {
        $storeId = $this->storeManager->getStore()->getId();
        /** @var Attribute $attribute */
        $attribute = $item->getData(self::ATTRIBUTE_DATA);
        $attributeType = $item->getAttributeCode();

        $frontEndLabel = $attribute->getStoreLabel();

        $storeLabels = $attribute->getStoreLabels();
        if (isset($storeLabels[$storeId])) {
            $frontEndLabel = $storeLabels[$storeId];
        }

        $options = $this->getStoreOptionValues($storeId, $item->getAttributeId());

        if ($item->getFrontendInput() === InputType::BOOLEAN) {
            $options = $this->yesNoCheckout();
        }

        $fieldLocation = (int)$item->getAvailableOn();

        $availableOn = [];
        if ($fieldLocation <= 2) {
            $availableOn = self::AVAILABLE_ON;
        }

        if ($fieldLocation > 2) {
            $availableOn = self::FIELD_SPECIFIC_POSITION;
        }

        $frontendInput = $item->getFrontendInput();
        $frontendOption = $item->getFrontendOption();
        if ($frontendInput === InputType::BOOLEAN && $frontendOption == 1) {
            $frontendInput = 'checkbox';
        }
        if ($frontendInput === InputType::SELECT && $frontendOption == 1) {
            $frontendInput = 'radio';
        }
        if ($frontendInput === InputType::MULTI_SELECT && $frontendOption == 1) {
            $frontendInput = 'multicheckbox';
        }
        if ($frontendInput === InputType::DATE && $frontendOption == 1) {
            $frontendInput = 'datetime';
        }

        $attributeType = [
            self::FIELD_IDENTIFIER => $attributeType,
            'label' => $frontEndLabel,
            self::FIELD_VALUES => [
                $availableOn => $fieldLocation
            ],
            'config' => [
                'component' => FieldTemplates::COMPONENT_BY_TYPE[$frontendInput],
            ],
            'options' => $options,
            'validation' => [
                'required-entry' => (bool)$item->getFieldRequired()
            ],
            'sortOrder' => $item->getCheckoutPosition(),
            'value' => explode(',', $attribute->getDefaultValue()),
            'default' => explode(',', $attribute->getDefaultValue()),
            'visible_on' => explode(',', $item->getIsVisibleOnFront()),
        ];

        $toolTip = $item->getTooltip();

        if ($toolTip) {
            $attributeType['tooltip'] = [
                'description' => $toolTip
            ];
        }

        $frontendClass = $item->getFrontendClass();
        if ($frontendClass) {
            //$attributeType['validation'] = true;
        }
        $maxLength = $item->getMaxLength();
        if ($maxLength > 0 && is_array($attributeType['validation'])) {
            $attributeType['validation']['max_text_length'] = $maxLength;
            $attributeType['validation']['min_text_length'] = 1;
        }

        return $attributeType;
    }

    /**
     * @param $entityTypeId
     */
    public function getEntityTypeById($entityTypeId)
    {
        $entityType = $this->eavSetup->create()->getEntityType($entityTypeId);
        return $entityType;
    }
}
