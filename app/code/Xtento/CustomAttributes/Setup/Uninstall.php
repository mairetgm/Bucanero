<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2020-07-25T19:34:39+00:00
 * File:          app/code/Xtento/CustomAttributes/Setup/Uninstall.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;
use Xtento\CustomAttributes\Model\CustomAttributes;
use Magento\Customer\Model\Customer;
use Magento\Framework\Setup\ModuleContextInterface;


/**
 * Class Uninstall
 * @package Xtento\CustomAttributes\Setup
 */
class Uninstall implements UninstallInterface
{
    private $eavSetupFactory;

    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create();

        //$eavSetup->removeAttribute(Customer::ENTITY, 'customer_f1');

        $setup->endSetup();
    }
}
