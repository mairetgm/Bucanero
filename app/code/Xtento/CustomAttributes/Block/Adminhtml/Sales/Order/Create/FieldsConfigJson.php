<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-03-27T20:28:24+00:00
 * File:          app/code/Xtento/CustomAttributes/Block/Adminhtml/Sales/Order/Create/FieldsConfigJson.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Block\Adminhtml\Sales\Order\Create;

use Xtento\CustomAttributes\Api\Data\FieldsInterface;
use Xtento\CustomAttributes\Model\Fields;
use Xtento\CustomAttributes\Model\FieldsRepository;
use Xtento\CustomAttributes\Model\Sources\InputType;
use Magento\Backend\Block\Template;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Api\SearchCriteriaBuilder;

class FieldsConfigJson extends Template
{
    private $json;
    private $fieldsRepository;
    private $searchCriteria;

    public function __construct(
        Template\Context $context,
        Json $json,
        FieldsRepository $fieldsRepository,
        SearchCriteriaBuilder $searchCriteria,
        array $data = []
    ) {
        $this->json = $json;
        $this->fieldsRepository = $fieldsRepository;
        $this->searchCriteria = $searchCriteria;
        parent::__construct($context, $data);
    }

    public function jsonUploadFields()
    {
        return $this->json->serialize($this->uploadFields());
    }

    private function uploadFields()
    {
        $searchCriteriaBuilder = $this->searchCriteria;
        $searchCriteria = $searchCriteriaBuilder
            ->addFilter(
                FieldsInterface::FRONTEND_INPUT,
                InputType::FILE,
                'eq'
            )->create();
        $fields = $this->fieldsRepository->getList($searchCriteria);
        $uploadFields = [];
        /** @var Fields $item */
        foreach ($fields->getItems() as $item) {
            $uploadFields[] = $item->getAttributeCode();
        }
        return $uploadFields;
    }
}