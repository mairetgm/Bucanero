<?php

/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\OrdersExportTool\Observer;

class CheckoutSubmitAllAfter implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Wyomind\OrdersExportTool\Model\ProfilesFactory 
     */
    protected $_modelProfiles;
    public function __construct(\Wyomind\OrdersExportTool\Helper\Delegate $wyomind, \Wyomind\OrdersExportTool\Model\ProfilesFactory $modelProfilesFactory)
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        $this->_modelProfiles = $modelProfilesFactory->create();
    }
    /**
     * Execute on or several profile on event order submit after all
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        // Add the profile id to each items (export_to column)
        $order = $observer->getEvent()->getData('order');
        foreach ($order->getItems() as $item) {
            $id = $item->getProductId();
            try {
                $product = $this->_productRepository->get($item->getSku());
            } catch (\Exception $e) {
                $id = $item->getProductId();
                $product = $this->_productRepository->getById($id);
            }
            if (isset($product)) {
                $profileId = $product->getExportTo();
                if ($profileId) {
                    $item->setExportTo($profileId);
                    $item->save();
                }
            }
        }
        $collection = $this->_modelProfiles->getCollection()->searchProfiles(explode(',', $this->_helperData->getStoreConfig("ordersexporttool/advanced/execute_on_checkout")));
        foreach ($collection as $profile) {
            if ($profile->getId()) {
                $profile->generate();
            }
        }
    }
}