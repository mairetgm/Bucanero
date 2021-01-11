<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2020-04-09T13:44:42+00:00
 * File:          app/code/Xtento/CustomAttributes/Plugin/Sales/AdminOrder/FormPlugin.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Plugin\Sales\AdminOrder;

use Xtento\CustomAttributes\Block\Adminhtml\Sales\Order\Create\OrderAttributes;
use Xtento\CustomAttributes\Model\Fields;
use Xtento\CustomAttributes\Model\FieldsRepository;
use Xtento\CustomAttributes\Helper\Data as DataHelper;
use Magento\Customer\Model\Metadata\Form;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;

class FormPlugin
{
    /**
     * @var DataHelper
     */
    private $dataHelper;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    private $fieldsRepository;

    private $searchCriteria;

    /**
     * CreatePlugin constructor.
     * @param DataHelper $dataHelper
     * @param FilterBuilder $filterBuilder
     */
    public function __construct(
        DataHelper $dataHelper,
        FilterBuilder $filterBuilder,
        FieldsRepository $fieldsRepository,
        SearchCriteriaBuilder $searchCriteria
    ) {
        $this->dataHelper       = $dataHelper;
        $this->filterBuilder    = $filterBuilder;
        $this->fieldsRepository = $fieldsRepository;
        $this->searchCriteria   = $searchCriteria;
    }

    /**
     * @param Form $subject
     * @param $data
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterGetAttributes(Form $subject, $result)
    {
        $allowedFields = $this->fields();

        $searchCriteria = $this->searchCriteria->create();
        $allFields = $this->fieldsRepository->getList($searchCriteria)->getItems();

        if (!is_array($allFields) && empty($allFields)) {
            return $result;
        }

        $allFieldsToProcess = [];

        /** @var Fields $field */
        foreach ($allFields as $field) {
            $allFieldsToProcess[$field->getAttributeCode()] = $field->getAttributeCode();
        }

        $codesToRemove = array_diff($allFieldsToProcess, $allowedFields);

        foreach ($codesToRemove as $code) {
            if (isset($result[$code])) {
                unset($result[$code]);
            }
        }

        return $result;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function fields()
    {
        $dataHelper = $this->dataHelper;

        $fieldsType = $dataHelper->createFields([], OrderAttributes::ADMIN_ORDER_LOCATION);

        $fields = [];
        foreach ($fieldsType as $fieldType => $allFields) {
            foreach ($allFields as $field) {
                $fields[$field->getAttributeCode()] = $field->getAttributeCode();
            }
        }

        return $fields;
    }
}
