<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrderUpdater\Controller\Adminhtml\Profiles;

/**
 * Class Ftp
 * @package Wyomind\OrderUpdater\Controller\Adminhtml\Profiles
 */
class Ftp extends \Magento\Backend\App\Action
{

    /**
     * @var \Wyomind\OrderUpdater\Helper\Ftp
     */
    protected $ftpHelper;

    /**
     * Ftp constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Wyomind\OrderUpdater\Helper\Ftp $ftpHelper
     */
    public function __construct(
    \Magento\Backend\App\Action\Context $context, \Wyomind\OrderUpdater\Helper\Ftp $ftpHelper
    )
    {

        parent::__construct($context);
        $this->ftpHelper = $ftpHelper;
    }

    /**
     *
     */
    public function execute()
    {

        try {
            $data = $this->getRequest()->getParams();
            $ftp = $this->ftpHelper->getConnection($data);

            $content = __("Connection succeeded");
            $ftp->close();
        } catch (\Exception $e) {
            $content = $e->getMessage();
        }

        $this->getResponse()->representJson($this->_objectManager->create('Magento\Framework\Json\Helper\Data')->jsonEncode($content));
    }

}
