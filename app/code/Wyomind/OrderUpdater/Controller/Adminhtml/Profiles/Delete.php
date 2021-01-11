<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrderUpdater\Controller\Adminhtml\Profiles;

/**
 * Delete action
 */
class Delete extends \Wyomind\OrderUpdater\Controller\Adminhtml\Profiles
{


    /**
     * Execute action
     * @return void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $model = $this->_objectManager->create("Wyomind\\" . $this->module . "\Model\Profiles");
                $model->setId($id);
                $model->delete();
                $this->messageManager->addSuccess(__("The profile has been deleted."));
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        } else {
            $this->messageManager->addError(__("This profile doesn't exist anymore."));
        }

        $return = $this->resultRedirectFactory->create()->setPath(strtolower($this->module) . '/profiles/index');
        return $return;
    }
}