<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-12-14T13:23:14+00:00
 * File:          app/code/Xtento/CustomAttributes/Controller/Adminhtml/Upload/Download.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Controller\Adminhtml\Upload;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Api\Uploader;

class Download extends Action
{
    const ACTION = 'Magento_Sales::actions_view';

    public $resultFactory;

    private $fileFactory;

    private $rawFactory;

    private $fileSystem;

    public function __construct(
        Context $context,
        FileFactory $fileFactory,
        RawFactory $rawFactory,
        Filesystem $fileSystem
    ) {
        parent::__construct($context);
        $this->fileFactory = $fileFactory;
        $this->rawFactory = $rawFactory;
        $this->fileSystem = $fileSystem;
    }

    public function execute()
    {
        $file = $this->getRequest()->getParam('file');
        $dispersionPath = Uploader::getDispersionPath($file);
        $uploadType = 'customer';
        if ($this->getRequest()->getParam('download_type') === 'customer_address') {
            $uploadType = 'customer_address';
        }
        $mediaDir = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA);
        $customerPath = $mediaDir->getAbsolutePath($uploadType);
        $contents = [
            'type' => 'filename',
            'value' => $customerPath . $dispersionPath . DIRECTORY_SEPARATOR . $file,
        ];

        try {
            $fileStream = $this->fileFactory->create(
                $file,
                $contents,
                DirectoryList::MEDIA,
                'application/octet-stream'
            );
        } catch (\Exception $e) {
            $this->messageManager->addWarningMessage(
                __(
                    'File not found.'
                )
            );
            /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
            $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);
            return $resultPage;
        }
        return $fileStream;
    }

    /**
     * @return bool
     */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed(self::ACTION);
    }
}