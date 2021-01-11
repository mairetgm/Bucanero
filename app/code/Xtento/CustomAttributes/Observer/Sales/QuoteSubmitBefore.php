<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2020-05-15T12:24:17+00:00
 * File:          app/code/Xtento/CustomAttributes/Observer/Sales/QuoteSubmitBefore.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Observer\Sales;

use Xtento\CustomAttributes\Helper\Data;
use Xtento\CustomAttributes\Helper\FieldTemplates;
use Xtento\CustomAttributes\Model\CustomAttributes;
use Xtento\CustomAttributes\Helper\Data as DataHelper;
use Magento\Customer\Model\Customer;
use Magento\Quote\Model\Quote;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\QuoteRepository;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

/**
 * Class QuoteSubmitBefore
 * @package Xtento\Checkoutaddressfields\Observer\Sales
 */
class QuoteSubmitBefore implements ObserverInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var QuoteRepository
     */
    private $quoteRepository;

    /**
     * @var DataHelper
     */
    private $dataHelper;
    private $timezone;

    /**
     * QuoteSubmitBefore constructor.
     * @param QuoteRepository $quoteRepository
     * @param LoggerInterface $logger
     * @param DataHelper $dataHelper
     */
    public function __construct(
        QuoteRepository $quoteRepository,
        LoggerInterface $logger,
        DataHelper $dataHelper,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->logger          = $logger;
        $this->dataHelper      = $dataHelper;
        $this->timezone        = $timezone;
    }

    /**
     * Execute observer
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(
        Observer $observer
    ) {

        /** @var Order $order */
        $order = $observer->getOrder();

        /** use to break checkout */
        $quote = $this->quoteRepository->get($order->getQuoteId());
        try {
            $this->orderBillingAddressFields($order, $quote);
            $this->orderShippingAddressFields($order, $quote);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }

    /**
     * @param $order
     * @param $quote
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function orderBillingAddressFields($order, $quote)
    {
        $fieldHelperData = $this->dataHelper->createFields();

        foreach ($fieldHelperData as $key => $fieldData) {
            if (empty($fieldData)) {
                return $this;
            }

            foreach ($fieldData as $field) {
                if ($key == CustomAttributes::ORDER_ENTITY) {
                    $value = $quote->getData($field[Data::FIELD_IDENTIFIER]);
                    $order->setData((string)$field[Data::FIELD_IDENTIFIER], $value);
                    continue;
                }

                $value = $quote->getBillingAddress()->getData($field[Data::FIELD_IDENTIFIER]);
                $order->getBillingAddress()->setData((string)$field[Data::FIELD_IDENTIFIER], $value);
            }
        }

        return $this;
    }

    /**
     * @param $order
     * @param $quote
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function orderShippingAddressFields($order, $quote)
    {
        $fieldHelperData = $this->dataHelper->createFields();

        $orderShippingAddress = $order->getShippingAddress();
        if(!$orderShippingAddress){
            return $this;
        }

        foreach ($fieldHelperData as $key => $fieldData) {
            if (empty($fieldData)) {
                return $this;
            }

            foreach ($fieldData as $field) {
                if ($key == CustomAttributes::ORDER_ENTITY) {
                    if ($field['config']['component'] == 'Xtento_CustomAttributes/js/form/element/date'){
                        $value = $this->timezone->date($quote->getData($field[Data::FIELD_IDENTIFIER]))->format('Y-m-d');
                    } else {
                        $value = $quote->getData($field[Data::FIELD_IDENTIFIER]);
                    }

                    $order->setData((string)$field[Data::FIELD_IDENTIFIER], $value);
                    continue;
                }

                $value = $quote->getShippingAddress()->getData($field[Data::FIELD_IDENTIFIER]);
                $orderShippingAddress->setData((string)$field[Data::FIELD_IDENTIFIER], $value);

                $order->setData((string)$field[Data::FIELD_IDENTIFIER], $value);
            }
        }

        return $this;
    }
}
