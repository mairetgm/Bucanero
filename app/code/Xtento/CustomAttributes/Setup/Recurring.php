<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-01-12T17:30:34+00:00
 * File:          app/code/Xtento/CustomAttributes/Setup/Recurring.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Setup;

use Xtento\CustomAttributes\Helper\Data;
use Xtento\CustomAttributes\Model\CustomAttributes;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Customer\Model\Customer;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Quote\Setup\QuoteSetupFactory;
use Magento\Sales\Setup\SalesSetupFactory;

/**
 * Class Recurring
 * @package Xtento\CustomAttributes\Setup
 */
class Recurring implements InstallSchemaInterface
{
    /**
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;

    private $eavSetupFactory;

    private $installData;

    private $moduleDataSetupInterface;

    /**
     * @var SalesSetupFactory
     */
    private $salesSetupFactory;

    /**
     * @var QuoteSetupFactory
     */
    private $quoteSetupFactory;

    public function __construct(
        CustomerSetupFactory $customerSetupFactory,
        EavSetupFactory $eavSetupFactory,
        InstallData $installData,
        ModuleDataSetupInterface $moduleDataSetupInterface,
        SalesSetupFactory $salesSetupFactory,
        QuoteSetupFactory $quoteSetupFactory
    ) {
        $this->customerSetupFactory     = $customerSetupFactory;
        $this->eavSetupFactory          = $eavSetupFactory;
        $this->installData              = $installData;
        $this->moduleDataSetupInterface = $moduleDataSetupInterface;
        $this->salesSetupFactory        = $salesSetupFactory;
        $this->quoteSetupFactory        = $quoteSetupFactory;
    }

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
//        $setup->startSetup();
//
//        /** @var EavSetup $eavSetup */
//        $eavSetup = $this->eavSetupFactory->create();
//
//        foreach (Data::TEMP_FIELDS[CustomAttributes::ADDRESS_ENTITY] as $field) {
//            $this->removeCustomerAddressFields($setup ,$eavSetup, $field);
//        }
//
//        foreach (Data::TEMP_FIELDS[Customer::ENTITY] as $field) {
//            $this->removeCustomerFields($setup, $eavSetup, $field);
//        }
//
//        foreach (Data::TEMP_FIELDS[CustomAttributes::ORDER_ENTITY] as $field) {
//            $this->removeOrderFields($setup, $field);
//        }
//
//        $setup->endSetup();
//
//        $this->installData->install($this->moduleDataSetupInterface, $context);
    }

    public function removeCustomerAddressFields($setup, $eavSetup, $field)
    {
        $eavSetup->removeAttribute(CustomAttributes::ADDRESS_ENTITY, $field[Data::FIELD_IDENTIFIER]);

        $setup->getConnection()->dropColumn(
            $setup->getTable('quote_address'),
            $field[Data::FIELD_IDENTIFIER]
        );

        $setup->getConnection()->dropColumn(
            $setup->getTable('sales_order_address'),
            $field[Data::FIELD_IDENTIFIER]
        );
    }

    public function removeCustomerFields($setup, $eavSetup, $field)
    {
        $eavSetup->removeAttribute(Customer::ENTITY, $field[Data::FIELD_IDENTIFIER]);

        $setup->getConnection()->dropColumn(
            $setup->getTable('quote_address'),
            $field[Data::FIELD_IDENTIFIER]
        );

        $setup->getConnection()->dropColumn(
            $setup->getTable('sales_order_address'),
            $field[Data::FIELD_IDENTIFIER]
        );
    }

    public function removeOrderFields($setup, $field)
    {
        $setup->getConnection()->dropColumn(
            $setup->getTable('quote'),
            $field[Data::FIELD_IDENTIFIER]
        );

        $setup->getConnection()->dropColumn(
            $setup->getTable('sales_order'),
            $field[Data::FIELD_IDENTIFIER]
        );
    }
}
