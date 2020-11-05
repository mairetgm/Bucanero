<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_OrderStatus
 */


namespace Amasty\OrderStatus\Model\Order\Plugin;

use Amasty\OrderStatus\Model\ResourceModel\Status\CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Registry;

class Status
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var CollectionFactory
     */
    private $amastyStatusCollectionFactory;

    /**
     * @var Registry
     */
    private $coreRegistry;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CollectionFactory $collectionFactory,
        Registry $coreRegistry
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->amastyStatusCollectionFactory = $collectionFactory;
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * @param $subject
     * @param $result
     *
     * @return mixed
     */
    public function afterLoad($subject, $result)
    {
        if (!$result->getLabel()) {
            $code = $subject->getStatus();
            $statusesCollection = $this->amastyStatusCollectionFactory->create();

            if ($code && $statusesCollection->getSize() > 0) {
                $result = $this->rebuildStatues($statusesCollection, $code);
            }
        }

        return $result;
    }

    /**
     * @param \Magento\Sales\Model\Order\Status $subject
     * @param array $result
     *
     * @return array
     */
    public function afterGetStoreLabels($subject, $result)
    {
        if (!$subject->getStatus()) {
            $amastyStatus = $this->coreRegistry->registry('amorderstatus_history_status');
            $storeId = $this->coreRegistry->registry('amorderstatus_store_id');
            $orderState = $this->coreRegistry->registry('amorderstatus_state');
            $hideState = $this->scopeConfig->getValue('amostatus/general/hide_state');

            if ($orderState) {
                $result[$storeId] = ($hideState ? '' : ucfirst($orderState) . ': ') . $amastyStatus->getStatus();
            }
        }

        return $result;
    }

    public function rebuildStatues($statusesCollection, $code)
    {
        $hideState = $this->scopeConfig->getValue('amostatus/general/hide_state');
        $statusLabel = '';

        foreach ($statusesCollection->getStates() as $state) {
            foreach ($statusesCollection as $status) {
                if ($status->getData('is_active') && !$status->getData('is_system')) {
                    // checking if we should apply status to the current state
                    $parentStates = [];

                    if ($status->getParentState()) {
                        $parentStates = explode(',', $status->getParentState());
                    }

                    if (!$parentStates || in_array($state['value'], $parentStates)) {
                        $elementName = $state['value'] . '_' . $status->getAlias();

                        if ($code == $elementName) {
                            $statusLabel = ($hideState ? '' : $state['label'] . ': ')
                                . __($status->getStatus());

                            break(2);
                        }
                    }
                }
            }
        }

        $status->setLabel($statusLabel);
        $status->setStoreLabel($statusLabel);
        return $status;
    }
}
