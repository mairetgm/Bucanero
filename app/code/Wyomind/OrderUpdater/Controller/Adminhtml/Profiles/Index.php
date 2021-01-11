<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrderUpdater\Controller\Adminhtml\Profiles;

/**
 * Class Index
 * @package Wyomind\OrderUpdater\Controller\Adminhtml\Profiles
 */
class Index extends \Wyomind\OrderUpdater\Controller\Adminhtml\Profiles
{

    /**
     * @var string
     */
    public $name = "Mass Order Update";

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu("Magento_Sales::sales");
        $resultPage->getConfig()->getTitle()->prepend(__($this->name . ' > Profiles'));
        $resultPage->addBreadcrumb(__($this->name), __($this->name));

        return $resultPage;
    }

}
