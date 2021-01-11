<?php

namespace Wyomind\OrderUpdater\Controller\Adminhtml;

/**
 * Class Profiles
 * @package Wyomind\OrderUpdater\Controller\Adminhtml
 */
abstract class Profiles extends \Wyomind\OrderUpdater\Controller\Adminhtml\AbstractController
{
    /**
     * @var \Magento\Framework\Registry|null
     */
    protected $coreRegistry=null;
    /**
     * @var null|\Wyomind\OrderUpdater\Helper\Config
     */
    protected $configHelper=null;
    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface|null
     */
    protected $directoryRead=null;
    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $directoryList;
    /**
     * @var \Magento\Framework\App\CacheInterface|null
     */
    protected $cacheManager=null;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface|null
     */
    protected $storeManager=null;
    /**
     * @var null|\Wyomind\Framework\Helper\Download
     */
    protected $download=null;

    /**
     * Profiles constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Model\Context $contextModel
     * @param \Magento\Framework\Registry $coreRegistry
//     * @param \Wyomind\OrderUpdater\Helper\Config $configHelper
     * @param \Magento\Framework\Filesystem\Directory\ReadFactory $directoryRead
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Wyomind\Framework\Helper\Download $download
     * @param String $module
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Model\Context $contextModel,
        \Magento\Framework\Registry $coreRegistry,
//        \Wyomind\OrderUpdater\Helper\Config $configHelper,
        \Magento\Framework\Filesystem\Directory\ReadFactory $directoryRead,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Wyomind\Framework\Helper\Download $download

    ) {
        $this->coreRegistry=$coreRegistry;
  //      $this->configHelper=$configHelper;
        $this->cacheManager=$contextModel->getCacheManager();
        $this->directoryRead=$directoryRead->create("");
        $this->directoryList=$directoryList;
        $this->storeManager=$storeManager;
        $this->download=$download;

        parent::__construct($context, $resultForwardFactory, $resultRawFactory, $resultPageFactory);
    }


}
