<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-01-12T17:30:34+00:00
 * File:          app/code/Xtento/CustomAttributes/Setup/UpgradeData.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Setup;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

/**
 * Class UpgradeData
 * @package Xtento\CustomAttributes\Setup
 */
class UpgradeData implements UpgradeDataInterface
{
    private $eavSetupFactory;

    private $uninstall;

    public function __construct(
        EavSetupFactory $eavSetupFactory,
        Uninstall $uninstall
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->uninstall = $uninstall;
    }

    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {

    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        // TODO: Implement upgrade() method.
    }
}
