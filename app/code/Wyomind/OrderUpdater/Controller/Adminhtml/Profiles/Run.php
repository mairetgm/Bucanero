<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrderUpdater\Controller\Adminhtml\Profiles;

/**
 * Full import process
 */
class Run extends \Wyomind\OrderUpdater\Controller\Adminhtml\Profiles
{



    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $request = $this->getRequest();
        $id = $this->getRequest()->getParam('id');
        $preview = $this->getRequest()->getParam('preview');
        try {
            $data = $this->getRequest()->getPost();
            if ($this->getRequest()->getParam("isAjax")) {
                session_write_close();
            }
            if ($data) {
                $model = $this->_objectManager->create('Wyomind\\' . $this->module . '\Model\Profiles');
                if ($id) {
                    $model->load($id);
                }

                /*
                 * If the profile is being run in preview mode, set the frontend values to the model.
                 * Thanks to this, the preview will take the unsaved changes into account.
                 * Warning: never save this model
                 */
                if ($preview) {
                    foreach ($data as $key => $value) {
                        $model->setData($key, $value);
                    }
                }
                $rtn = $model->multipleImport($preview);

                if($preview == 1) {
                    $report = implode("\n", $rtn);
                    return $this->getResponse()->representJson('{"error":"false","message":"' . $report . '"}');
                } else {
                    $this->messageManager->addSuccess(__('The profile %1 [ID:%2] has been processed.', $model->getName(), $model->getId()));

                    if (is_string($rtn["success"])) {
                        $this->messageManager->addSuccess(__('%1', $rtn["success"]));
                    }
                    if (is_string($rtn["notice"])) {
                        $this->messageManager->addNotice(__('%1', $rtn["notice"]));
                    }
                    if (is_string($rtn["warning"])) {
                        $this->messageManager->addWarning(__('%1', $rtn["warning"]));
                    }
                    if (is_string($rtn["error"])) {
                        $this->messageManager->addError(__('%1', $rtn["error"]));
                    }
                    if ($request->getParam('run_i')) {
                        return $this->resultRedirectFactory->create()->setPath(strtolower($this->module) . '/profiles/edit', ['id' => $model->getId(), "_current" => true]);
                    }
                }
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
            if ($request->getParam('run_i')) {
                return $this->resultRedirectFactory->create()->setPath(strtolower($this->module) . '/profiles/edit', ['id' => $id, "_current" => true]);
            }
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            if ($request->getParam('run_i')) {
                return $this->resultRedirectFactory->create()->setPath(strtolower($this->module) . '/profiles/edit', ['id' => $id, "_current" => true]);
            }
        }
    }

}
