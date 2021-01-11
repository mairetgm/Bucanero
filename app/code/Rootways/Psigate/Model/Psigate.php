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
namespace Rootways\Psigate\Model;

use Rootways\Psigate\Model\PsigateException;
use Magento\Framework\App\Filesystem\DirectoryList;
use Monolog\Logger;
use Magento\Quote\Api\Data\CartInterface;

class Psigate extends \Magento\Payment\Model\Method\Cc
{
    const CODE = 'rootways_psigate_option';
    protected $_code = self::CODE;
    
    protected $_isGateway = true;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = false;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canVoid = true;
    protected $_canUseInternal = true;
    protected $_canUseCheckout = true;
    protected $_canUseForMultishipping = true;
    protected $_canSaveCc = false;
    
    protected $customerHelper;
    const REQUEST_TYPE_AUTH_CAPTURE = 'AUTH_CAPTURE';
    const REQUEST_TYPE_AUTH_ONLY = 'AUTH_ONLY';
    const REQUEST_TYPE_CAPTURE_ONLY = 'CAPTURE_ONLY';
    const REQUEST_TYPE_CREDIT = 'CREDIT';
    const REQUEST_TYPE_VOID = 'VOID';
    const REQUEST_TYPE_PRIOR_AUTH_CAPTURE = 'PRIOR_AUTH_CAPTURE';
    const RESPONSE_CODE_APPROVED = 1;
    const RESPONSE_CODE_DECLINED = 2;
    const RESPONSE_CODE_ERROR = 3;
    const RESPONSE_CODE_HELD = 4;
    
	/**
     * Payment Model.
     * @param Magento\Framework\Model\Context $context
     * @param Magento\Framework\Registry $registry
     * @param Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param Magento\Payment\Helper\Data $paymentData
     * @param Rootways\Psigate\Helper\Data $customHelper
     * @param Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param Magento\Payment\Model\Method\Logger $logger
     * @param Magento\Framework\Module\ModuleListInterface $moduleList
     * @param Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Rootways\Psigate\Helper\Data $customerHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Rootways\Psigate\Model\Request\Factory $request,
        \Rootways\Psigate\Model\Response\Factory $response,
        \Magento\Checkout\Model\Cart $checkoutCart,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = array()
    )
    {
        $this->customerHelper = $customerHelper;
        $this->requestFactory = $request;
        $this->responseFactory = $response;
        $this->cart = $checkoutCart;
        $this->checkoutSession = $checkoutSession;
        $this->_storeManager = $storeManager;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $moduleList,
            $localeDate,
            $resource,
            $resourceCollection,
            $data
        );
    }
    
    public function isAvailable(CartInterface $available = null)
    {
        return true;
    }

    
    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        if ($amount <= 0) {
            self::throwException(__('Invalid amount for capture.'));
        }
        
        $auth_trans = false;
        if ($amount > 0) {
            $payment->setAnetTransType(self::REQUEST_TYPE_AUTH_ONLY);
            $payment->setAmount($amount);
            $build_request = $this->_buildRequest($payment);
            $post_request = $this->_postRequest($build_request);
            $payment->setCcApproval($post_request->getApprovalCode())
                ->setLastTransId($post_request->getTransactionId())
                ->setCcTransId($post_request->getTransactionId())
                ->setCcAvsStatus($post_request->getAvsResultCode())
                ->setCcCidStatus($post_request->getCardCodeResponseCode());
            $respose_reason_code = $post_request->getResponseReasonCode();
            $respose_reason_text = $post_request->getResponseReasonText();
            
            switch ($post_request->getResponseCode()) {
                case self::RESPONSE_CODE_APPROVED:
                    $payment->setStatus(self::STATUS_APPROVED);
                    if ($post_request->getTransactionId() != $payment->getParentTransactionId()) {
                        $payment->setTransactionId($post_request->getTransactionId());
                    }
                    $payment->setIsTransactionClosed(0)->setTransactionAdditionalInfo('real_transaction_id', $post_request->getTransactionId());
                    break;
                case self::RESPONSE_CODE_DECLINED:
                    $auth_trans = __('Payment authorization transaction has been declined. ' . "\n{$respose_reason_text}");
                    break;
                default:
                    $auth_trans = __('Payment authorization error. ' . "\n{$respose_reason_text}");
                    break;
            }
        } else {
            $auth_trans = __('Invalid amount for authorization.');
        }
        if ($auth_trans !== false) {
            self::throwException($auth_trans);
        } else {
            
        }
        return $this;
    }
    
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $auth_trans = false;
        if ($payment->getParentTransactionId()) {
            $payment->setAnetTransType(self::REQUEST_TYPE_PRIOR_AUTH_CAPTURE);
        } else {
            $payment->setAnetTransType(self::REQUEST_TYPE_AUTH_CAPTURE);
        }
        $payment->setAmount($amount);
        $build_request = $this->_buildRequest($payment);
        $post_request = $this->_postRequest($build_request);
        if ($post_request->getResponseCode() == self::RESPONSE_CODE_APPROVED) {
            $payment->setStatus(self::STATUS_APPROVED);
            $payment->setCcTransId($post_request->getTransactionId());
            $payment->setLastTransId($post_request->getTransactionId());
            if ($post_request->getTransactionId() != $payment->getParentTransactionId()) {
                $payment->setTransactionId($post_request->getTransactionId());
            }
            $payment->setIsTransactionClosed(0)
                ->setTransactionAdditionalInfo('real_transaction_id', $post_request->getTransactionId());
        } else {
            if ($post_request->getResponseReasonText()) {
                $auth_trans = $post_request->getResponseReasonText();
            } else {
                $auth_trans = __('Error in capturing the payment');
            }
            if (!($order = $payment->getOrder())) {
                $order = $payment->getQuote();
            }
            $order->addStatusToHistory($order->getStatus() , urldecode($auth_trans) . ' at PsiGate', $auth_trans . ' from PsiGate');
        }
        if ($auth_trans !== false) {
            self::throwException($auth_trans);
        }
        return $this;
    }
    
    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $auth_trans = false;
        $transaction_id = $payment->getRefundTransactionId();
        
        if (empty($transaction_id)) {
            $transaction_id = $payment->getParentTransactionId();
        }
        
        if (($this->getConfigData('test') && $transaction_id == 0 || $transaction_id) && $amount > 0) {
            $payment->setAnetTransType(self::REQUEST_TYPE_CREDIT);
            $build_request = $this->_buildRequest($payment);
            $build_request->setXAmount($amount);
            $post_request = $this->_postRequest($build_request);
            
            if ($post_request->getResponseCode() == self::RESPONSE_CODE_APPROVED) {
                $payment->setStatus(self::STATUS_SUCCESS);
                if ($post_request->getTransactionId() != $payment->getParentTransactionId()) {
                    $payment->setTransactionId($post_request->getTransactionId());
                }
                $ref_order = $payment->getOrder()
                    ->canCreditmemo() ? 0 : 1;
                $payment->setIsTransactionClosed(1)
                    ->setShouldCloseParentTransaction($ref_order)->setTransactionAdditionalInfo('real_transaction_id', $post_request->getTransactionId());
            } else {
                $response_txt = $post_request->getResponseReasonText();
                $auth_trans = true;
            }
        } else {
            $response_txt = __('Error in refunding the payment');
            $auth_trans = true;
        }
        
        if ($auth_trans !== false) {
            self::throwException($response_txt);
        }
        return $this;
    }
    
    public function void(\Magento\Payment\Model\InfoInterface $payment)
    {
        $auth_trans = false;
        $transaction_id = $payment->getVoidTransactionId();
        
        if (empty($transaction_id)) {
            $transaction_id = $payment->getParentTransactionId();
        }
        $amount = $payment->getAmount();
        
        if ($amount <= 0) {
            $amount = $payment->getAmountAuthorized();
            $payment->setAmount($payment->getAmountAuthorized());
        }
        
        if ($transaction_id && $amount > 0) {
            $payment->setAnetTransType(self::REQUEST_TYPE_VOID);
            $build_request = $this->_buildRequest($payment);
            $post_request = $this->_postRequest($build_request);
            
            if ($post_request->getResponseCode() == self::RESPONSE_CODE_APPROVED) {
                $payment->setStatus(self::STATUS_VOID);
                if ($post_request->getTransactionId() != $payment->getParentTransactionId()) {
                    $payment->setTransactionId($post_request->getTransactionId());
                }
                $payment->setIsTransactionClosed(1)
                    ->setShouldCloseParentTransaction(1)
                    ->setTransactionAdditionalInfo('real_transaction_id', $post_request->getTransactionId());
            } else {
                $response_txt = $post_request->getResponseReasonText();
                $auth_trans = true;
            }
        } elseif (!$transaction_id) {
            $response_txt = __('Error in voiding the payment. Transaction ID not found');
            $auth_trans = true;
        } elseif ($amount <= 0) {
            $response_txt = __('Error in voiding the payment. Payment amount is 0');
            $auth_trans = true;
        } else {
            $response_txt = __('Error in voiding the payment');
            $auth_trans = true;
        }
        if ($auth_trans !== false) {
            self::throwException($response_txt);
        }
        return $this;
    }
    
    protected function _buildRequest(\Magento\Payment\Model\InfoInterface $payment)
    {
        $storeId = $this->customerHelper->getStoreID();
        $passphrase = $this->customerHelper->getPassphrase();
        
        $order = $payment->getOrder();
        $build_request = $this
            ->requestFactory
            ->create();
        $build_request->setXVersion(3.1)
            ->setXDelimData('True')
            ->setXDelimChar('')
            ->setXRelayResponse('False');
        $build_request->setXTestRequest($this->getConfigData('test') ? 'TRUE' : 'FALSE');
        $build_request->setXLogin($storeId)
            ->setXTranKey($passphrase)
            ->setXType($payment->getAnetTransType())
            ->setXMethod($payment->getAnetTransMethod());
        if ($payment->getAmount()) {
            $build_request->setXAmount($payment->getAmount() , 2);
            $build_request->setXCurrencyCode($order->getBaseCurrencyCode());
        }
        switch ($payment->getAnetTransType()) {
            case self::REQUEST_TYPE_CREDIT:
            case self::REQUEST_TYPE_VOID:
            case self::REQUEST_TYPE_PRIOR_AUTH_CAPTURE:
                $build_request->setXTransId($payment->getCcTransId());
                $build_request->setXCardNum($payment->getCcNumber())
                    ->setXExpDate(sprintf('%02d-%04d', $payment->getCcExpMonth() , $payment->getCcExpYear()))
                    ->setXCardCode($payment->getCcCid())
                    ->setXCardName($payment->getCcOwner());
                break;
            case self::REQUEST_TYPE_CAPTURE_ONLY:
                $build_request->setXAuthCode($payment->getCcAuthCode());
                break;
        }
        
        if (!empty($order)) {
            $shipping_amount = $order->getShippingAmount();
            $tax_amount = $order->getTaxAmount();
            $sub_total = $order->getSubtotal();
            $build_request->setXInvoiceNum($order->getIncrementId());
            $billing_address = $order->getBillingAddress();
            
            if (!empty($billing_address)) {
                $email = $billing_address->getEmail();
                if (!$email) {
                    $email = $order->getBillingAddress()
                        ->getEmail();
                }
                
                if (!$email) {
                    $email = $order->getCustomerEmail();
                }
                $build_request->setXFirstName($billing_address->getFirstname())
                    ->setXLastName($billing_address->getLastname())
                    ->setXCompany($billing_address->getCompany())
                    ->setXAddress($billing_address->getStreet(1) [0])
                    ->setXCity($billing_address->getCity())
                    ->setXState($billing_address->getRegion())
                    ->setXZip($billing_address->getPostcode())
                    ->setXCountry($billing_address->getCountry())
                    ->setXPhone($billing_address->getTelephone())
                    ->setXFax($billing_address->getFax())
                    ->setXCustId($billing_address->getCustomerId())
                    ->setXCustomerIp($order->getRemoteIp())
                    ->setXCustomerTaxId($billing_address->getTaxId())
                    ->setXEmail($email)->setXEmailCustomer($this->getConfigData('email_customer'))
                    ->setXMerchantEmail($this->getConfigData('merchant_email'));
            }
            $shipping_address = $order->getShippingAddress();
            if (!$shipping_address) {
                $shipping_address = $billing_address;
            }
            if (!empty($shipping_address)) {
                $build_request->setXShipToFirstName($shipping_address->getFirstname())
                    ->setXShipToLastName($shipping_address->getLastname())
                    ->setXShipToCompany($shipping_address->getCompany())
                    ->setXShipToAddress($shipping_address->getStreet(1) [0])
                    ->setXShipToCity($shipping_address->getCity())
                    ->setXShipToState($shipping_address->getRegion())
                    ->setXShipToZip($shipping_address->getPostcode())
                    ->setXShipToCountry($shipping_address->getCountry());
                if (!isset($shipping_amount) || $shipping_amount <= 0) {
                    $shipping_amount = $shipping_address->getShippingAmount();
                }
                if (!isset($tax_amount) || $tax_amount <= 0) {
                    $tax_amount = $shipping_address->getTaxAmount();
                }
                if (!isset($sub_total) || $sub_total <= 0) {
                    $sub_total = $shipping_address->getSubtotal();
                }
            }
            $build_request->setXPoNum($payment->getPoNumber())
                ->setXTax($tax_amount)->setXSubtotal($sub_total)->setXFreight($shipping_amount);
        }
        if ($payment->getCcNumber()) {
            $build_request->setXCardNum($payment->getCcNumber())
                ->setXExpDate(sprintf('%02d-%04d', $payment->getCcExpMonth() , $payment->getCcExpYear()))
                ->setXCardCode($payment->getCcCid())
                ->setXCardName($payment->getCcOwner());
        }
        $build_request->setXLineItems($this->getLineItems($order));
        return $build_request;
    }
    
    protected function _postRequest(\Rootways\Psigate\Model\Request $build_request)
    {
        $post_request = $this
            ->responseFactory
            ->create();
        $get_data = $build_request->getData();
        $code = array(
            0 => '1',
            1 => '1',
            2 => '1',
            3 => '(TESTMODE) This transaction has been approved.',
            4 => '000000',
            5 => 'P',
            6 => '0',
            7 => '100000018',
            8 => '',
            9 => '2704.99',
            10 => 'CC',
            11 => 'auth_only',
            12 => '',
            13 => 'Sreeprakash',
            14 => 'N.',
            15 => 'Rootways',
            16 => 'XYZ',
            17 => 'City',
            18 => 'Idaho',
            19 => '695038',
            20 => 'US',
            21 => '1234567890',
            22 => '',
            23 => '',
            24 => 'Sreeprakash',
            25 => 'N.',
            26 => 'Rootways',
            27 => 'XYZ',
            28 => 'City',
            29 => 'Idaho',
            30 => '695038',
            31 => 'US',
            32 => '',
            33 => '',
            34 => '',
            35 => '',
            36 => '',
            37 => '382065EC3B4C2F5CDC424A730393D2DF',
            38 => '',
            39 => '',
            40 => '',
            41 => '',
            42 => '',
            43 => '',
            44 => '',
            45 => '',
            46 => '',
            47 => '',
            48 => '',
            49 => '',
            50 => '',
            51 => '',
            52 => '',
            53 => '',
            54 => '',
            55 => '',
            56 => '',
            57 => '',
            58 => '',
            59 => '',
            60 => '',
            61 => '',
            62 => '',
            63 => '',
            64 => '',
            65 => '',
            66 => '',
            67 => ''
        );
        $code[7] = $get_data['x_invoice_num'];
        $code[8] = '';
        $code[9] = $get_data['x_amount'];
        $code[10] = $get_data['x_method'];
        $code[11] = $get_data['x_type'];
        $code[12] = $get_data['x_cust_id'];
        $code[13] = $get_data['x_first_name'];
        $code[14] = $get_data['x_last_name'];
        $code[15] = $get_data['x_company'];
        $code[16] = $get_data['x_address'];
        $code[17] = $get_data['x_city'];
        $code[18] = $get_data['x_state'];
        $code[19] = $get_data['x_zip'];
        $code[20] = $get_data['x_country'];
        $code[21] = $get_data['x_phone'];
        $code[22] = $get_data['x_fax'];
        $code[23] = '';
        $get_data['x_ship_to_first_name'] = !isset($get_data['x_ship_to_first_name']) ? $get_data['x_first_name'] : $get_data['x_ship_to_first_name'];
        $get_data['x_ship_to_first_name'] = !isset($get_data['x_ship_to_first_name']) ? $get_data['x_first_name'] : $get_data['x_ship_to_first_name'];
        $get_data['x_ship_to_last_name'] = !isset($get_data['x_ship_to_last_name']) ? $get_data['x_last_name'] : $get_data['x_ship_to_last_name'];
        $get_data['x_ship_to_company'] = !isset($get_data['x_ship_to_company']) ? $get_data['x_company'] : $get_data['x_ship_to_company'];
        $get_data['x_ship_to_address'] = !isset($get_data['x_ship_to_address']) ? $get_data['x_address'] : $get_data['x_ship_to_address'];
        $get_data['x_ship_to_city'] = !isset($get_data['x_ship_to_city']) ? $get_data['x_city'] : $get_data['x_ship_to_city'];
        $get_data['x_ship_to_state'] = !isset($get_data['x_ship_to_state']) ? $get_data['x_state'] : $get_data['x_ship_to_state'];
        $get_data['x_ship_to_zip'] = !isset($get_data['x_ship_to_zip']) ? $get_data['x_zip'] : $get_data['x_ship_to_zip'];
        $get_data['x_ship_to_country'] = !isset($get_data['x_ship_to_country']) ? $get_data['x_country'] : $get_data['x_ship_to_country'];
        $code[24] = $get_data['x_ship_to_first_name'];
        $code[25] = $get_data['x_ship_to_last_name'];
        $code[26] = $get_data['x_ship_to_company'];
        $code[27] = $get_data['x_ship_to_address'];
        $code[28] = $get_data['x_ship_to_city'];
        $code[29] = $get_data['x_ship_to_state'];
        $code[30] = $get_data['x_ship_to_zip'];
        $code[31] = $get_data['x_ship_to_country'];
        $code[0] = '1';
        $code[1] = '1';
        $code[2] = '1';
        $code[3] = '(TESTMODE2) This transaction has been approved.';
        $code[4] = '000000';
        $code[5] = 'P';
        $code[6] = '0';
        $code[37] = '382065EC3B4C2F5CDC424A730393D2DF';
        $code[39] = '';
        $psi_gate_api = $this->_psigateapi($get_data);
        $code[0] = $psi_gate_api['response_code'];
        $code[1] = $psi_gate_api['response_subcode'];
        $code[2] = $psi_gate_api['response_reason_code'];
        $code[3] = $psi_gate_api['response_reason_text'];
        $code[4] = $psi_gate_api['approval_code'];
        $code[5] = $psi_gate_api['avs_result_code'];
        $code[6] = $psi_gate_api['transaction_id'];
        $code[37] = '';
        $code[39] = '';
        if ($code) {
            $post_request->setResponseCode((int)str_replace('"', '', $code[0]));
            $post_request->setResponseSubcode((int)str_replace('"', '', $code[1]));
            $post_request->setResponseReasonCode((int)str_replace('"', '', $code[2]));
            $post_request->setResponseReasonText($code[3]);
            $post_request->setApprovalCode($code[4]);
            $post_request->setAvsResultCode($code[5]);
            $post_request->setTransactionId($code[6]);
            $post_request->setInvoiceNumber($code[7]);
            $post_request->setDescription($code[8]);
            $post_request->setAmount($code[9]);
            $post_request->setMethod($code[10]);
            $post_request->setTransactionType($code[11]);
            $post_request->setCustomerId($code[12]);
            $post_request->setMd5Hash($code[37]);
            $post_request->setCardCodeResponseCode($code[39]);
        } else {
            self::throwException(__('Error in payment gateway'));
        }
        return $post_request;
    }
    
    function _psigateapi($get_data)
    {
        if (!isset($get_data['x_currency_code'])) {
            $get_data['x_currency_code'] = 'USD';
        }
        if (!isset($get_data['x_login']) || empty($get_data['x_login'])) {
            if (!isset($get_data['x_login']) || empty($get_data['x_login'])) {
                $get_data['x_login'] = 'teststore';
            }
        }
        $x_login = $get_data['x_login'];
        if (!isset($get_data['x_tran_key']) || empty($get_data['x_tran_key'])) {
            if (!isset($get_data['x_tran_key']) || empty($get_data['x_tran_key'])) {
                $get_data['x_tran_key'] = 'psigate1234';
            }
        }
        $x_tran_key = $get_data['x_tran_key'];
        $x_login = $this->decrypt($x_login);
        $x_tran_key = $this->decrypt($x_tran_key);
        $get_data['x_amount'] = number_format($get_data['x_amount'], 2);
        $x_amount = $get_data['x_amount'];
        $psi_gate_test = $this->getConfigData('test');
        if ($psi_gate_test) {
            $psi_gate_api_url = 'https://realtimestaging.psigate.com/xml';
        } else {
            $psi_gate_api_url = 'https://realtime.psigate.com/xml';
        }
        $psi_u = true;
        $psi_u_amount = false;
        //$x_invo_amount = htmlentities($get_data['x_invoice_num']) . $get_data['x_amount'] . '-' . substr(uniqid() , -4);
        $x_invo_amount = htmlentities($get_data['x_invoice_num']);
        if ($get_data['x_type'] == 'AUTH_CAPTURE') {
            $ref_id = 0;
        } elseif ($get_data['x_type'] == 'AUTH_ONLY') {
            $ref_id = 1;
        } elseif ($get_data['x_type'] == 'CAPTURE_ONLY' || $get_data['x_type'] == 'PRIOR_AUTH_CAPTURE') {
            $ref_id = 2;
            $x_invo_amount = '';
            if (isset($get_data['x_trans_id'])) {
                $x_invo_amount = $get_data['x_trans_id'];
            }
            if ($x_invo_amount == '') {
                self::throwException('Could not get transaction(Order) Id');
            }
            $psi_u = false;
        } elseif ($get_data['x_type'] == 'CREDIT') {
            $ref_id = 3;
            $x_invo_amount = '';
            if (isset($get_data['x_trans_id'])) {
                $x_invo_amount = $get_data['x_trans_id'];
            } else {
                $x_invo_amount = $get_data['x_invoice_num'];
            }
            if ($x_invo_amount == '') {
                self::throwException('Could not get transaction(Order) Id');
            }
            $psi_u = false;
            $psi_u_amount = true;
        } else {
            self::throwException('Unsupported Operation: ' . $get_data['x_type']);
        }
        if ($psi_u) {
            $post_fields = '<?xml version=\'1.0\' encoding=\'UTF-8\'?><Order>' . '<StoreID>' . htmlentities($x_login) . '</StoreID>' . '<Passphrase>' . htmlentities($x_tran_key) . '</Passphrase>' . '<ewayCustomerEmail>' . htmlentities($get_data['x_merchant_email']) . '</ewayCustomerEmail>' . '<Bname>' . htmlentities($get_data['x_first_name'] . ', ' . $get_data['x_last_name']) . '</Bname>' . '<Bcompany>' . htmlentities($get_data['x_company']) . '</Bcompany>' . '<Baddress1>' . htmlentities($get_data['x_address']) . '</Baddress1>';
            $post_fields .= '<Baddress2></Baddress2>' . '<Bcity>' . htmlentities($get_data['x_city']) . '</Bcity>' . '<Bprovince>' . htmlentities($get_data['x_state']) . '</Bprovince>' . '<Bpostalcode>' . htmlentities($get_data['x_zip']) . '</Bpostalcode>' . '<Bcountry>' . htmlentities($get_data['x_country']) . '</Bcountry>' . '<Sname>' . htmlentities($get_data['x_ship_to_first_name'] . ', ' . $get_data['x_ship_to_last_name']) . '</Sname>' . '<Scompany>' . htmlentities($get_data['x_ship_to_company']) . '</Scompany>' . '<Saddress1>' . htmlentities($get_data['x_ship_to_address']) . '</Saddress1>' . '<Saddress2></Saddress2>' . '<Scity>' . htmlentities($get_data['x_ship_to_city']) . '</Scity>' . '<Sprovince>' . htmlentities($get_data['x_ship_to_state']) . '</Sprovince>' . '<Spostalcode>' . htmlentities($get_data['x_ship_to_zip']) . '</Spostalcode>' . '<Scountry>' . htmlentities($get_data['x_ship_to_country']) . '</Scountry>';
            $post_fields .= '<Email>' . htmlentities($get_data['x_email']) . '</Email>' . '<Phone>' . htmlentities($get_data['x_phone']) . '</Phone>' . '<Fax>' . htmlentities($get_data['x_fax']) . '</Fax>' . '<Comments></Comments>' . "<OrderID>{$x_invo_amount}</OrderID>" . '<Subtotal>' . htmlentities($x_amount) . '</Subtotal>' . '<PaymentType>CC</PaymentType>' . "<CardAction>{$ref_id}</CardAction>" . '<CardNumber>' . htmlentities($get_data['x_card_num']) . '</CardNumber>' . '<CardExpMonth>' . htmlentities(substr($get_data['x_exp_date'], 0, 2)) . '</CardExpMonth>' . '<CardExpYear>' . htmlentities(substr($get_data['x_exp_date'], -2)) . '</CardExpYear>' . '<CustomerIP>' . htmlentities($_SERVER['REMOTE_ADDR']) . '</CustomerIP>' . '<CardIDNumber>' . htmlentities($get_data['x_card_code']) . '</CardIDNumber>' . '</Order>';
        } else {
            if ($psi_u_amount) {
                $x_amt = $get_data['x_amount'];
                $post_fields = '<?xml version=\'1.0\' encoding=\'UTF-8\'?><Order>' . '<StoreID>' . htmlentities($x_login) . '</StoreID>' . '<Passphrase>' . htmlentities($x_tran_key) . '</Passphrase>' . "<OrderID>{$x_invo_amount}</OrderID>" . '<PaymentType>CC</PaymentType>' . "<CardAction>{$ref_id}</CardAction>" . '<Subtotal>' . htmlentities($x_amt) . '</Subtotal>' . '</Order>';
            } else {
                $post_fields = '<?xml version=\'1.0\' encoding=\'UTF-8\'?><Order>' . '<StoreID>' . htmlentities($x_login) . '</StoreID>' . '<Passphrase>' . htmlentities($x_tran_key) . '</Passphrase>' . "<OrderID>{$x_invo_amount}</OrderID>" . '<PaymentType>CC</PaymentType>' . "<CardAction>{$ref_id}</CardAction>" . '</Order>';
            }
        }
        $psi_gate_api = array();
        $psi_gate_api['response_code'] = '0';
        $psi_gate_api['response_subcode'] = '0';
        $psi_gate_api['response_reason_code'] = '0';
        $psi_gate_api['response_reason_text'] = '';
        $psi_gate_api['approval_code'] = '000000';
        $psi_gate_api['avs_result_code'] = 'P';
        $psi_gate_api['transaction_id'] = '0';
        $session = curl_init();
        curl_setopt($session, CURLOPT_URL, $psi_gate_api_url);
        curl_setopt($session, CURLOPT_POST, 1);
        curl_setopt($session, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($session, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($session, CURLOPT_POSTFIELDS, $post_fields);
        curl_setopt($session, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($session, CURLOPT_TIMEOUT, 240);
        curl_setopt($session, CURLOPT_SSLVERSION, 1);
        $post_request = curl_exec($session);
        $c_error = curl_error($session);
        $c_errno = curl_errno($session);
        curl_close($session);
        if ($c_errno != CURLE_OK) {
            $psi_gate_api['response_reason_code'] = $c_errno + 1000;
            $psi_gate_api['response_reason_text'] = $c_error;
            return $psi_gate_api;
        }
        preg_match_all('/<(.*?)>(.*?)\\</', $post_request, $preg_order, PREG_SET_ORDER);
        $trans_type = array();
        $preg_orders = 0;
        while (isset($preg_order[$preg_orders])) {
            $trans_type[$preg_order[$preg_orders][1]] = strip_tags($preg_order[$preg_orders][0]);
            $preg_orders++;
        }
        
        if (isset($trans_type['Approved']) && $trans_type['Approved'] == 'APPROVED') {
            $psi_gate_api['response_code'] = '1';
            $psi_gate_api['response_subcode'] = '1';
            $psi_gate_api['response_reason_code'] = '1';
            if (isset($trans_type['TransactionType']) && !empty($trans_type['TransactionType'])) {
                $psi_gate_api['response_reason_text'] = $trans_type['TransactionType'];
            }
            if (isset($trans_type['ReturnCode']) && !empty($trans_type['ReturnCode'])) {
                $psi_gate_api['approval_code'] = $trans_type['ReturnCode'];
            }
            if (isset($trans_type['AVSResult']) && !empty($trans_type['AVSResult'])) {
                $psi_gate_api['avs_result_code'] = $trans_type['AVSResult'];
            }
            $psi_gate_api['transaction_id'] = $x_invo_amount;
        } else {
            $psi_gate_api['response_code'] = '0';
            $psi_gate_api['response_subcode'] = '0';
            $psi_gate_api['response_reason_code'] = '0';
            if (isset($trans_type['ErrMsg']) && !empty($trans_type['ErrMsg'])) {
                $psi_gate_api['response_reason_text'] = $trans_type['ErrMsg'];
            }
            if (!isset($trans_type['ErrMsg']) && isset($trans_type['h1'])) {
                $psi_gate_api['response_reason_text'] = $trans_type['h1'];
            }
            $psi_gate_api['approval_code'] = '000000';
            $psi_gate_api['avs_result_code'] = 'P';
            $psi_gate_api['transaction_id'] = '0';
        }
        return $psi_gate_api;
    }
    
    function conv($zero = 0, $usd = 'USD', $cad = 'CAD')
    {
        $rate = self::getModel('directory/mysql4_currency');
        $get_rate = $rate->getRate($usd, $cad);
        if (isset($get_rate) && !empty($get_rate)) {
            return round($zero * $get_rate, 2);
        }
        $get_rate = $this->getConfigData('exch_rate');
        if (isset($get_rate) && !empty($get_rate)) {
            return round($zero * $get_rate, 2);
        }
        if (!isset($this->pth) || empty($this->pth)) {
            $self_config = self::getConfig();
            $this->pth = $self_config->getBaseDir();
        }
        $conv_cache = $this->pth . '/var/cache/eurofxref-daily.xml';
        if (time() - @filemtime($conv_cache) > 36000) {
            $conv_eurofxref = file('http://www.ecb.int/stats/eurofxref/eurofxref-daily.xml');
            $conv_fopen = fopen($conv_cache, 'w');
            foreach ($conv_eurofxref as $conv_eurofxrefs) {
                fputs($conv_fopen, $conv_eurofxrefs);
            }
            fclose($conv_fopen);
        } else {
            $conv_eurofxref = file($conv_cache);
        }
        $curr_eur = array();
        $curr_eur['EUR'] = 1.0;
        $conv_match = $conv_mat = array();
        $curr_eur['EUR'] = 1.0;
        $conv_match = $conv_mat = array();
        foreach ($conv_eurofxref as $conv_eurofxrefs) {
            preg_match('/currency=\'([[:alpha:]]+)\'/', $conv_eurofxrefs, $conv_match);
            if (preg_match('/rate=\'([[:graph:]]+)\'/', $conv_eurofxrefs, $conv_mat)) {
                $curr_eur[$conv_match[1]] = $conv_mat[1];
            }
        }
        $conv_total = $zero / $curr_eur[$usd];
        return round($conv_total * $curr_eur[$cad], 2);
    }
    
    function getLineItems($order)
    {
        $cart_items = array();
        $cart_array = array();
        $calcu_price = 0;
        $qty_ordered = 0;
        $discount_amt = 0;
        $get_all_items = $order->getAllItems();
        if ($get_all_items) {
            $line_itm = 0;
            foreach ($get_all_items as $get_all_item) {
                if ($get_all_item->getParentItem()) {
                    continue;
                }
                if ($get_all_item->getQty() == '') {
                    $tax_amount = 0;
                    if ($get_all_item->getBaseTaxAmount() > 0) {
                        $tax_amount = $get_all_item->getBaseTaxAmount() / $get_all_item->getQtyOrdered();
                    }
                    $base_discount_amount = $get_all_item->getBaseDiscountAmount() / $get_all_item->getQtyOrdered();
                    $commodity_code = $get_all_item->getCommodityCode();
                    $unit_measure = $get_all_item->getUnitOfMeasure();
                    $cart_items[$line_itm] = array(
                        'item_name' => $get_all_item->getName() ,
                        'item_number' => $get_all_item->getSku() ,
                        'quantity' => sprintf('%d', $get_all_item->getQtyOrdered()) ,
                        'individual_discount' => sprintf('%.2f', $base_discount_amount) ,
                        'amount' => sprintf('%.2f', $get_all_item->getBasePrice() - $base_discount_amount + $tax_amount) ,
                        'base_price' => sprintf('%.2f', $get_all_item->getBasePrice() - $base_discount_amount) ,
                        'individual_total' => sprintf('%.2f', ($get_all_item->getBasePrice() - $base_discount_amount + $tax_amount) * $get_all_item->getQtyOrdered()) ,
                        'tax' => sprintf('%.2f', $tax_amount) ,
                        'commodity_code' => $commodity_code,
                        'unit_of_measure' => $unit_measure
                    );
                    $calcu_price += ($get_all_item->getBasePrice() - $base_discount_amount + $tax_amount) * $get_all_item->getQtyOrdered();
                    $qty_ordered += $tax_amount * $get_all_item->getQtyOrdered();
                    $discount_amt += $base_discount_amount * $get_all_item->getQtyOrdered();
                } else {
                    $tax_amount = 0;
                    $get_base_dis_amt = $get_all_item->getBaseDiscountAmount();
                    if ($get_all_item->getBaseTaxAmount() > 0) {
                        $tax_amount = $get_all_item->getBaseTaxAmount() / $get_all_item->getQty();
                    }
                    $base_discount_amount = $get_all_item->getBaseDiscountAmount() / $get_all_item->getQty();
                    $commodity_code = $get_all_item->getCommodityCode();
                    $unit_measure = $get_all_item->getUnitOfMeasure();
                    $cart_items[$line_itm] = array(
                        'item_name' => $get_all_item->getName() ,
                        'item_number' => $get_all_item->getSku() ,
                        'quantity' => sprintf('%d', $get_all_item->getQty()) ,
                        'individual_discount' => sprintf('%.2f', $base_discount_amount) ,
                        'amount' => sprintf('%.2f', $get_all_item->getBaseCalculationPrice() - $get_all_item->getBaseDiscountAmount() + $tax_amount) ,
                        'base_price' => sprintf('%.2f', $get_all_item->getBaseCalculationPrice() - $base_discount_amount) ,
                        'individual_total' => sprintf('%.2f', ($get_all_item->getBaseCalculationPrice() - $base_discount_amount + $tax_amount) * $get_all_item->getQty()) ,
                        'tax' => sprintf('%.2f', $tax_amount) ,
                        'commodity_code' => $commodity_code,
                        'unit_of_measure' => $unit_measure
                    );
                    $calcu_price += ($get_all_item->getBaseCalculationPrice() - $base_discount_amount + $tax_amount) * $get_all_item->getQty();
                    $qty_ordered += $tax_amount * $get_all_item->getQtyOrdered();
                    $discount_amt += $base_discount_amount * $get_all_item->getQtyOrdered();
                }
                $line_itm++;
            }
        }
        $cart_array['items'] = $cart_items;
        $cart_array['shipping_desc'] = $order->getShippingDescription();
        $cart_array['shipping_amount'] = sprintf('%.2f', $order->getShippingAmount() + $order->getShippingTaxAmount());
        $cart_array['subtotal_amount'] = sprintf('%.2f', $calcu_price);
        $cart_array['tax_amount'] = sprintf('%.2f', $qty_ordered);
        $cart_array['discount_amount'] = sprintf('%.2f', $discount_amt);
        $cart_array['grandtotal_amount'] = sprintf('%.2f', $cart_array['shipping_amount'] + $cart_array['subtotal_amount']);
        return $cart_array;
    }
    
    public static function throwException($thr_exe = null)
    {
        if (is_null($thr_exe)) {
            $thr_exe = __('Payment error occurred.');
        }
        throw new \Magento\Framework\Exception\LocalizedException(new \Magento\Framework\Phrase($thr_exe));
    }
    
    public static function getResourceModel($res_model)
    {
        $res_inst = \Magento\Framework\App\ObjectManager::getInstance();
        return $res_inst->get($res_model);
    }
    
    public function decrypt($decrypt)
    {
        return self::getResourceModel('\\Magento\\Framework\\Encryption\\EncryptorInterface')->decrypt($decrypt);
    }
    
    public function getBaseDir()
    {
        return self::getResourceModel('\\Magento\\Framework\\Filesystem')->getDirectoryWrite(DirectoryList::ROOT)
            ->getAbsolutePath();
    }
    
    function logit($login_i, $login_it = array())
    {
        
    }
}

