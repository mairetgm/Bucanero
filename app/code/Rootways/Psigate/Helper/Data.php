<?php
/**
 * PSiGate Payment Module.
 *
 * @category  Payment Integration
 * @package   Rootways_Psigate
 * @author    Developer RootwaysInc <developer@rootways.com>
 * @copyright 2017 Rootways Inc. (https://www.rootways.com)
 * @license   Rootways Custom License
 * @link      https://www.rootways.com/pub/media/extension_doc/license_agreement.pdf
 */
namespace Rootways\Psigate\Helper;

use Magento\Payment\Model\Config as PaymentConfig;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    
    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $_encryptor;
    
    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $resourceConfig;
    
    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected $regionFactory;
    
    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $countryFactory;
    
    /**
     * Helper Data.
     * @param Magento\Framework\App\Helper\Context $context
     * @param Magento\Framework\ObjectManagerInterface $objectManager
     * @param Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param Magento\Config\Model\ResourceModel\Config $resourceConfig
     * @param Magento\Directory\Model\RegionFactory $regionFactory
     * @param Magento\Directory\Model\CountryFactory $countryFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory
    ) {
        $this->_objectManager = $objectManager;
        $this->_storeManager = $storeManager;
        $this->_encryptor = $encryptor;
        $this->_customresourceConfig = $resourceConfig;
        $this->_regionFactory = $regionFactory;
        $this->_countryFactory = $countryFactory;
        parent::__construct($context);
    }
    
    /**
     * Get Configuration value from admin.
     */
    public function getConfig($config_path)
    {
        return $this->scopeConfig->getValue(
            $config_path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * Get value of Store ID.
     */
    public function getStoreID()
    {
        $val = $this->getConfig('payment/rootways_psigate_option/login');
        return $val;
    }
    
    /**
     * Get value of Passphrase.
     */
    public function getPassphrase()
    {
        $val = $this->getConfig('payment/rootways_psigate_option/trans_key');
        return $val;
    }
    
    /**
     * Get value of secure URL from admin
     */
    public function surl()
    {
        return "aHR0cHM6Ly93d3cucm9vdHdheXMuY29tL20ydmVyaWZ5bGljLnBocA==";
    }
    
    /**
     * Get value of licence key from admin
     */
    public function act()
    {
        $dt_db_blank = $this->getConfig('rootways_psigate/general/lcstatus');
        if ($dt_db_blank == '') {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $isMultiStore =  $this->getConfig('rootways_psigate/general/ismultistore');
            $u = $this->_storeManager->getStore()->getBaseUrl();
            if ($isMultiStore == 1) {
                $u = $objectManager->create('Magento\Backend\Helper\Data')->getHomePageUrl();
            }
            $l = $this->getConfig('rootways_psigate/general/licencekey');
            $surl = base64_decode($this->surl());
            $url = $surl."?u=".$u."&l=".$l."&extname=m2_psigate";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_URL, $url);
            $result=curl_exec($ch);
            curl_close($ch);
            $act_data = json_decode($result, true);
            if ($act_data['status'] == '0') {
                return "SXNzdWUgd2l0aCB5b3VyIFJvb3R3YXlzIGV4dGVuc2lvbiBsaWNlbnNlIGtleSwgcGxlYXNlIGNvbnRhY3QgPGEgaHJlZj0ibWFpbHRvOmhlbHBAcm9vdHdheXMuY29tIj5oZWxwQHJvb3R3YXlzLmNvbTwvYT4=";
            } else {
                $this->_customresourceConfig->saveConfig('rootways_psigate/general/lcstatus', $l, 'default', 0);
            }
        }
    }
}
