<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2020-11-30T10:01:57+00:00
 * File:          app/code/Xtento/CustomAttributes/Plugin/Sales/Order/Pdf/Invoice.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Plugin\Sales\Order\Pdf;

use Magento\Framework\App\ProductMetadataInterface;
use Xtento\CustomAttributes\Helper\Data;
use Xtento\CustomAttributes\Api\Data\FieldsInterface;
use Xtento\CustomAttributes\Model\FieldProcessor\AddValue;
use Xtento\CustomAttributes\Block\Adminhtml\Sales\Order\View\OrderAttributesView;
use Xtento\CustomAttributes\Block\Adminhtml\Sales\Order\Create\OrderAttributes;
use Magento\Sales\Model\Order\Pdf\Invoice as MagentoInvoice;
use Magento\Framework\Api\FilterBuilder;
use Magento\Payment\Helper\Data as DataPayment;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Filesystem;
use Magento\Sales\Model\Order\Pdf\Config;
use Magento\Sales\Model\Order\Pdf\Total\Factory;
use Magento\Sales\Model\Order\Pdf\ItemsFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Locale\ResolverInterface;

class Invoice extends MagentoInvoice
{
    private $orderAttributesView;

    private $addValue;

    private $dataHelper;

    private $filterBuilder;

    public function __construct(
        DataPayment $paymentData,
        StringUtils $string,
        ScopeConfigInterface $scopeConfig,
        Filesystem $filesystem,
        Config $pdfConfig,
        Factory $pdfTotalFactory,
        ItemsFactory $pdfItemsFactory,
        TimezoneInterface $localeDate,
        StateInterface $inlineTranslation,
        Renderer $addressRenderer,
        StoreManagerInterface $storeManager,
        ResolverInterface $localeResolver,
        OrderAttributesView $orderAttributesView,
        FilterBuilder $filterBuilder,
        AddValue $addValue,
        Data $dataHelper,
        ProductMetadataInterface $productMetadata,
        \Magento\Store\Model\App\Emulation $appEmulation,
        array $data = []
    ) {
        $this->filterBuilder       = $filterBuilder;
        $this->dataHelper          = $dataHelper;
        $this->addValue            = $addValue;
        $this->orderAttributesView = $orderAttributesView;

        if (
            (version_compare($productMetadata->getVersion(), '2.3.6', '>=') && version_compare($productMetadata->getVersion(), '2.4.0', '<')) ||
            version_compare($productMetadata->getVersion(), '2.4.1', '>=')) {
            parent::__construct(
                $paymentData,
                $string,
                $scopeConfig,
                $filesystem,
                $pdfConfig,
                $pdfTotalFactory,
                $pdfItemsFactory,
                $localeDate,
                $inlineTranslation,
                $addressRenderer,
                $storeManager,
                $appEmulation,
                $data
            );
            $this->_localeResolver = $localeResolver; // Removed in 2.3.6, set it again for usage in this class
        } else {
            parent::__construct(
                $paymentData,
                $string,
                $scopeConfig,
                $filesystem,
                $pdfConfig,
                $pdfTotalFactory,
                $pdfItemsFactory,
                $localeDate,
                $inlineTranslation,
                $addressRenderer,
                $storeManager,
                $localeResolver,
                $data
            );
        }
    }

    public function getPdf($invoices = [])
    {
        $this->_beforeGetPdf();
        $this->_initRenderer('invoice');

        $pdf = new \Zend_Pdf();
        $this->_setPdf($pdf);

        $style = new \Zend_Pdf_Style();
        $this->_setFontBold($style, 10);

        foreach ($invoices as $invoice) {
            if ($invoice->getStoreId()) {
                $this->_localeResolver->emulate($invoice->getStoreId());
                $this->_storeManager->setCurrentStore($invoice->getStoreId());
            }
            $page = $this->newPage();
            $order = $invoice->getOrder();
            /* Add image */
            $this->insertLogo($page, $invoice->getStore());
            /* Add address */
            $this->insertAddress($page, $invoice->getStore());
            /* Add head */
            $this->insertOrder(
                $page,
                $order,
                $this->_scopeConfig->isSetFlag(
                    self::XML_PATH_SALES_PDF_INVOICE_PUT_ORDER_ID,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $order->getStoreId()
                )
            );
            /* Add document text and number */
            $this->insertDocumentNumber($page, __('Invoice # ') . $invoice->getIncrementId());

            /* Add custom field*/
            $this->insertCustomField($page, $order);

            /* Add table */
            $this->_drawHeader($page);
            /* Add body */
            foreach ($invoice->getAllItems() as $item) {
                if ($item->getOrderItem()->getParentItem()) {
                    continue;
                }
                /* Draw item */
                $this->_drawItem($item, $page, $order);
                $page = end($pdf->pages);
            }
            /* Add totals */
            $this->insertTotals($page, $invoice);
            if ($invoice->getStoreId()) {
                $this->_localeResolver->revert();
            }
        }
        $this->_afterGetPdf();
        return $pdf;
    }

    public function insertCustomField(&$page, $order)
    {
        $this->fieldTypeCustomer($page, $order);
        $this->fieldTypeOrder($page, $order);
        $this->fieldTypeAddressShipping($page, $order);
        $this->fieldTypeAddressBilling($page, $order);
    }

    private function fieldTypeCustomer(&$page, $order)
    {
        $filter = [
            $this->filterBuilder
                ->setField(FieldsInterface::SHOW_ON_PDF)
                ->setValue(1)
                ->setConditionType('eq')
                ->create(),
        ];

        $dataHelper = $this->dataHelper;
        $fieldsList = $dataHelper->createFields($filter, OrderAttributes::ADMIN_ORDER_LOCATION);

        $this->y -= -8;

        if (isset($fieldsList['customer'])) {
            $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
            $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
            $page->setLineWidth(0.5);
            $page->drawRectangle(25, $this->y, 570, $this->y - 20);
            $yDraw = 33;

            foreach ($fieldsList['customer'] as $field) {
                $yDraw += 12;
            }

            $page->setFillColor(new \Zend_Pdf_Color_Rgb(1, 1, 1));
            $page->drawRectangle(25, $this->y - 20, 570, $this->y - $yDraw);
            $page->setFillColor(new \Zend_Pdf_Color_RGB(0.1, 0.1, 0.1));
            $this->_setFontBold($page, 12);
            $page->drawText(__('Customer'), 35, $this->y - 13, 'UTF-8');
            $this->_setFontRegular($page, 10);
            $y = 33;

            $dataHelper = $this->dataHelper;
            $fieldList = $dataHelper->createFields([], OrderAttributes::ADMIN_ORDER_LOCATION);
            $customerField = $fieldList['customer'];
            if (isset($customerField)) {
                foreach ($customerField as $field) {
                    /** @var \Magento\Eav\Model\Attribute $attribute */
                    $attribute = $field->getData(Data::ATTRIBUTE_DATA);
                    $addValue = $this->addValue;
                    $value = $addValue->addValues($attribute, $order);
                    $valueField = $value->getData('value');

                    $usedInPdf = $field->getData('show_on_pdf');
                    if ($usedInPdf == '0') {
                        continue;
                    }

                    $page->drawText($attribute->getStoreLabel() . ': ' . $valueField, 33, $this->y - $y, 'UTF-8');
                    $y += 15;
                }
            }
            $y += +10;
            $this->y -= $y;
        }
    }

    private function fieldTypeOrder(&$page, $order)
    {
        $filter = [
            $this->filterBuilder
                ->setField(FieldsInterface::SHOW_ON_PDF)
                ->setValue(1)
                ->setConditionType('eq')
                ->create(),
        ];

        $dataHelper = $this->dataHelper;
        $fieldsList = $dataHelper->createFields($filter, OrderAttributes::ADMIN_ORDER_LOCATION);

        $this->y -= -8;

        if (isset($fieldsList['order_field'])) {
            $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
            $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
            $page->setLineWidth(0.5);
            $page->drawRectangle(25, $this->y, 570, $this->y - 20);
            $yDraw = 33;

            foreach ($fieldsList['order_field'] as $field) {
                $yDraw += 12;
            }

            $page->setFillColor(new \Zend_Pdf_Color_Rgb(1, 1, 1));
            $page->drawRectangle(25, $this->y - 20, 570, $this->y - $yDraw);
            $page->setFillColor(new \Zend_Pdf_Color_RGB(0.1, 0.1, 0.1));
            $this->_setFontBold($page, 12);
            $page->drawText(__('Order'), 35, $this->y - 13, 'UTF-8');
            $this->_setFontRegular($page, 10);
            $y = 33;

            $dataHelper = $this->dataHelper;
            $fieldList = $dataHelper->createFields([], OrderAttributes::ADMIN_ORDER_LOCATION);
            $orderField = $fieldList['order_field'];
            if (isset($orderField)) {
                foreach ($orderField as $field) {
                    /** @var \Magento\Eav\Model\Attribute $attribute */
                    $attribute = $field->getData(Data::ATTRIBUTE_DATA);
                    $addValue = $this->addValue;
                    $value = $addValue->addValues($attribute, $order);
                    $valueField = $value->getData('value');

                    $usedInPdf = $field->getData('show_on_pdf');
                    if ($usedInPdf == '0') {
                        continue;
                    }

                    $page->drawText($attribute->getStoreLabel() . ': ' . $valueField, 33, $this->y - $y, 'UTF-8');
                    $y += 15;
                }
            }
            $y += +10;
            $this->y -= $y;
        }
    }

    private function fieldTypeAddressShipping(&$page, $order)
    {
        $filter = [
            $this->filterBuilder
                ->setField(FieldsInterface::SHOW_ON_PDF)
                ->setValue(1)
                ->setConditionType('eq')
                ->create(),
        ];

        $dataHelper = $this->dataHelper;
        $fieldsList = $dataHelper->createFields($filter, OrderAttributes::ADMIN_ORDER_LOCATION);


        $this->y -= -8;

        if (isset($fieldsList['customer_address'])) {
            $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
            $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
            $page->setLineWidth(0.5);
            $page->drawRectangle(25, $this->y, 570, $this->y - 20);
            $yDraw = 33;

            foreach ($fieldsList['customer_address'] as $field) {
                $yDraw += 12;
            }

            $page->setFillColor(new \Zend_Pdf_Color_Rgb(1, 1, 1));
            $page->drawRectangle(25, $this->y - 20, 570, $this->y - $yDraw);
            $page->setFillColor(new \Zend_Pdf_Color_RGB(0.1, 0.1, 0.1));
            $this->_setFontBold($page, 12);
            $page->drawText(__('Customer Shipping Address'), 35, $this->y - 13, 'UTF-8');
            $this->_setFontRegular($page, 10);
            $y = 33;

            $dataHelper = $this->dataHelper;
            $fieldList = $dataHelper->createFields([], OrderAttributes::ADMIN_ORDER_LOCATION);
            $customerShippingAddressField = $fieldList['customer_address'];
            if (isset($customerShippingAddressField)) {
                foreach ($customerShippingAddressField as $field) {
                    /** @var \Magento\Eav\Model\Attribute $attribute */
                    $attribute = $field->getData(Data::ATTRIBUTE_DATA);
                    $addValue = $this->addValue;
                    $value = $addValue->addValues($attribute, $order);
                    $valueField = $value->getData('value');

                    $usedInPdf = $field->getData('show_on_pdf');
                    if ($usedInPdf == '0') {
                        continue;
                    }

                    $page->drawText($attribute->getStoreLabel() . ': ' . $valueField, 33, $this->y - $y, 'UTF-8');
                    $y += 15;
                }
            }
            $y += +10;
            $this->y -= $y;
        }
    }

    private function fieldTypeAddressBilling(&$page, $order)
    {
        $filter = [
            $this->filterBuilder
                ->setField(FieldsInterface::SHOW_ON_PDF)
                ->setValue(1)
                ->setConditionType('eq')
                ->create(),
        ];

        $dataHelper = $this->dataHelper;
        $fieldsList = $dataHelper->createFields($filter, OrderAttributes::ADMIN_ORDER_LOCATION);

        $this->y -= -8;

        if (isset($fieldsList['customer_address'])) {
            $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
            $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
            $page->setLineWidth(0.5);
            $page->drawRectangle(25, $this->y, 570, $this->y - 20);
            $yDraw = 33;

            foreach ($fieldsList['customer_address'] as $field) {
                $yDraw += 12;
            }

            $page->setFillColor(new \Zend_Pdf_Color_Rgb(1, 1, 1));
            $page->drawRectangle(25, $this->y - 20, 570, $this->y - $yDraw);
            $page->setFillColor(new \Zend_Pdf_Color_RGB(0.1, 0.1, 0.1));
            $this->_setFontBold($page, 12);
            $page->drawText(__('Customer Billing Address'), 35, $this->y - 13, 'UTF-8');
            $this->_setFontRegular($page, 10);
            $y = 33;

            $dataHelper = $this->dataHelper;
            $fieldList = $dataHelper->createFields([], OrderAttributes::ADMIN_ORDER_LOCATION);
            $customerBillingAddressField = $fieldList['customer_address'];
            if (isset($customerBillingAddressField)) {
                foreach ($customerBillingAddressField as $field) {
                    /** @var \Magento\Eav\Model\Attribute $attribute */
                    $attribute = $field->getData(Data::ATTRIBUTE_DATA);
                    $addValue = $this->addValue;
                    $value = $addValue->addValues($attribute, $order);
                    $valueField = $value->getData('value');

                    $usedInPdf = $field->getData('show_on_pdf');
                    if ($usedInPdf == '0') {
                        continue;
                    }

                    $page->drawText($attribute->getStoreLabel() . ': ' . $valueField, 33, $this->y - $y, 'UTF-8');
                    $y += 15;
                }
            }
            $y += +6;
            $this->y -= $y;
        }
    }
}
