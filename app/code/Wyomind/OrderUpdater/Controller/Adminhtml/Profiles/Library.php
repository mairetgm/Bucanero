<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrderUpdater\Controller\Adminhtml\Profiles;

/**
 * Class Library
 * @package Wyomind\OrderUpdater\Controller\Adminhtml\Profiles
 */
class Library extends \Wyomind\OrderUpdater\Controller\Adminhtml\AbstractController
{

    /**
     * @var null|\Wyomind\OrderUpdater\Helper\Data
     */
    protected $dataHelper=null;

    /**
     * Library constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Wyomind\OrderUpdater\Helper\Data $dataHelper
     */

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Wyomind\OrderUpdater\Helper\Data $dataHelper
    ) {
        parent::__construct($context, $resultForwardFactory, $resultRawFactory, $resultPageFactory);

        $this->dataHelper=$dataHelper;

    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {

            $library['error']='false';
            $library['data']=array();
            $library['color']=array("rgba(0,0,0,0.1)", "rgba(50,255,0,0.1)", "rgba(50,255,0,0.1)");
            $library['tag']=array("Values", "Values", "");
            $library['header']=array("Attribute", "Type", "Example");

            foreach ($this->dataHelper->getJsonAttributes($this->dataHelper::MODULES_MAPPING) as $name=>$group) {
                if ($name == "storeviews") {
                    continue;
                }

                foreach ($group as $attribute) {

                    $value=isset($attribute["value"]) ? $attribute["value"] : "-";
                    $library['data'][]=array("<b>" . $name . "</b> | " . $attribute["label"], $attribute["type"], $value);
                }
            }


            return $this->getResponse()->representJson(json_encode($library));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            return $this->getResponse()->representJson('{"error":"true","message":"' . preg_replace("/\r|\n|\t|\\\\/", "", nl2br(htmlentities($e->getMessage()))) . '"}');
        }
    }

}
