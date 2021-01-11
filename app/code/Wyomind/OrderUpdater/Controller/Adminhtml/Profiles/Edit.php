<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrderUpdater\Controller\Adminhtml\Profiles;

/**
 * Class Edit
 * @package Wyomind\OrderUpdater\Controller\Adminhtml\Profiles
 */
class Edit extends \Wyomind\OrderUpdater\Controller\Adminhtml\Profiles
{

    /**
     * @var string
     */
    public $name = "Mass Order Update";


    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu("Magento_Sales::sales");
        $resultPage->addBreadcrumb(__($this->name), __($this->name));
        $resultPage->addBreadcrumb(__('Manage Profiles'), __('Manage Profiles'));

        $id = $this->getRequest()->getParam('id');
        $model = $this->_objectManager->create('Wyomind\\' . $this->module . '\Model\Profiles');


        if ($id) {
            $model->load($id);

            if (!$model->getId()) {
                $this->messageManager->addError(__('This profile no longer exists.'));
                return $this->resultRedirectFactory->create()->setPath(strtolower($this->module) . '/profiles/index');
            }
        }

        $resultPage->getConfig()->getTitle()->prepend($model->getId() ? (__('Modify profile : ') . $model->getName()) : __('New profile'));

        $this->coreRegistry->register('profile', $model);

        return $resultPage;
    }

}
