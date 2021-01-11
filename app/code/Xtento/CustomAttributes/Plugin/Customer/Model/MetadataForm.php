<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2020-04-09T13:38:32+00:00
 * File:          app/code/Xtento/CustomAttributes/Plugin/Customer/Model/MetadataForm.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Plugin\Customer\Model;

use Xtento\CustomAttributes\Api\Data\FieldsInterface;
use Xtento\CustomAttributes\Model\CustomAttributes;
use Xtento\CustomAttributes\Model\Fields;
use Xtento\CustomAttributes\Model\FieldsRepository;
use Xtento\CustomAttributes\Model\FileUpload;
use Magento\Customer\Model\Metadata\Form;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\File\Uploader;
use Magento\Store\Model\StoreManagerInterface;

class MetadataForm
{
    private $request;
    private $fileUpload;
    private $fieldsRepository;
    private $searchCriteriaBuilder;
    private $filterBuilder;
    private $storeManager;

    public function __construct(
        RequestInterface $request,
        FileUpload $fileUpload,
        FieldsRepository $fieldsRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        StoreManagerInterface $storeManager
    ) {
        $this->request = $request;
        $this->fileUpload = $fileUpload;
        $this->fieldsRepository = $fieldsRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->storeManager = $storeManager;
    }

    public function afterGetAllowedAttributes(Form $subject, $return): array
    {
        $actionName = $this->request->getActionName();
        if ($actionName !== 'createpost') {
            return $return;
        }
        $params = $this->request->getParams();
        $files = $this->request->getFiles()->toArray();
        $addressFields = $this->addressFields();
        foreach ($files as $code => $file) {
            if ($file['tmp_name']) {
                if (in_array($code, $addressFields)) {
                    $fileForParam = $this->fileUpload->processUpload(
                        $file,
                        'customer_address'
                    );
                    $fileForParam = str_replace('/', '', $fileForParam);
                    $params[$code] = Uploader::getDispersionPath($fileForParam) .
                        DIRECTORY_SEPARATOR .
                        $fileForParam;
                }
            }
        }
        $this->request->setParams($params);
        return $return;
    }

    private function addressFields(): array
    {
        $searchCriteriaBuilder = $this->searchCriteriaBuilder;
        $store = $this->filterBuilder
            ->setField(FieldsInterface::STORE_ID)
            ->setValue($this->storeManager->getStore()->getId())
            ->setConditionType('in')
            ->create();
        $active = $this->filterBuilder
            ->setField(FieldsInterface::IS_ACTIVE)
            ->setValue(1)
            ->setConditionType('eq')
            ->create();
        $addressType = $this->filterBuilder
            ->setField(FieldsInterface::ATTRIBUTE_TYPE)
            ->setValue(CustomAttributes::ADDRESS_ENTITY)
            ->setConditionType('eq')
            ->create();
        $searchCriteriaBuilder
            ->addFilters([$addressType]);
        $searchCriteriaBuilder
            ->addFilters([$active]);
        $searchCriteriaBuilder
            ->addFilters([$store]);
        $searchCriteria = $searchCriteriaBuilder->create();
        $list = $this->fieldsRepository->getList($searchCriteria);
        $items = $list->getItems();
        $fields = [];
        if (empty($items)) {
            return $fields;
        }
        /** @var Fields $item */
        foreach ($items as $item) {
            $fields[] = $item->getAttributeCode();
        }
        return $fields;
    }
}