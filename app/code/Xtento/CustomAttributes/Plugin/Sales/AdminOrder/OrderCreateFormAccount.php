<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-01-12T17:30:34+00:00
 * File:          app/code/Xtento/CustomAttributes/Plugin/Sales/AdminOrder/OrderCreateFormAccount.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Plugin\Sales\AdminOrder;

use Xtento\CustomAttributes\Api\Data\FieldsInterface;
use Xtento\CustomAttributes\Block\Adminhtml\Sales\Order\Create\OrderAttributes;
use Xtento\CustomAttributes\Model\Fields;
use Xtento\CustomAttributes\Model\Sales\AdminOrder\Create;
use Xtento\CustomAttributes\Helper\Data as DataHelper;
use Magento\Eav\Model\Attribute;
use Magento\Framework\Api\FilterBuilder;
use Magento\Sales\Block\Adminhtml\Order\Create\Form\Account;
use Magento\Customer\Model\Customer;

class OrderCreateFormAccount
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
     * CreatePlugin constructor.
     * @param DataHelper $dataHelper
     * @param FilterBuilder $filterBuilder
     */
    public function __construct(
        DataHelper $dataHelper,
        FilterBuilder $filterBuilder
    ) {
        $this->dataHelper    = $dataHelper;
        $this->filterBuilder = $filterBuilder;
    }

    /**
     * @param Create $subject
     * @param $data
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterGetFormValues(Account $subject, $initialDatas)
    {
        $fields = $this->fields();

        $data = [];

        foreach ($initialDatas as $key => $initialData) {
            if (isset($fields[$key]) && $initialData == null) {
                /** @var Fields $field */
                $field = $fields[$key];
                /** @var Attribute $attribute */
                $attribute = $field->getData(DataHelper::ATTRIBUTE_DATA);
                $data[$key] = $attribute->getDefaultValue();
                unset($fields[$key]);
                continue;
            }

            if (!in_array($key, array_keys($fields))) {
                $data[$key] = $initialData;
                continue;
            }
        }

        /** @var Fields $field */
        foreach ($fields as $fieldKey => $field) {
            /** @var Attribute $attribute */
            $attribute = $field->getData(DataHelper::ATTRIBUTE_DATA);
            $data[$fieldKey] = $attribute->getDefaultValue();
        }

        return $data;
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

        return $fields[Customer::ENTITY];
    }
}
