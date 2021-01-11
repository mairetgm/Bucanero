<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2020-04-20T09:25:25+00:00
 * File:          app/code/Xtento/CustomAttributes/Plugin/Eav/Model/Entity/AttributePlugin.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Plugin\Eav\Model\Entity;

use Xtento\CustomAttributes\Plugin\Eav\Model\AbstractAttributePlugin;

class AttributePlugin extends AbstractAttributePlugin
{
    /**
     * Check if is one of our attributes, if yes, check if it's required in the frontend
     *
     * @param \Magento\Eav\Model\Attribute $subject
     * @param callable $proceed
     */
    public function aroundGetIsRequired(\Magento\Eav\Model\Entity\Attribute $subject, callable $proceed)
    {
        $this->checkAttributes($subject);
        return $proceed();
    }
}