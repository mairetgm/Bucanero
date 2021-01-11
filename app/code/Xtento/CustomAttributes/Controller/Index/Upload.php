<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-12-11T13:51:16+00:00
 * File:          app/code/Xtento/CustomAttributes/Controller/Index/Upload.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Controller\Index;

use Xtento\CustomAttributes\Model\FileUpload;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Response\Http\FileFactory;

/**
 * Class Download
 * @package Xtento\CustomAttributes\Controller\Index
 */
class Upload extends Action
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * @var FileUpload
     */
    private $fileUpload;

    /**
     * Upload constructor.
     *
     * @param Context $context
     * @param Session $session
     * @param FileFactory $fileFactory
     * @param FileUpload $fileUpload
     */
    public function __construct(
        Context $context,
        Session $session,
        FileFactory $fileFactory,
        FileUpload $fileUpload
    ) {
        parent::__construct($context);
        $this->fileFactory = $fileFactory;
        $this->session = $session;
        $this->fileUpload = $fileUpload;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $files = $this->getRequest()->getFiles()->toArray();
        if (array_key_exists('custom_attributes', $files)) {
            $this->fileUpload->checkoutUploads($files['custom_attributes']);
        } else {
            $this->fileUpload->checkoutUploads($files);
        }
    }
}