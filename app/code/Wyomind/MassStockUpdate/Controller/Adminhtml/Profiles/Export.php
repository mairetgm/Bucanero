<?php

/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassStockUpdate\Controller\Adminhtml\Profiles;

/**
 * Class Export
 * @package Wyomind\MassStockUpdate\Controller\Adminhtml\Profiles
 */
class Export extends \Wyomind\MassStockUpdate\Controller\Adminhtml\Profiles
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


        return $this->_framework->sendUploadResponse($model->getName() . ".conf", $content);
    }
}
