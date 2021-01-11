<?php
/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrdersExportTool\Block\Adminhtml\Order\View\Items\Renderer;

/**
 * Render the export button in order > view
 */
class DefaultRenderer
{
    /**
     * @param $subject
     * @param $item
     * @return mixed
     */
    public function afterGetItem($subject, $item)
    {
        $profiles = $subject->getData('oet_profiles');
        if ($profiles == null) {
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            $profiles = $om->get('\Wyomind\OrdersExportTool\Model\ResourceModel\Profiles\Collection');
            $helper = $om->get('\Wyomind\OrdersExportTool\Helper\Data');
            $subject->setData('oet_profiles', $profiles);
            $subject->setData('oet_helper', $helper);
        }

        return $item;
    }
}