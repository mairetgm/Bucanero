<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-01-11T23:10:03+00:00
 * File:          app/code/Xtento/CustomAttributes/Model/AccountDelegationFactory.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Model;

use Magento\Framework\ObjectManagerInterface;

class AccountDelegationFactory implements FactoryInterface
{
    private $objectManager = null;

    private $instanceName = null;

    /**
     * AccountDelegationFactory constructor.
     *
     * @param ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
        if (class_exists(\Magento\Customer\Model\Delegation\AccountDelegation::class)) {
            $this->instanceName = \Magento\Customer\Model\Delegation\AccountDelegation::class;
        }
    }

    public function create(array $data = [])
    {
        if (is_null($this->instanceName)) {
            return false;
        }
        return $this->objectManager->create($this->instanceName, $data);
    }
}