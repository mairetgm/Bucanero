<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2020-04-09T14:46:50+00:00
 * File:          app/code/Xtento/CustomAttributes/Plugin/Customer/Model/AttributeMetadataResolverPlugin.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Plugin\Customer\Model;

use Xtento\CustomAttributes\Plugin\Eav\Model\AbstractAttributePlugin;

class AttributeMetadataResolverPlugin extends AbstractAttributePlugin
{
    /**
     * @param \Magento\Customer\Model\AttributeMetadataResolver $subject
     * @param callable $proceed
     * @param \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute
     * @param \Magento\Eav\Model\Entity\Type $entityType
     * @param bool $allowToShowHiddenAttributes
     */
    public function aroundGetAttributesMeta(
        \Magento\Customer\Model\AttributeMetadataResolver $subject,
        callable $proceed,
        \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute,
        \Magento\Eav\Model\Entity\Type $entityType,
        bool $allowToShowHiddenAttributes
    ) {
        $meta = $proceed($attribute, $entityType, $allowToShowHiddenAttributes);

        if (!$this->checkAttributes($attribute)) {
            $meta['arguments']['data']['config']['visible'] = false;
        }

        return $meta;
    }
}