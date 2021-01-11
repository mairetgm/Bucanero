<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-08-27T08:40:36+00:00
 * File:          app/code/Xtento/CustomAttributes/Model/Api/GuestCustomerOrderFieldsManagement.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Model\Api;

use Xtento\CustomAttributes\Api\Data\OrderCustomerFieldsInterface;
use Xtento\CustomAttributes\Api\GuestCustomerOrderFieldsManagementInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;

class GuestCustomerOrderFieldsManagement implements GuestCustomerOrderFieldsManagementInterface
{
    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @var OrderFieldsManagementInterface
     */
    private $orderCustomerFields;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * GuestCustomerOrderFieldsManagement constructor.
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param OrderCustomerFieldsInterface $orderCustomerFields
     * @param CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        QuoteIdMaskFactory $quoteIdMaskFactory,
        OrderCustomerFieldsInterface $orderCustomerFields,
        CartRepositoryInterface $quoteRepository
    ) {
        $this->quoteIdMaskFactory  = $quoteIdMaskFactory;
        $this->orderCustomerFields = $orderCustomerFields;
        $this->quoteRepository     = $quoteRepository;
    }

    /**
     * @param $cartId
     * @param OrderCustomerFieldsInterface $fields
     * @return null|string
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    public function saveFields(
        $cartId,
        OrderCustomerFieldsInterface $fields
    ) {
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');

        /** @var Quote $quote */
        $quote = $this->quoteRepository->getActive($quoteIdMask->getQuoteId());
        if (!$quote->getItemsCount()) {
            throw new NoSuchEntityException(__('Cart %1 doesn\'t contain products', $cartId));
        }

        $data = $fields->getFields();

        foreach ($data as $key => $field) {
            if ($field !== '') {
                $quote->setData($key, $field);
            }
        }

        try {
            $this->quoteRepository->save($quote);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('The order fields could not be saved'));
        }

        return null;
    }
}