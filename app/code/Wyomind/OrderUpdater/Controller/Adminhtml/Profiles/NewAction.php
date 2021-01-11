<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrderUpdater\Controller\Adminhtml\Profiles;

/**
 * Class NewAction
 * @package Wyomind\OrderUpdater\Controller\Adminhtml\Profiles
 */
class NewAction extends \Wyomind\OrderUpdater\Controller\Adminhtml\Profiles
{

    /**
     * @return \Magento\Backend\Model\View\Result\Forward|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        return $this->resultForwardFactory->create()->forward("edit");
    }
}
