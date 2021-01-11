<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-01-11T23:16:36+00:00
 * File:          app/code/Xtento/CustomAttributes/Controller/Index/Download.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Controller\Index;

use Magento\Framework\Api\Uploader;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Filesystem;

/**
 * Class Download
 * @package Xtento\CustomAttributes\Controller\Index
 */
class Download extends Action
{
    /**
     * @var Session
     */
    private $session;

    private $fileFactory;

    private $rawFactory;

    private $fileSystem;

    public function __construct(
        Context $context,
        Session $session,
        FileFactory $fileFactory,
        RawFactory $rawFactory,
        Filesystem $fileSystem
    ) {
        parent::__construct($context);

        $this->fileFactory = $fileFactory;
        $this->session     = $session;
        $this->rawFactory  = $rawFactory;
        $this->fileSystem  = $fileSystem;
    }

    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $file = end($params);
        $dispersionPath = Uploader::getDispersionPath($file);

        $mediaDir = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA);
        $customerPath = $mediaDir->getAbsolutePath('customer' . $dispersionPath);

        $contents = [
            'type' => 'filename',
            'value' => $customerPath . DIRECTORY_SEPARATOR . $file,
        ];

        $fileStream = $this->fileFactory->create(
            $file,
            $contents,
            DirectoryList::MEDIA,
            'application/octet-stream'
        );

        return $fileStream;
    }
}