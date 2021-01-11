<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrderUpdater\Controller\Adminhtml\Profiles;

/**
 * Class Report
 * @package Wyomind\OrderUpdater\Controller\Adminhtml\Profiles
 */
class Report extends \Wyomind\OrderUpdater\Controller\Adminhtml\Profiles
{
    /**
     * @var string
     */
    public $module = "OrderUpdater";

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPost();
        if ($data) {
            $model = $this->_objectManager->create('Wyomind\\' . $this->module . '\Model\Profiles');

            $id = $this->getRequest()->getParam('id');

            if ($id) {
                $model->load($id);
            }

            return $this->getResponse()->representJson($model->getLastImportReport());
        }
    }
}