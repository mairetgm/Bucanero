<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-01-12T17:30:34+00:00
 * File:          app/code/Xtento/CustomAttributes/Setup/InstallData.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Setup;

use Xtento\CustomAttributes\Helper\Data;
use Xtento\CustomAttributes\Model\CustomAttributes;
use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Quote\Setup\QuoteSetupFactory;
use Magento\Sales\Setup\SalesSetup;
use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Framework\DB\Ddl\Table;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;

/**
 * Class InstallData
 * @package Xtento\CustomAttributes\Setup
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;

    /**
     * @var SalesSetupFactory
     */
    private $salesSetupFactory;

    /**
     * @var QuoteSetupFactory
     */
    private $quoteSetupFactory;

    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;

    /**
     * InstallData constructor.
     * @param CustomerSetupFactory $customerSetupFactory
     * @param SalesSetupFactory $salesSetupFactory
     * @param QuoteSetupFactory $quoteSetupFactory
     */
    public function __construct(
        CustomerSetupFactory $customerSetupFactory,
        SalesSetupFactory $salesSetupFactory,
        QuoteSetupFactory $quoteSetupFactory,
        AttributeSetFactory $attributeSetFactory
    ) {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->salesSetupFactory    = $salesSetupFactory;
        $this->quoteSetupFactory    = $quoteSetupFactory;
        $this->attributeSetFactory  = $attributeSetFactory;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    )
    {
//        foreach (Data::TEMP_FIELDS[CustomAttributes::ADDRESS_ENTITY] as $field) {
//            $this->addCustomerAddressFields($setup, $field);
//        }
//
//        foreach (Data::TEMP_FIELDS[Customer::ENTITY] as $field) {
//            $this->addCustomerFields($setup, $field);
//        }
//
//        foreach (Data::TEMP_FIELDS[CustomAttributes::ORDER_ENTITY] as $field) {
//            $this->addOrderFields($setup, $field);
//        }
    }

    public function addCustomerAddressFields($setup, $field)
    {

        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

        $attributeData = [
            'label' => 'Label',
            'input' => 'text',
            'type' => 'varchar',
            'source' => '',
            'required' => false,
            'position' => 0,
            'visible' => true,
            'system' => false,
            'is_used_in_grid' => false,
            'is_visible_in_grid' => false,
            'is_filterable_in_grid' => false,
            'is_searchable_in_grid' => false,
            'backend' => ''
        ];

        $attributeData = array_replace_recursive($attributeData, $field);

//        $customerSetup->update
        $customerSetup->addAttribute(CustomAttributes::ADDRESS_ENTITY, $field[Data::FIELD_IDENTIFIER], $attributeData);

        $attribute = $customerSetup->getEavConfig()
            ->getAttribute(CustomAttributes::ADDRESS_ENTITY, $field[Data::FIELD_IDENTIFIER])
            ->addData(CustomAttributes::USED_IN);

        $attribute->save();

        $quoteInstaller = $this->quoteSetupFactory->create(
            ['resourceName' => 'quote_setup', 'setup' => $setup]
        );

        $salesInstaller = $this->salesSetupFactory->create(
            ['resourceName' => 'sales_setup', 'setup' => $setup]
        );

        $quoteInstaller->addAttribute(
            'quote_address',
            $field[Data::FIELD_IDENTIFIER],
            ['type' => Table::TYPE_TEXT, 'length' => 255, 'nullable' => true]
        );

        $salesInstaller->addAttribute(
            'order_address',
            $field[Data::FIELD_IDENTIFIER],
            ['type' => Table::TYPE_TEXT, 'length' => 255, 'nullable' => true, 'grid' => true]
        );
    }

    public function addCustomerFields($setup, $field)
    {

        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

        $attributeData = [
            'label' => 'Customer',
            'input' => 'text',
            'type' => 'varchar',
            'source' => '',
            'required' => false,
            'position' => 0,
            'visible' => true,
            'system' => false,
            'is_used_in_grid' => false,
            'is_visible_in_grid' => false,
            'is_filterable_in_grid' => false,
            'is_searchable_in_grid' => false,
            'backend' => ''
        ];

        $attributeData = array_replace_recursive($attributeData, $field);

        $customerSetup->addAttribute(Customer::ENTITY, $field[Data::FIELD_IDENTIFIER], $attributeData);

        $attribute = $customerSetup->getEavConfig()
            ->getAttribute(Customer::ENTITY, $field[Data::FIELD_IDENTIFIER])
            ->addData(CustomAttributes::USED_IN);

        $attribute->save();

        $quoteInstaller = $this->quoteSetupFactory->create(
            ['resourceName' => 'quote_setup', 'setup' => $setup]
        );

        $salesInstaller = $this->salesSetupFactory->create(
            ['resourceName' => 'sales_setup', 'setup' => $setup]
        );

        $quoteInstaller->addAttribute(
            'quote_address',
            $field[Data::FIELD_IDENTIFIER],
            ['type' => Table::TYPE_TEXT, 'length' => 255, 'nullable' => true]
        );

        $salesInstaller->addAttribute(
            'order_address',
            $field[Data::FIELD_IDENTIFIER],
            ['type' => Table::TYPE_TEXT, 'length' => 255, 'nullable' => true, 'grid' => true]
        );
    }

    public function addOrderFields($setup, $field)
    {

        $quoteInstaller = $this->quoteSetupFactory->create(
            ['resourceName' => 'quote_setup', 'setup' => $setup]
        );
        /** @var SalesSetup $salesInstaller */
        $salesInstaller = $this->salesSetupFactory->create(
            ['resourceName' => 'sales_setup', 'setup' => $setup]
        );

        $quoteInstaller->addAttribute(
            'quote',
            $field[Data::FIELD_IDENTIFIER],
            ['type' => Table::TYPE_TEXT, 'length' => 255, 'nullable' => true]
        );

        $salesInstaller->addAttribute(
            'order',
            $field[Data::FIELD_IDENTIFIER],
            ['type' => Table::TYPE_TEXT, 'length' => 255, 'nullable' => true, 'grid' => true]
        );
    }
}
