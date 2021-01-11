<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrderUpdater\Controller\Adminhtml;

/**
 * Class AbstractController
 * @package Wyomind\OrderUpdater\Controller\Adminhtml
 */
abstract class AbstractController extends \Magento\Backend\App\Action
{

    public $module = "OrderUpdater";

    protected $resultForwardFactory = null;
    protected $resultRedirectFactory = null;
    protected $resultRawFactory = null;
    protected $resultPageFactory = null;

    /**
     * AbstractController constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);


        $this->resultForwardFactory=$resultForwardFactory;
        $this->resultRedirectFactory=$context->getResultRedirectFactory();
        $this->resultRawFactory=$resultRawFactory;
        $this->resultPageFactory=$resultPageFactory;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Wyomind_' . $this->module . '::profiles');
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    abstract public function execute();
}
