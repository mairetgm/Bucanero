<?php

/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassStockUpdate\Controller\Adminhtml\Profiles;

/**
 * Class Import
 * @package Wyomind\MassStockUpdate\Controller\Adminhtml\Profiles
 */
class Import extends \Wyomind\MassStockUpdate\Controller\Adminhtml\Profiles
{
    /**
     * @var string
     */
    public $module = "MassStockUpdate";

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function execute()
    {
        $this->_uploader = new \Magento\Framework\File\Uploader("file");

        if ($this->_uploader->getFileExtension() != "conf") {
            $this->messageManager->addError(__("Wrong file type (") . $this->_uploader->getFileExtension() . __(").<br>Choose a .profile file."));
        } else {

            $rootDir = $this->_directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::ROOT);
            $this->_uploader->save($rootDir . "/var/tmp", "import-file.csv");
            // récuperer le contenu
            $file = new \Magento\Framework\Filesystem\Driver\File;
            $import = new \Magento\Framework\File\Csv($file);
            $data = $import->getData($rootDir . "/var/tmp/" . $this->_uploader->getUploadedFileName());

            $key = $this->module;
            $model = $this->_objectManager->create('Wyomind\\' . $this->module . '\Model\Profiles');

            $profile = openssl_decrypt($data[0][0], "AES-128-ECB", $key);

            if ($model->load(0)->getResource()->importProfile($profile)) {
                $this->messageManager->addSuccess(__("The profile has been imported."));
            } else {
                $this->messageManager->addError(__("An error occured when importing the profile."));
            }
            $file->deleteFile($rootDir . "/var/tmp/" . $this->_uploader->getUploadedFileName());
        }

        $result = $this->_resultRedirectFactory->create()->setPath("*/*/index");
        return $result;
    }

}
