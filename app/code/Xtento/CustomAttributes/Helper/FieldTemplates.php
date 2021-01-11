<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-01-11T23:16:36+00:00
 * File:          app/code/Xtento/CustomAttributes/Helper/FieldTemplates.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Helper;

use Xtento\CustomAttributes\Model\CustomAttributes;
use Magento\Customer\Model\Customer;
use Magento\Framework\DB\Ddl\Table;

class FieldTemplates
{
    const DEFAULT_ATTRIBUTE_DATA = [
        'attribute_code'=> 'zzzz_2',
        'backend_type' => 'varchar',
        'frontend_class' => '',
        'label' => 'Customerx1',
        'input' => 'text',
        'type' => 'varchar',
        'source' => '',
        'required' => false,
        'position' => 0,
        'formElement' => 'input',
        'is_user_defined' => true,
        'visible' => true,
        'system' => false,
        'is_used_in_grid' => false,
        'is_visible_in_grid' => false,
        'is_filterable_in_grid' => false,
        'is_searchable_in_grid' => false,
        'backend' => ''
    ];

    /*const TEMP_FIELDS = [
        // The address fields are available for modifications only change the key
        CustomAttributes::ADDRESS_ENTITY => [
            'customer_address_f1' => [
                // need to change customer_address_f1 with actual field in real life
                self::FIELD_IDENTIFIER => 'customer_address_f1',
                'label' => 'Customer address custom1',
                self::FIELD_VALUES => [
                    self::AVAILABLE_ON => self::AVAILABLE_ON_BOTH,
                ],
                self::DB_DEFINITION => [

                ]
            ]
        ],
        Customer::ENTITY => [
            'customer_f1' => [
                self::FIELD_IDENTIFIER => 'customer_f1',
                'label' => 'Customer f11',
                self::FIELD_VALUES => [
                    self::AVAILABLE_ON => self::AVAILABLE_ON_BOTH,
                ]

            ],
            'customer_f2' => [
                self::FIELD_IDENTIFIER => 'customer_f2',
                'label' => 'Customer f21',
                'validation' => [
                    'required-entry' => true
                ],
                self::FIELD_VALUES => [
                    self::AVAILABLE_ON => self::AVAILABLE_ON_BOTH,
                ]
            ],
        ],
        CustomAttributes::ORDER_ENTITY => [
            'order_comment' => [
                self::FIELD_IDENTIFIER => 'order_comment',
                'label' => 'Order Comment1',
                'validation' => [
                    'required-entry' => true
                ],

                self::FIELD_VALUES => [
                    self::AVAILABLE_ON => self::AVAILABLE_ON_BOTH,
                    self::FIELD_SPECIFIC_POSITION => 'payments-list',
                ],
                'config' => [
                    'component' => 'Magento_Ui/js/form/element/textarea',
                    'customScope' => 'customCheckoutForm',
                ],
            ],
        ]
    ];*/

    const COMPONENT_BY_TYPE = [
        'text' => 'Magento_Ui/js/form/element/abstract',
        'textarea' => 'Xtento_CustomAttributes/js/form/element/textarea',
        'date' => 'Xtento_CustomAttributes/js/form/element/date',
        'boolean' => 'Xtento_CustomAttributes/js/form/element/select',
        'multiselect' => 'Xtento_CustomAttributes/js/form/element/multiselect',
        'select' => 'Xtento_CustomAttributes/js/form/element/select',
        'checkbox' => 'Xtento_CustomAttributes/js/form/element/checkbox',
        'radio' => 'Xtento_CustomAttributes/js/form/element/radio',
        'multicheckbox' => 'Xtento_CustomAttributes/js/form/element/multiplecheckbox',
        'datetime' => 'Xtento_CustomAttributes/js/form/element/datetime',
        'file' => 'Xtento_CustomAttributes/js/form/element/file',
    ];

    const QUOTE_ATTRIBUTES = [
        'text' => ['type' => Table::TYPE_TEXT, 'length' => 255, 'nullable' => true],
        'textarea' => ['type' => Table::TYPE_TEXT, 'length' => Table::MAX_TEXT_SIZE, 'nullable' => true],
        'date' => ['type' => Table::TYPE_TEXT, 'length' => 255, 'nullable' => true],
        'boolean' => ['type' => Table::TYPE_BOOLEAN,'length' => 255, 'nullable' => false, 'default' => 0],
        'multiselect' => ['type' => Table::TYPE_TEXT, 'length' => 500, 'nullable' => true],
        'select' => ['type' => Table::TYPE_INTEGER, 'length' => 255, 'nullable' => true],
        'file' => ['type' => Table::TYPE_TEXT, 'length' => 255, 'nullable' => true],
    ];

    const ORDER_ATTRIBUTES = [
        'text' => [
            'type' => Table::TYPE_TEXT, 'length' => 255, 'nullable' => true, 'grid' => true
        ],
        'textarea' => [
            'type' => Table::TYPE_TEXT, 'length' => Table::MAX_TEXT_SIZE, 'nullable' => true, 'grid' => true
        ],
        'date' => [
            'type' => Table::TYPE_TEXT, 'length' => 255, 'nullable' => true, 'grid' => true
        ],
        'boolean' => [
            'type' => Table::TYPE_BOOLEAN,'length' => 255, 'nullable' => false, 'default' => 0, 'grid' => true
        ],
        'multiselect' => [
            'type' => Table::TYPE_TEXT, 'length' => 500, 'nullable' => true, 'grid' => true
        ],
        'select' => [
            'type' => Table::TYPE_INTEGER, 'length' => 255, 'nullable' => true, 'grid' => true
        ],
        'file' => [
            'type' => Table::TYPE_TEXT, 'length' => 255, 'nullable' => true, 'grid' => true
        ],
    ];
}
