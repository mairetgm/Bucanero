<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrderUpdater\Controller\Adminhtml\Profiles;

/**
 * Class Preview
 * @package Wyomind\OrderUpdater\Controller\Adminhtml\Profiles
 */
class Preview extends \Wyomind\OrderUpdater\Controller\Adminhtml\AbstractController
{

    /**
     * @var null|\Wyomind\OrderUpdater\Model\ProfilesFactory
     */
    protected $profileModelFactory=null;
    /**
     * @var null|\Wyomind\OrderUpdater\Helper\Data
     */
    protected $dataHelper=null;
    /**
     * @var null|\Wyomind\OrderUpdater\Helper\Config
     */
    protected $configHelper=null;
    /**
     * @var null|\Wyomind\OrderUpdater\Helper\Storage
     */
    protected $storageHelper=null;


    /**
     * Preview constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Wyomind\OrderUpdater\Model\ProfilesFactory $profileModelFactory
     * @param \Wyomind\OrderUpdater\Helper\Data $dataHelper
     * @param \Wyomind\OrderUpdater\Helper\Storage $storageHelper
     * @param \Wyomind\OrderUpdater\Helper\Config $configHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Wyomind\OrderUpdater\Model\ProfilesFactory $profileModelFactory,
        \Wyomind\OrderUpdater\Helper\Data $dataHelper,
        \Wyomind\OrderUpdater\Helper\Storage $storageHelper,
        \Wyomind\OrderUpdater\Helper\Config $configHelper
    ) {
        parent::__construct($context, $resultForwardFactory, $resultRawFactory, $resultPageFactory);
        $this->profileModelFactory=$profileModelFactory;
        $this->dataHelper=$dataHelper;
        $this->storageHelper=$storageHelper;
        $this->configHelper=$configHelper;

    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $id=$this->getRequest()->getParam('id');
            $request=$this->getRequest();

            $isOutput=$this->getRequest()->getParam("isOutput");
            $model=$this->profileModelFactory->create();
            $model->load($id);
            $file=$this->storageHelper->evalRegexp($request->getParam("file_path"), $request->getParam("file_system_type"));

            $request->setParam("file_path", $file);
            $previewDta=$model->getImportData($request, $this->configHelper->getSettingsNbPreview(), $isOutput);

            return $this->getResponse()->representJson(json_encode($previewDta));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            return $this->getResponse()->representJson('{"error":"true","message":"' . preg_replace("/\r|\n|\t|\\\\/", "", nl2br(htmlentities($e->getMessage()))) . '"}');
        }
    }

}
