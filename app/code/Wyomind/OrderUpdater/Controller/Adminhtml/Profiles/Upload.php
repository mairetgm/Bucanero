<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */


namespace Wyomind\OrderUpdater\Controller\Adminhtml\Profiles;

/**
 * Class Upload
 * @package Wyomind\OrderUpdater\Controller\Adminhtml\Profiles
 */
class Upload extends \Wyomind\OrderUpdater\Controller\Adminhtml\Profiles
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        try {
            $json = array();

            $uploader = new \Magento\Framework\File\Uploader("file_upload");
            $uploader->setAllowedExtensions(array("txt", "csv", "xml"));
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

            $directory = $objectManager->get('\Magento\Framework\Filesystem\DirectoryList');

            $rootPath = $directory->getRoot();

            $uploader->setFilesDispersion(false);

            $path = $rootPath . \Wyomind\OrderUpdater\Helper\Data::UPLOAD_DIR;
            $uploader->save($path);
            $fileName = $uploader->getCorrectFileName($uploader->getUploadedFileName());
            $json["error"] = false;
            $json["message"] = \Wyomind\OrderUpdater\Helper\Data::UPLOAD_DIR . $fileName;
        } catch (\Exception $e) {
            $json["error"] = true;
            $json["message"] = $e->getMessage();
        }
        $this->getResponse()->representJson($this->_objectManager->create('Magento\Framework\Json\Helper\Data')->jsonEncode($json));
    }
}