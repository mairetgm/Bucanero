<?php

/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrderUpdater\Controller\Adminhtml\Profiles;

/**
 * Class Export
 * @package Wyomind\OrderUpdater\Controller\Adminhtml\Profiles
 */
class Export extends \Wyomind\OrderUpdater\Controller\Adminhtml\Profiles
{


    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\RawFactory|\Magento\Framework\Controller\ResultInterface|mixed
     */
    public function execute()
    {
        $model = $this->_objectManager->create('Wyomind\\' . $this->module . '\Model\Profiles');
        $model->load($this->getRequest()->getParam('id'));

        foreach ($model->getData() as $field => $value) {
            $fields[] = $field;
            if ($field == "id") {
                $values[] = "NULL";
            } else {
                $values[] = "'" . str_replace(["'", "\\"], ["''", "\\\\"], $value) . "'";
            }
        }
        $sql = "INSERT INTO {{table}}(`" . implode('`,`', $fields) . "`) VALUES (" . implode(',', $values) . ");";
        $key = $this->module;
        $content = openssl_encrypt($sql, "AES-128-ECB", $key);


        return $this->download->sendUploadResponse($model->getName() . ".conf", $content);
    }
}
