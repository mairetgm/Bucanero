<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-03-31T20:11:26+00:00
 * File:          app/code/Xtento/CustomAttributes/Model/FieldsRepository.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Model;

use Xtento\CustomAttributes\Api\Data\FieldsInterface;
use Xtento\CustomAttributes\Api\FieldsRepositoryInterface;
use Xtento\CustomAttributes\Model\ResourceModel\Collection\Collection;
use Xtento\CustomAttributes\Model\ResourceModel\Collection\CollectionFactory;
use Xtento\CustomAttributes\Api\Data\FieldsSearchResultsInterfaceFactory;
use Xtento\CustomAttributes\Helper\Data;
use Xtento\CustomAttributes\Model\ResourceModel\Fields as FieldsResourceModel;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException as Exception;
use Magento\Framework\Message\ManagerInterface;

/**
 * Class FieldsRepository
 * @package Xtento\CustomAttributes\Model
 */
class FieldsRepository implements FieldsRepositoryInterface
{
    /**
     * @var array
     */
    private $instances = [];

    /**
     * @var FieldsResourceModel
     */
    private $resource;

    /**
     * @var FieldsInterface
     */
    private $fields;

    /**
     * @var FieldsFactory
     */
    private $fieldsFactory;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**z
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var FieldsSearchResultsInterfaceFactory
     */
    private $fieldsSearchResultsInterfaceFactory;

    /**
     * FieldsRepository constructor.
     *
     * @param FieldsResourceModel $resource
     * @param FieldsInterface $fields
     * @param FieldsFactory $fieldsFactory
     * @param ManagerInterface $messageManager
     * @param CollectionFactory $collectionFactory
     * @param FieldsSearchResultsInterfaceFactory $fieldsSearchResultsInterfaceFactory
     */
    public function __construct(
        FieldsResourceModel $resource,
        FieldsInterface $fields,
        FieldsFactory $fieldsFactory,
        ManagerInterface $messageManager,
        CollectionFactory $collectionFactory,
        FieldsSearchResultsInterfaceFactory $fieldsSearchResultsInterfaceFactory
    ) {
        $this->resource = $resource;
        $this->fields = $fields;
        $this->fieldsFactory = $fieldsFactory;
        $this->messageManager = $messageManager;
        $this->collectionFactory = $collectionFactory;
        $this->fieldsSearchResultsInterfaceFactory = $fieldsSearchResultsInterfaceFactory;
    }

    /**
     * @param FieldsInterface $field
     *
     * @return FieldsInterface
     * @throws \Exception
     */
    public function save(FieldsInterface $field)
    {
        $this->resource->save($field);

        return $field;
    }

    /**
     * @param $FieldId
     *
     * @return array
     */
    public function getById($FieldId)
    {
        if (!isset($this->instances[$FieldId])) {
            $field = $this->fieldsFactory->create();
            $this->resource->load($field, $FieldId);
            $this->instances[$FieldId] = $field;
        }
        return $this->instances[$FieldId];
    }

    /**
     * @param FieldsInterface $field
     *
     * @return bool
     * @throws \Exception
     */
    public function delete(FieldsInterface $field)
    {
        $id = $field->getId();
        try {
            unset($this->instances[$id]);
            $this->resource->delete($field);
        } catch (\Exception $e) {
            $this->messageManager
                ->addExceptionMessage($e, __('There was an error while deleting the field.'));
        }
        unset($this->instances[$id]);
        return true;
    }

    /**
     * @param $fieldId
     *
     * @return bool
     * @throws \Exception
     */
    public function deleteById($fieldId)
    {
        $field = $this->getById($fieldId);
        return $this->delete($field);
    }

    /**
     * @param FieldsInterface $field
     *
     * @return bool
     * @throws \Exception
     */
    public function saveAndDelete(FieldsInterface $field)
    {
        $field->setData(Data::ACTION, Data::DELETE);
        $this->save($field);
        return true;
    }

    /**
     * @param $fieldId
     *
     * @return bool
     * @throws \Exception
     */
    public function saveAndDeleteById($fieldId)
    {
        $field = $this->getById($fieldId);
        return $this->saveAndDelete($field);
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return mixed
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->collectionFactory->create();

        $this->addFiltersToCollection($searchCriteria, $collection);
        $this->addSortOrdersToCollection($searchCriteria, $collection);
        $this->addPagingToCollection($searchCriteria, $collection);

        $collection->load();

        return $this->buildSearchResult($searchCriteria, $collection);
    }

    private function addFiltersToCollection(SearchCriteriaInterface $searchCriteria, Collection $collection)
    {

        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            $fields = $conditions = [];
            foreach ($filterGroup->getFilters() as $filter) {
                $fields[] = $filter->getField();
                $conditions[] = [$filter->getConditionType() => $filter->getValue()];
            }

            if ($filter->getField() == FieldsInterface::STORE_ID) {
                $collection->addStoreFilter($filter->getValue());
                continue;
            }

            $collection->addFieldToFilter($fields, $conditions);
        }
    }

    private function addSortOrdersToCollection(SearchCriteriaInterface $searchCriteria, Collection $collection)
    {
        foreach ((array)$searchCriteria->getSortOrders() as $sortOrder) {
            $direction = $sortOrder->getDirection() == SortOrder::SORT_ASC ? 'asc' : 'desc';
            $collection->addOrder($sortOrder->getField(), $direction);
        }
    }

    private function addPagingToCollection(SearchCriteriaInterface $searchCriteria, Collection $collection)
    {
        $collection->setPageSize($searchCriteria->getPageSize());
        $collection->setCurPage($searchCriteria->getCurrentPage());
    }

    private function buildSearchResult(SearchCriteriaInterface $searchCriteria, Collection $collection)
    {
        $searchResults = $this->fieldsSearchResultsInterfaceFactory->create();

        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }
}
