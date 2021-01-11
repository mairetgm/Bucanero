<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-01-12T17:30:34+00:00
 * File:          app/code/Xtento/CustomAttributes/Api/FieldsRepositoryInterface.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Api;

use Xtento\CustomAttributes\Api\Data\FieldsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface FieldsRepositoryInterface
{
    public function save(FieldsInterface $field);

    public function getById($fieldId);

    public function getList(SearchCriteriaInterface $searchCriteria);

    public function delete(FieldsInterface $field);

    public function deleteById($fieldId);

    public function saveAndDeleteById($fieldId);

    public function saveAndDelete(FieldsInterface $field);
}
