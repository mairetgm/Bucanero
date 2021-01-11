<?php
/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\OrdersExportTool\Block\Adminhtml\Order\View\Items\Renderer;

use Magento\Framework\App\ObjectManager;

/**
 * Render the export button in order > view
 */
class ExportTo extends \Magento\Sales\Block\Adminhtml\Items\Renderer\DefaultRenderer
{
    /**
     * @var null
     */
    protected $profiles = null;

    /**
     * @return null
     */
    public function getProfiles()
    {
        if ($this->profiles == null) {
            $om = ObjectManager::getInstance();
            $this->profiles = $om->get('\Wyomind\OrdersExportTool\Model\ResourceModel\Profiles\Collection');


        }
        return $this->profiles;

    }
}