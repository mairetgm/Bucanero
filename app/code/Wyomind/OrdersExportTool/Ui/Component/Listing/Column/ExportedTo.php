<?php

/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\OrdersExportTool\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
/**
 * Render column block in the order grid
 */
class ExportedTo extends Column
{
    protected $_modelProfiles = null;
    public function __construct(\Wyomind\OrdersExportTool\Helper\Delegate $wyomind, ContextInterface $context, UiComponentFactory $uiComponentFactory, \Wyomind\OrdersExportTool\Model\ProfilesFactory $modelProfilesFactory, array $components = [], array $data = [])
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        $this->_modelProfiles = $modelProfilesFactory->create();
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            // load orders
            $orderIds = [];
            foreach ($dataSource['data']['items'] as $item) {
                $orderIds[] = $item['entity_id'];
            }
            $searchCriteria = $this->_searchCriteriaBuilder;
            $searchCriteria->addFilter('entity_id', $orderIds, 'in');
            $collection = $this->_orderRepository->getList($searchCriteria->create());
            $orders = [];
            foreach ($collection as $order) {
                $orders[$order->getEntityId()] = $order;
            }
            foreach ($dataSource['data']['items'] as &$item) {
                $html = null;
                $data = explode(',', $item[$this->getData('name')]);
                $doneIds = [];
                $done = [];
                $collection = $this->_modelProfiles->getCollection()->searchProfiles($data);
                foreach ($collection as $profile) {
                    $doneIds[] = $profile->getId();
                    $done[] = "<span id='orderexported-" . $item['entity_id'] . '-' . $profile->getId() . "'><span class='checkmark'>✔</span>&nbsp;" . $profile->getName() . " <a href='#' onclick='javascript:jQuery(this).parents(\"TD\").eq(0).off(\"click\");OrdersExportTool._delete(" . $item['entity_id'] . "," . $profile->getId() . ",\"" . $this->_urlInterface->getUrl('ordersexporttool/orders/reset') . "\")'>(✘)</a></span>";
                }
                $todo = [];
                if (isset($orders[$item['entity_id']])) {
                    $items = $orders[$item['entity_id']]->getAllItems();
                    $ids = [];
                    foreach ($items as $orderedItem) {
                        if ($orderedItem->getExportTo() > 0 && !in_array($orderedItem->getExportTo(), $doneIds)) {
                            $ids[] = $orderedItem->getExportTo();
                        }
                    }
                    $collection = $this->_modelProfiles->getCollection()->searchProfiles($ids);
                    foreach ($collection as $profile) {
                        $todo[] = "<span style='color:grey' id='orderexported-" . $item['entity_id'] . "-" . $profile->getId() . "'><span class='checkmark' style='font-size:20px;vertical-align: sub;'>&#10144;</span>&nbsp;" . $profile->getName() . " </span>";
                    }
                }
                $html .= implode('<br>', array_merge(array_unique($done), array_unique($todo)));
                // cannot remove count in this loop as the variables are filled IN the loop
                if (!count($done) && !count($todo)) {
                    $html = __("No profile defined");
                }
                $item[$this->getData('name')] = $html;
            }
        }
        return $dataSource;
    }
}