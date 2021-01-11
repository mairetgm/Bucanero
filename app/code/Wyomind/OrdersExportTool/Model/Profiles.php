<?php

/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\OrdersExportTool\Model;

use Wyomind\Framework\Helper\Progress as ProgressHelper;
/**
 * Profiles model
 *
 */
class Profiles extends \Magento\Framework\Model\AbstractModel
{
    /**
     *
     */
    const TMP_DIR = '/var/tmp/';
    /**
     * @var bool
     */
    public $logEnabled = false;
    /**
     * @var bool
     */
    public $isCron = false;
    /**
     * @var bool
     */
    public $limit = false;
    /**
     * @var int
     */
    public $inc = 0;
    /**
     * @var string
     */
    public $error = 'Invalid license !!!';
    /**
     * @var bool
     */
    private $_isPreview = false;
    /**
     * @var null
     */
    private $_currentTime = null;
    /**
     * @var array
     */
    private $_params = [];
    /**
     * @var array
     */
    private $_counter = [];
    /**
     * @var bool
     */
    private $_variables = false;
    /**
     * @var array
     */
    private $_billingAddressIds = [];
    /**
     * @var array
     */
    private $_shippingAddressIds = [];
    /**
     * @var array
     */
    private $_orders = [];
    /**
     * @var array
     */
    private $_payments = [];
    /**
     * @var array
     */
    private $_invoices = [];
    /**
     * @var array
     */
    private $_creditmemos = [];
    /**
     * @var array
     */
    private $_shipments = [];
    /**
     * @var array
     */
    private $_references = [];
    /**
     * @var array
     */
    private $_products = [];
    /**
     * @var array
     */
    private $_initializedTypes = [];
    /**
     * @var array
     */
    private $_orderIds = [];
    /**
     * @var array
     */
    private $_customVariables = [];
    /**
     * @var array
     */
    private $_attributesFilter = [];
    /**
     * @var null|ResourceModel\Functions\CollectionFactory
     */
    public $functionCollectionFactory = null;
    /**
     * @var null|ResourceModel\Variables\CollectionFactory
     */
    public $variablesCollectionFactory = null;
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Address\CollectionFactory|null
     */
    public $addressCollectionFactory = null;
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Creditmemo\CollectionFactory|null
     */
    public $creditmemoCollectionFactory = null;
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory|null
     */
    public $invoiceCollectionFactory = null;
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory|null
     */
    public $itemCollectionFactory = null;
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Payment\CollectionFactory|null
     */
    public $paymentCollectionFactory = null;
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory|null
     */
    public $shipmentCollectionFactory = null;
    /**
     * @var null
     */
    private $_billingAddresses = null;
    /**
     * @var null
     */
    private $_shippingAddresses = null;
    /**
     * @var \Magento\Framework\Event\Manager|null
     */
    protected $_eventManager = null;
    /**
     * @var \Wyomind\OrdersExportTool\Helper\Progress
     */
    private $progressHelper;
    /**
     * @var bool
     */
    private $isPreview = false;
    public function __construct(\Wyomind\OrdersExportTool\Helper\Delegate $wyomind, \Magento\Framework\Model\Context $context, \Magento\Framework\Registry $registry, ResourceModel\Functions\CollectionFactory $functionCollectionFactory, ResourceModel\Variables\CollectionFactory $variablesCollectionFactory, \Magento\Sales\Model\ResourceModel\Order\Address\CollectionFactory $addressCollectionFactory, \Magento\Sales\Model\ResourceModel\Order\Creditmemo\CollectionFactory $creditmemoCollectionFactory, \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory $invoiceCollectionFactory, \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $itemCollectionFactory, \Magento\Sales\Model\ResourceModel\Order\Payment\CollectionFactory $paymentCollectionFactory, \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory $shipmentCollectionFactory, \Magento\Framework\Model\ResourceModel\AbstractResource $abstractResource = null, \Magento\Framework\Data\Collection\AbstractDb $abstractDb = null, array $data = [])
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        $this->framework->constructor($this, func_get_args());
        $this->functionCollectionFactory = $functionCollectionFactory;
        $this->variablesCollectionFactory = $variablesCollectionFactory;
        $this->addressCollectionFactory = $addressCollectionFactory;
        $this->creditmemoCollectionFactory = $creditmemoCollectionFactory;
        $this->invoiceCollectionFactory = $invoiceCollectionFactory;
        $this->itemCollectionFactory = $itemCollectionFactory;
        $this->paymentCollectionFactory = $paymentCollectionFactory;
        $this->shipmentCollectionFactory = $shipmentCollectionFactory;
        $this->_eventManager = $context->getEventDispatcher();
        parent::__construct($context, $registry, $abstractResource, $abstractDb, $data);
        $this->progressHelper = $this->objectManager->create("Wyomind\\OrdersExportTool\\Helper\\Progress");
    }
    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init('Wyomind\\OrdersExportTool\\Model\\ResourceModel\\Profiles');
    }
    /**
     * Retrieve params from the request or from the model itself
     * @param Request $request
     */
    public function extractParams($request)
    {
        $resource = $this->appResource;
        $read = $resource->getConnection('core_read');
        $table = $resource->getTableName("ordersexporttool_profiles");
        $fields = $read->describeTable($table);
        foreach (array_keys($fields) as $field) {
            $this->_params[$field] = $request != null && (is_string($request->getParam($field)) || is_array($request->getParam($field))) ? $request->getParam($field) : $this->getData($field);
        }
    }
    /**
     * Return the data related to an instance
     * @param string $reference
     * @param Object $order
     * @return array
     */
    public function checkReference($reference, $order)
    {
        if ($reference == 'billing' && isset($this->_billingAddresses[$order->getBillingAddressId()])) {
            return $this->_billingAddresses[$order->getBillingAddressId()];
        } elseif ($reference == 'shipping' && isset($this->_shippingAddresses[$order->getShippingAddressId()])) {
            return $this->_shippingAddresses[$order->getShippingAddressId()];
        } elseif ($reference == 'payment' && isset($this->_payments[$order->getEntityId()])) {
            return $this->_payments[$order->getEntityId()];
        } elseif ($reference == 'invoice' && isset($this->_invoices[$order->getEntityId()])) {
            return $this->_invoices[$order->getEntityId()];
        } elseif ($reference == 'shipment' && isset($this->_shipments[$order->getEntityId()])) {
            return $this->_shipments[$order->getEntityId()];
        } elseif ($reference == 'creditmemo' && isset($this->_creditmemos[$order->getEntityId()])) {
            return $this->_creditmemos[$order->getEntityId()];
        } elseif ($reference == 'product' && isset($this->_products[$order->getEntityId()])) {
            return $this->_products[$order->getEntityId()];
        } elseif ($reference == null || $reference == 'order') {
            return [$order];
        }
    }
    /**
     * Load custom functions from DB and instantiate them
     * @throws \Exception
     */
    public function loadCustomFunctions()
    {
        $displayErrors = ini_get('display_errors');
        ini_set('display_errors', 0);
        $collection = $this->functionCollectionFactory->create();
        foreach ($collection as $function) {
            try {
                if ($this->helper->execPhp($function->getScript(), "?>" . $function->getScript()) === false) {
                    if ($this->_isPreview || $this->isCron || $this->debugEnabled) {
                        ini_set('display_errors', $displayErrors);
                        throw new \Exception("Syntax error in " . $function->getScript() . ' : ' . error_get_last()["message"]);
                    } else {
                        ini_set('display_errors', $displayErrors);
                        $this->addError("Syntax error in <i>" . $function->getScript() . "</i><br>" . error_get_last()["message"]);
                        throw new \Exception();
                    }
                }
            } catch (\Exception $e) {
                if ($this->_isPreview || $this->isCron || $this->debugEnabled) {
                    throw new \Exception($e->getMessage());
                } else {
                    $this->addError($e->getMessage());
                    throw new \Exception();
                }
            }
        }
        ini_set('display_errors', $displayErrors);
    }
    /**
     * Analyse a profile template
     * @param array $instances
     * @throws \Exception
     */
    public function analyseTemplate($instances)
    {
        if (!$this->isCron) {
            $this->loadCustomFunctions();
        }
        $columns = [];
        $collection = $this->variablesCollectionFactory->create();
        foreach ($collection as $variable) {
            $columns[$variable->getScope()][] = $variable->getName();
            $this->_customVariables[$variable->getScope() . "." . $variable->getName()] = $variable->getScript();
        }
        $this->progressHelper->log('Custom variables loaded', !$this->isPreview);
        foreach ($instances as $instance) {
            $resource = $this->appResource;
            $read = $resource->getConnection($this->helper->getConnection($instance['connection']));
            $tableSfo = $resource->getTableName($instance['table']);
            $fields = $read->describeTable($tableSfo);
            $this->_references[] = $instance['syntax'];
            foreach (array_keys($fields) as $field) {
                $columns[$instance['syntax']][] = $field;
            }
        }
        $pattern = '/(?<pattern>{{\\s*(?<reference>' . implode('|', $this->_references) . '|[a-z]+).(?<variable>[a-zA-Z_0-9]+)(\\s+(?<parameters>.*))?}})/U';
        preg_match_all($pattern, $this->_params['body'], $matches);
        $variables = [];
        foreach ($matches['variable'] as $key => $attr) {
            $php = null;
            if (!in_array($matches['reference'][$key], $this->_references)) {
                if ($this->_isPreview || $this->isCron) {
                    throw new \Exception("Unknown reference " . $matches['reference'][$key] . " in " . $matches['pattern'][$key] . '.');
                } else {
                    $this->addError("Unknown reference " . $matches['reference'][$key] . " in <i>" . $matches['pattern'][$key] . '</i>.');
                    throw new \Exception();
                }
            }
            if (!in_array($matches['variable'][$key], $columns[$matches['reference'][$key]])) {
                if ($this->_isPreview || $this->isCron) {
                    throw new \Exception('Unknown variable ' . $matches['variable'][$key] . ' in ' . $matches['pattern'][$key] . '.');
                } else {
                    $this->addError("Unknown variable " . $matches['variable'][$key] . ' in <i>' . $matches['pattern'][$key] . '</i>.');
                    throw new \Exception();
                }
            }
            $variables[$key]['pattern'] = $matches['pattern'][$key];
            $variables[$key]['variable'] = $matches['variable'][$key];
            $variables[$key]['reference'] = $matches['reference'][$key];
            $paramPattern = '/(?<name>\\b\\w+\\b)\\s*=\\s*(?<value>"[^"]*"|\'[^\']*\'|[^"\'<>\\s]+)/';
            preg_match_all($paramPattern, stripslashes($matches['parameters'][$key]), $parameters);
            $variables[$key]["parameters"] = [];
            $this->_initializedTypes[$matches['reference'][$key]] = true;
            foreach (array_keys($parameters['name']) as $k) {
                if ($parameters['name'][$k] == 'output') {
                    $php = $parameters['value'][$k];
                    $variables[$key]['php'] = $php;
                } else {
                    if ($parameters['name'][$k] == 'as') {
                        $as = $parameters['value'][$k];
                        $variables[$key]['as'] = $as;
                    }
                }
                if ($parameters['name'][$k] == 'if') {
                    $if = $parameters['value'][$k];
                    $variables[$key]['as'] = $if;
                } else {
                    $variables[$key]['parameters'][] = ['name' => $parameters['name'][$k], 'value' => $parameters['value'][$k]];
                }
            }
            if (!isset($this->_customVariables[$matches['reference'][$key] . '.' . $matches['variable'][$key]])) {
                if ($php != null) {
                    $variables[$key]['expression'] = str_replace("\$self", "\$item->getData('" . $matches["variable"][$key] . "')", substr($php, 1, -1));
                } else {
                    $variables[$key]['expression'] = "/* */" . $matches["variable"][$key];
                }
            } else {
                if ($php != null) {
                    $variables[$key]['expression'] = str_replace("\$self", $this->_customVariables[$matches['reference'][$key] . '.' . $matches['variable'][$key]], substr($php, 1, -1));
                } else {
                    $variables[$key]['expression'] = $this->_customVariables[$matches['reference'][$key] . '.' . $matches['variable'][$key]];
                }
            }
        }
        $this->_variables = $variables;
    }
    /**
     * @param $type
     */
    public function requireData($type)
    {
        $this->_initializedTypes[$type] = true;
    }
    /**
     * Return the order collection
     * @return collection
     */
    private function getItemCollection()
    {
        $searchCriteria = $this->searchCriteriaBuilder;
        if (is_array($this->_params['store_id'])) {
            $values = $this->_params['store_id'];
        } else {
            $values = explode(",", $this->_params['store_id']);
        }
        //scope
        $searchCriteria->addFilter('store_id', $values, 'in');
        //scope
        if ($this->_params['last_exported_id'] != '') {
            if (is_numeric($this->_params['last_exported_id'])) {
                $searchCriteria->addFilter('increment_id', (int) $this->_params['last_exported_id'], 'gteq');
            } else {
                $searchCriteria->addFilter('increment_id', $this->_params['last_exported_id'], 'gteq');
            }
        }
        //order
        $searchCriteria->addFilter('status', explode(',', $this->_params['states']), 'in');
        //scope
        $searchCriteria->addFilter('customer_group_id', explode(',', $this->_params['customer_groups']), 'in');
        if (isset($this->_attributesFilter['order'])) {
            foreach ($this->_attributesFilter['order'] as $attributeFilter) {
                if ($attributeFilter->checked) {
                    if ($attributeFilter->condition == 'in' || $attributeFilter->condition == 'nin') {
                        $value = explode(',', $attributeFilter->value);
                    } else {
                        $value = $attributeFilter->value;
                    }
                    $searchCriteria->addFilter($attributeFilter->code, $value, $attributeFilter->condition);
                }
            }
        }
        $collection = $this->orderRepository->getList($searchCriteria->create());
        return $collection->getItems();
    }
    /**
     * Check the order to include
     */
    private function extractOrders()
    {
        $collection = $this->getItemCollection();
        $billingAddressIds = [];
        $shippingAddressIds = [];
        $this->_orderIds = [];
        foreach ($collection as $ord) {
            $this->_orderIds[] = $ord->getEntityId();
            $billingAddressIds[] = $ord->getBillingAddressId();
            $shippingAddressIds[] = $ord->getShippingAddressId();
        }
        $this->_billingAddressIds = $billingAddressIds;
        $this->_shippingAddressIds = $shippingAddressIds;
        unset($collection);
    }
    /**
     * Check the shipping address to include
     * @return array
     */
    public function extractShippingAddresses()
    {
        $collection = $this->addressCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->addAttributeToFilter('entity_id', ['in' => $this->_shippingAddressIds]);
        $shippingAddressIds = [];
        foreach ($collection as $shippingAddress) {
            $shippingAddressIds[$shippingAddress->getParentId()][] = $shippingAddress->getEntityId();
            $this->_shippingAddresses[$shippingAddress->getEntityId()][] = $shippingAddress;
        }
        unset($collection);
        return $shippingAddressIds;
    }
    /**
     * Check the billing address to include
     * @return array
     */
    public function extractBillingAddresses()
    {
        $collection = $this->addressCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->addAttributeToFilter('entity_id', ['in' => $this->_billingAddressIds]);
        $billingAddressIds = [];
        foreach ($collection as $billingAddress) {
            $billingAddressIds[$billingAddress->getParentId()][] = $billingAddress->getEntityId();
            $this->_billingAddresses[$billingAddress->getEntityId()][] = $billingAddress;
        }
        unset($collection);
        return $billingAddressIds;
    }
    /**
     * Check the payments to include
     * @return array
     */
    public function extractPayments()
    {
        $collection = $this->paymentCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->addAttributeToFilter('parent_id', ['in' => $this->_orderIds]);
        if (isset($this->_attributesFilter['order_payment'])) {
            foreach ($this->_attributesFilter['order_payment'] as $attributeFilter) {
                if ($attributeFilter->checked) {
                    if ($attributeFilter->condition == 'in' || $attributeFilter->condition == 'nin') {
                        $attributeFilter->value = explode(',', $attributeFilter->value);
                    }
                    $collection->addFieldToFilter($attributeFilter->code, [$attributeFilter->condition => $attributeFilter->value]);
                }
            }
        }
        $paymentsIds = [];
        foreach ($collection as $payment) {
            $paymentsIds[$payment->getParentId()][] = $payment->getEntityId();
            $this->_payments[$payment->getParentId()][] = $payment;
        }
        unset($collection);
        return $paymentsIds;
    }
    /**
     * Check the invoices to include
     * @return array
     */
    public function extractInvoices()
    {
        $collection = $this->invoiceCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->addAttributeToFilter('order_id', ['in' => $this->_orderIds]);
        if (isset($this->_attributesFilter['invoice'])) {
            foreach ($this->_attributesFilter['invoice'] as $attributeFilter) {
                if ($attributeFilter->checked) {
                    if ($attributeFilter->condition == 'in' || $attributeFilter->condition == 'nin') {
                        $attributeFilter->value = explode(',', $attributeFilter->value);
                    }
                    $collection->addFieldToFilter($attributeFilter->code, [$attributeFilter->condition => $attributeFilter->value]);
                }
            }
        }
        $invoicesIds = [];
        foreach ($collection as $invoice) {
            $invoicesIds[$invoice->getOrderId()][] = $invoice->getEntityId();
            $this->_invoices[$invoice->getOrderId()][] = $invoice;
        }
        unset($collection);
        return $invoicesIds;
    }
    /**
     * Check the shipments to include
     * @return array
     */
    public function extractShipments()
    {
        $collection = $this->shipmentCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->addAttributeToFilter('order_id', ['in' => $this->_orderIds]);
        $shipmentsIds = [];
        if (isset($this->_attributesFilter['shipment'])) {
            foreach ($this->_attributesFilter['shipment'] as $attributeFilter) {
                if ($attributeFilter->checked) {
                    if ($attributeFilter->condition == 'in' || $attributeFilter->condition == 'nin') {
                        $attributeFilter->value = explode(',', $attributeFilter->value);
                    }
                    $collection->addFieldToFilter($attributeFilter->code, [$attributeFilter->condition => $attributeFilter->value]);
                }
            }
        }
        foreach ($collection as $shipment) {
            $shipmentsIds[$shipment->getOrderId()][] = $shipment->getEntityId();
            $this->_shipments[$shipment->getOrderId()][] = $shipment;
        }
        unset($collection);
        return $shipmentsIds;
    }
    /**
     * Check the creditmemo to include
     * @return array
     */
    public function extractCreditmemos()
    {
        $collection = $this->creditmemoCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->addAttributeToFilter('order_id', ['in' => $this->_orderIds]);
        if (isset($this->_attributesFilter['creditmemo'])) {
            foreach ($this->_attributesFilter['creditmemo'] as $attributeFilter) {
                if ($attributeFilter->checked) {
                    if ($attributeFilter->condition == 'in' || $attributeFilter->condition == 'nin') {
                        $attributeFilter->value = explode(',', $attributeFilter->value);
                    }
                    $collection->addFieldToFilter($attributeFilter->code, [$attributeFilter->condition => $attributeFilter->value]);
                }
            }
        }
        $creditmemosIds = [];
        foreach ($collection as $creditmemo) {
            $creditmemosIds[$creditmemo->getOrderId()][] = $creditmemo->getEntityId();
            $this->_creditmemos[$creditmemo->getOrderId()][] = $creditmemo;
        }
        unset($collection);
        return $creditmemosIds;
    }
    /**
     * Check the items to include
     * @return array
     */
    public function extractProducts()
    {
        $bundleProducts = [];
        $groupedProducts = [];
        $configurableProducts = [];
        $collection = $this->itemCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->addFieldToFilter('order_id', ['in' => $this->_orderIds]);
        if ($this->framework->moduleIsEnabled("Wyomind_AdvancedInventory")) {
            $aia = $this->appResource->getTableName("advancedinventory_assignation");
            $collection->getSelect()->joinLeft($aia, $aia . ".item_id = main_table.item_id", ["GROUP_CONCAT(place_id) AS place_id"])->group("main_table.item_id");
        }
        if (isset($this->_attributesFilter['order_item'])) {
            foreach ($this->_attributesFilter['order_item'] as $attributeFilter) {
                if ($attributeFilter->checked) {
                    if ($attributeFilter->condition == 'in' || $attributeFilter->condition == 'nin') {
                        $attributeFilter->value = explode(',', $attributeFilter->value);
                    }
                    $collection->addFieldToFilter($attributeFilter->code, [$attributeFilter->condition => $attributeFilter->value]);
                }
            }
        }
        $productsIds = [];
        $w = 0;
        foreach ($collection as $product) {
            // Index related products
            switch ($product->getProductType()) {
                case 'configurable':
                    $configurableProducts[$product->getItemId()] = $product;
                    break;
                case 'grouped':
                    $groupedProducts[$product->getItemId()] = $product;
                    break;
                case 'bundle':
                    $bundleProducts[$product->getItemId()] = $product;
                    break;
            }
            // if main product
            if (!$product->getParentItemId()) {
                $productsIds[$product->getOrderId()][$w] = $product->getItemId();
                $this->_products[$product->getOrderId()][$w] = $product;
                $w++;
                // increment index if the product is added to the export array
            } else {
                // if child product has a parent
                // if configurable child and selected configurable
                // cannot remove count in these conditions
                if (isset($configurableProducts[$product->getParentItemId()]) && !empty($configurableProducts[$product->getParentItemId()]) && isset($this->_products[$product->getOrderId()][$w - 1])) {
                    $this->_products[$product->getOrderId()][$w - 1]->setParentItemId($product->getParentItemId());
                    $this->_products[$product->getOrderId()][$w - 1]->setItemId($product->getItemId());
                    $this->_products[$product->getOrderId()][$w - 1]->setName($product->getName());
                } elseif (isset($bundleProducts[$product->getParentItemId()]) && !empty($bundleProducts[$product->getParentItemId()]) || isset($groupedProducts[$product->getParentItemId()]) && !empty($groupedProducts[$product->getParentItemId()])) {
                    $productsIds[$product->getOrderId()][$w] = $product->getItemId();
                    $this->_products[$product->getOrderId()][$w] = $product;
                    $w++;
                    // increment index if the product is added to the export array
                }
            }
        }
        unset($collection);
        return $productsIds;
    }
    /**
     * Parse the template and analyse the sub loop
     * @param array $instances
     * @return array
     */
    public function extractSubloops($instances)
    {
        $subloops = [];
        foreach ($instances as $instance) {
            $pattern = '/(?<fullpattern><\\?php\\s+foreach\\s*?\\(\\s*?\\$' . $instance['syntax'] . 's\\s+as\\s+\\$' . $instance['syntax'] . '\\s*?\\)\\s*?:\\s+\\?>(?<pattern>.+)<\\?php\\s+endforeach\\s*?;\\s*?\\?>)/Us';
            $matches = [];
            preg_match_all($pattern, $this->_params['body'], $matches);
            if (isset($matches[1][0])) {
                $subloops[$instance['syntax']]['fullpattern'] = $matches['fullpattern'][0];
                $subloops[$instance['syntax']]['pattern'] = $matches['pattern'][0];
            }
        }
        return $subloops;
    }
    /**
     * @return array
     * @throws \Exception
     */
    public function analyseFilter()
    {
        $temp = [];
        foreach (json_decode($this->_params['attributes']) as $attribute) {
            if ($attribute->checked) {
                preg_match('/(order_payment|order_item|order|invoice|shipment|creditmemo).(.*)/', $attribute->code, $matches);
                $attribute->code = $matches[2];
                $temp[$matches[1]][] = $attribute;
                $attribute->value = $this->helper->executePhpScripts($this->_isPreview, $attribute->value);
            }
        }
        return $temp;
    }
    /**
     *
     */
    public function extractAll($preview)
    {
        // orders
        $this->extractOrders();
        $this->progressHelper->log('Orders extracted', !$this->isPreview);
        // billing
        if (isset($this->_initializedTypes['billing'])) {
            $billingAddressIds = $this->extractBillingAddresses();
            $this->progressHelper->log('Billing addresses extracted', !$this->isPreview);
        }
        // shipping
        if (isset($this->_initializedTypes['shipping'])) {
            $shippingAddressIds = $this->extractShippingAddresses();
            $this->progressHelper->log('Shipping addresses extracted', !$this->isPreview);
        }
        // payments
        if (isset($this->_initializedTypes['payment'])) {
            $paymentsIds = $this->extractPayments();
            $this->progressHelper->log('Payments extracted', !$this->isPreview);
        }
        // invoices
        if (isset($this->_initializedTypes['invoice'])) {
            $invoicesIds = $this->extractInvoices();
            $this->progressHelper->log('Invoices extracted', !$this->isPreview);
        }
        // shipments
        if (isset($this->_initializedTypes['shipment'])) {
            $shipmentsIds = $this->extractShipments();
            $this->progressHelper->log('Shipments extracted', !$this->isPreview);
        }
        // creditmemo
        if (isset($this->_initializedTypes['creditmemo'])) {
            $creditmemosIds = $this->extractCreditmemos();
            $this->progressHelper->log('Creditmemos extracted', !$this->isPreview);
        }
        // products
        $this->_params['product_type'] = "simple,configurable,grouped_parent,bundle_parent,bundle_children";
        if (isset($this->_initializedTypes['product']) && $this->_params['product_type'] != null) {
            $productIds = $this->extractProducts();
            $this->progressHelper->log('Products extracted', !$this->isPreview);
        }
    }
    /**
     * Execute a profile and generate the data
     * @param Request $request
     * @param boolean $preview
     * @return string
     * @throws \Exception
     * @ignore_var data,item,order,values
     */
    public function generate($request = null, $preview = false)
    {
        try {
            $this->isPreview = $preview;
            $this->progressHelper->startObservingProgress($this->isLogEnabled(), $this->getId(), $this->getName());
            $this->progressHelper->log("### New orders export started", !$this->isPreview);
            $this->_eventManager->dispatch("ordersexporttool_export_before", ['profile' => $this]);
            $this->helper->setProfile($this);
            $instances = $this->helper->getEntities();
            $this->inc = 1;
            $increment = 1;
            $filesList = [];
            $display = '';
            $output = '';
            $lastOrder = null;
            // Get profiles settings
            $this->extractParams($request);
            $this->progressHelper->log('Parameters loaded', !$this->isPreview);
            $this->_isPreview = $preview;
            $this->_currentTime = $this->dateTime->timestamp();
            $this->progressHelper->log("" . strtoupper($this->storageHelper->getFileName($this->_params['date_format'], $this->_params['name'], $this->_params['type'], $this->_currentTime, false)), !$this->isPreview);
            // set the memory limit size
            $limit = (string) $this->framework->getStoreConfig('ordersexporttool/system/memory_limit') . 'M';
            if ($this->framework->getStoreConfig("ordersexporttool/system/memory_limit") > 0) {
                ini_set('memory_limit', $limit);
                $this->progressHelper->log('Memory limit set to ' . $limit, !$this->isPreview);
            }
            $ioFull = null;
            // Open file
            if (!$this->_isPreview) {
                if (!$this->_params['storage_enabled']) {
                    $this->_params['path'] = self::TMP_DIR;
                }
                if (!$this->_params['repeat_for_each']) {
                    $fileName = $this->storageHelper->getFileName($this->_params['date_format'], $this->_params['name'], $this->_params['type'], $this->_currentTime);
                    $ioFull = $this->storageHelper->openDestinationFile($this->_params['path'], $fileName);
                    $this->progressHelper->log('' . $this->_params['path'] . ' ' . $fileName . ' created.', !$this->isPreview);
                }
            }
            if ($this->_params['encoding'] == 'UTF-8' && !$this->_isPreview && !$this->_params['repeat_for_each']) {
                $ioFull->write(pack('CCC', 0xef, 0xbb, 0xbf));
            }
            // extra header
            $extraHeader = $this->helper->executePhpScripts($this->_isPreview, $this->_params['extra_header']);
            $this->progressHelper->log('Php scripts parsed for Extra Header', !$this->isPreview);
            // header
            $header = $this->helper->executePhpScripts($this->_isPreview, $this->_params['header']);
            $this->progressHelper->log('Php scripts parsed for Header', !$this->isPreview);
            // If is XML or if TXT and not previewing then add the extra Header
            if ($this->helper->isXml($this->_params) || !$this->helper->isXml($this->_params) && !$this->_isPreview) {
                $extraHeader = $this->outputHelper->encode($extraHeader, $this->_params['encoding']);
                $this->progressHelper->log('Extra Header encoded', !$this->isPreview);
                $header = $this->outputHelper->encode($header, $this->_params['encoding']);
                $this->progressHelper->log('Header encoded', !$this->isPreview);
            }
            // If is preview
            if ($this->_isPreview) {
                // If is XML  then enclose data
                if ($this->helper->isXml($this->_params)) {
                    $display .= $this->outputHelper->xmlEncloseData($header, $this->_params['enclose_data'], $this->_params['type']);
                } else {
                    // If is ADVANCED then add the Extra Header + <br>
                    if (!$this->helper->isAdvanced($this->_params) && $extraHeader != '') {
                        $display .= $extraHeader . '<br>';
                    } elseif ($extraHeader != '') {
                        $display .= $extraHeader . "\r
";
                    }
                    // if not ADVANCED then create a header
                    if (!$this->helper->isAdvanced($this->_params)) {
                        $display .= '<table class="data-grid" cellspacing=0 cellpadding=0>';
                        if ($this->_params['include_header']) {
                            $display .= $this->outputHelper->jsonToTable($this, $header, $this->_params['incremental_column'], $this->_params['incremental_column_name'], true);
                        }
                    }
                }
                $this->progressHelper->log('Header /Extra Header ready for rendering', !$this->isPreview);
            } elseif (!$this->_params['repeat_for_each']) {
                // if is XML then ecnlose data
                if ($this->helper->isXml($this->_params)) {
                    $output .= $this->outputHelper->xmlEncloseData($header, $this->_params['enclose_data'], $this->_params['type']);
                } else {
                    //if Extra Header not null
                    if ($extraHeader != '') {
                        $output .= $extraHeader . "\r
";
                    }
                    // if include header then add header
                    if ($this->_params['include_header']) {
                        $output .= $this->outputHelper->jsonToStr($this, $header, $this->_params['separator'], $this->_params['protector'], $this->_params['escaper'], $this->_params['incremental_column'], $this->_params['incremental_column_name'], false);
                    }
                }
                $ioFull->write($output);
                $this->progressHelper->log('Header /Extra Header added', !$this->isPreview);
                $output = '';
            }
            // filters
            $this->_attributesFilter = $this->analyseFilter();
            $this->progressHelper->log('Filters analyzed', !$this->isPreview);
            // sub patterns
            $subloops = [];
            // if is XML or Advanced
            if ($this->helper->isXmlOrAdvanced($this->_params)) {
                $subloops = $this->extractSubloops($instances);
                $this->progressHelper->log('Subloops analyzed', !$this->isPreview);
            }
            // attributes
            $this->analyseTemplate($instances);
            $this->progressHelper->log('Attributes analyzed', !$this->isPreview);
            // extract all
            /*
             * 1. Récupérer les items en fonction de la scope
             * 2. Récupérer les sous-items et les sur-items en fonction des items
             * 3. Récupérer les data en fonction
             * 4. Parser le template
             */
            $this->extractAll($preview);
            $total = count($this->getItemCollection());
            // GO, GO, GO
            $collection = $this->getItemCollection();
            $this->progressHelper->log('Total orders to export: ' . $total, !$this->isPreview);
            $i = 1;
            // Processing order by order
            foreach ($collection as $order) {
                $this->helper->skip(false);
                $incrementMarker = null;
                $percent = $increment * 100 / $total;
                $this->progressHelper->log('Exporting order #' . $order->getIncrementId(), !$this->isPreview, ProgressHelper::PROCESSING, $percent);
                // get instances data
                $data = ['product' => $this->checkReference('product', $order), 'payment' => $this->checkReference('payment', $order), 'invoice' => $this->checkReference('invoice', $order), 'shipment' => $this->checkReference('shipment', $order), 'creditmemo' => $this->checkReference('creditmemo', $order), 'billing' => $this->checkReference('billing', $order), 'shipping' => $this->checkReference('shipping', $order), 'order' => $this->checkReference('order', $order)];
                // slip if already exported
                if ($this->_params['flag'] && $this->_params['single_export'] && in_array($this->_params['id'], explode(',', $order->getExportedTo())) && !$this->_isPreview) {
                    continue;
                }
                $ioSingle = null;
                // one file per order if needed
                if (!$this->_isPreview && $this->_params['repeat_for_each']) {
                    switch ($this->_params['repeat_for_each_increment']) {
                        case 1:
                            $incrementMarker = '-' . $order->getIncrementId();
                            break;
                        case 2:
                            $incrementMarker = '-' . $order->getEntityId();
                            break;
                        case 3:
                            $incrementMarker = '-' . $increment;
                            break;
                    }
                    $fileName = $this->storageHelper->getFileName($this->_params['date_format'], $this->_params['name'], $this->_params['type'], $this->_currentTime, '.temp', $incrementMarker);
                    $ioSingle = $this->storageHelper->openDestinationFile($this->_params['path'], $fileName);
                    if ($this->_params['encoding'] == 'UTF-8') {
                        $ioSingle->write(pack('CCC', 0xef, 0xbb, 0xbf));
                    }
                    $header = $this->helper->executePhpScripts($this->_isPreview, $this->_params['header']);
                    if ($this->helper->isXmlOrAdvanced($this->_params)) {
                        $ioSingle->write($this->outputHelper->xmlEncloseData($header, $this->_params['enclose_data'], $this->_params['type']));
                    } else {
                        if ($this->_params['include_header'] && !$this->helper->isAdvanced($this->_params)) {
                            $ioSingle->write($this->outputHelper->jsonToStr($this, $header, $this->_params['separator'], $this->_params['protector'], $this->_params['escaper'], $this->_params['incremental_column'], $this->_params['incremental_column_name'], false));
                        }
                    }
                    $this->progressHelper->log("File '" . $fileName . "' created.", !$this->isPreview, ProgressHelper::PROCESSING, $percent);
                }
                // Initial patter load
                $orderPattern = $this->_params['body'];
                $addedReference = [];
                $orderPattern = $this->helper->addQuoteToPhpScripts($orderPattern);
                foreach ($this->_variables as $key => $exp) {
                    $values = [];
                    $lines = [];
                    $subloopsData = null;
                    $reference = $exp['reference'];
                    $variable = $exp['variable'];
                    $expression = $exp['expression'];
                    $pattern = $exp['pattern'];
                    $items = (array) $this->checkReference($reference, $order);
                    $g = 0;
                    if (count($items) < 1) {
                        $values[] = null;
                    } else {
                        foreach ($items as $item) {
                            // Attributes labels management
                            if (in_array($reference, $this->_references) && isset($subloops[$reference])) {
                                $subloopsData .= $subloops[$reference]['pattern'];
                            }
                            $values[$g] = null;
                            if (!isset($this->_customVariables[$reference . '.' . $variable])) {
                                if (strpos($expression, '/* */') === 0) {
                                    $values[$g] = $item->getData(str_replace("/* */", "", $expression));
                                } else {
                                    $displayErrors = ini_get('display_errors');
                                    ini_set('display_errors', 0);
                                    try {
                                        if (($values[$g] = $this->helper->execPhp($pattern, 'return ' . $expression . ';', $order, $data, $item)) === false) {
                                            ini_set('display_errors', $displayErrors);
                                            throw new \Exception("Syntax error in " . $expression);
                                        }
                                        ini_set('display_errors', $displayErrors);
                                    } catch (\Exception $e) {
                                        ini_set('display_errors', $displayErrors);
                                        throw $e;
                                    }
                                }
                            } else {
                                $values[$g] = $this->helper->executePhpScripts($this->_isPreview, $this->_customVariables[$reference . '.' . $variable], $order, $data, $item);
                            }
                            if (!in_array($reference, $addedReference)) {
                                $lines[] = $orderPattern;
                            }
                            $g++;
                        }
                    }
                    if (!in_array($reference, $addedReference)) {
                        $addedReference[] = $reference;
                    }
                    //CSV TXT
                    if (!$this->helper->isXmlorAdvanced($this->_params) && count($lines)) {
                        $orderPattern = implode(',', $lines);
                    }
                    //XML
                    if (in_array($reference, $this->_references) && isset($subloops[$reference])) {
                        $orderPattern = str_replace($subloops[$reference]['fullpattern'], $subloopsData, $orderPattern);
                    }
                    if (is_bool($values) && !$values) {
                        continue 2;
                    }
                    foreach ($values as $arrayKey => $value) {
                        // delimiter and/or enclosure escape
                        if (!$this->helper->isXmlorAdvanced($this->_params)) {
                            $value = $this->outputHelper->escapeStr($value);
                        }
                        // temporary transformation of html tags
                        $value = str_replace(["<", ">", '"', '\\'], ['__LOWERTHAN__', '__HIGHERTHAN__', '__QUOTES__', '__BACKSLASH__'], $value);
                        $values[$arrayKey] = $value;
                    }
                    // replace the value
                    // count($values) cannot be moved out of the loop !!
                    // $values is built IN the loop !!
                    $var = substr_count($orderPattern, $pattern) / count($values);
                    $count = count($values);
                    foreach ($values as $value) {
                        $preg = '/' . preg_quote($pattern, '/') . '/';
                        if (is_array($value)) {
                            $value = implode(',', $value);
                        }
                        if ($count == 1) {
                            $orderPattern = preg_replace($preg, $value, $orderPattern);
                        } else {
                            $orderPattern = preg_replace($preg, $value, $orderPattern, $var);
                        }
                    }
                    // recompute loops with the {{order...}} replaced
                    if ($reference == 'order') {
                        $subloops = [];
                        foreach ($instances as $instance) {
                            $pattern = '/(?<fullpattern><\\?php\\s+foreach\\s*?\\(\\s*?\\$' . $instance['syntax'] . 's\\s+as\\s+\\$' . $instance['syntax'] . '\\s*?\\)\\s*?:\\s+\\?>(?<pattern>.+)<\\?php\\s+endforeach\\s*?;\\s*?\\?>)/Us';
                            $matches = [];
                            preg_match_all($pattern, $orderPattern, $matches);
                            if (isset($matches[1][0])) {
                                $subloops[$instance['syntax']]['fullpattern'] = $matches['fullpattern'][0];
                                $subloops[$instance['syntax']]['pattern'] = $matches['pattern'][0];
                            }
                        }
                        //                    }
                    }
                }
                // run PHP scripts
                $orderPattern = $this->helper->executePhpScripts($this->_isPreview, $orderPattern, $order, $data, $order, $this->getType());
                if ($this->helper->getSkip()) {
                    continue;
                }
                $this->_counter[] = '#' . $order->getIncrementId();
                // Data protected by <!CDATA[[ ]]> in the XML file
                if ($this->helper->isXmlOrAdvanced($this->_params)) {
                    $orderPattern = $this->outputHelper->xmlEncloseData($orderPattern, $this->_params['enclose_data'], $this->_params['type']);
                } else {
                    // Transformation JSON -> TXT
                    if (!$this->_isPreview) {
                        if (!$this->helper->isAdvanced($this->_params)) {
                            if (!$this->_params['include_header'] && $increment <= 1 || !$this->_params['include_header'] && $this->_params['repeat_for_each']) {
                                $breakline = false;
                            } else {
                                $breakline = true;
                            }
                            $orderPattern = $this->outputHelper->jsonToStr($this, $orderPattern, $this->_params['separator'], $this->_params['protector'], $this->_params['escaper'], $this->_params['incremental_column'], $this->_params['incremental_column_name'], $breakline);
                        }
                    } else {
                        if (!$this->helper->isAdvanced($this->_params)) {
                            $orderPattern = $this->outputHelper->jsonToTable($this, $orderPattern, $this->_params['incremental_column'], $this->_params['incremental_column_name'], false);
                        }
                    }
                }
                if ($this->helper->isXml($this->_params) || !$this->helper->isXml($this->_params) && !$this->_isPreview) {
                    $orderPattern = $this->outputHelper->encode($orderPattern, $this->_params['encoding']);
                }
                // Retrieve html tags
                $orderPattern = str_replace(['__LOWERTHAN__', '__HIGHERTHAN__', '__QUOTES__', '__BACKSLASH__'], ["<", ">", '"', '\\'], $orderPattern);
                // Output preparation
                if (!empty($orderPattern)) {
                    $output .= $orderPattern . '';
                    // Write output to file or display
                    if ($this->_isPreview) {
                        $display .= $output;
                        $output = '';
                    } else {
                        if ($i % $this->framework->getStoreConfig('ordersexporttool/system/buffer') == 0) {
                            if (!$this->_params['repeat_for_each']) {
                                $ioFull->write($output);
                                unset($output);
                                $output = '';
                            }
                        }
                    }
                    // break if number reached
                    if ($this->limit && $i >= $this->limit) {
                        break 1;
                    }
                    $i++;
                    $increment++;
                    $extraFooter = $this->helper->executePhpScripts($this->_isPreview, $this->_params['extra_footer']);
                    $this->progressHelper->log('Php scripts parsed for Extra Footer', !$this->isPreview, ProgressHelper::PROCESSING, $percent);
                    $extraFooter = $this->outputHelper->encode($extraFooter, $this->_params['encoding']);
                    $this->progressHelper->log('Extra Footer encoded', !$this->isPreview, ProgressHelper::PROCESSING, $percent);
                    // close file per order
                    if (!$this->_isPreview && $this->_params['repeat_for_each']) {
                        $ioSingle->write($output);
                        if ($this->helper->isXml($this->_params) && strlen(trim($this->_params['footer'])) > 1) {
                            $ioSingle->write($this->_params['footer']);
                        } elseif (strlen(trim($extraFooter)) > 1) {
                            $ioSingle->write($extraFooter);
                        }
                        $ioSingle->close();
                        $root = $this->storageHelper->getAbsoluteRootDir() . $this->_params['path'];
                        $fileName = $this->storageHelper->getFileName($this->_params['date_format'], $this->_params['name'], $this->_params['type'], $this->_currentTime, false, $incrementMarker);
                        $fileNameTemp = $this->storageHelper->getFileName($this->_params['date_format'], $this->_params['name'], $this->_params['type'], $this->_currentTime, '.temp', $incrementMarker);
                        $this->ioFile->rm($root . $fileName);
                        $this->ioFile->mv($root . $fileNameTemp, $root . $fileName);
                        $this->ioFile->rm($root . $fileNameTemp);
                        $this->_eventManager->dispatch('ordersexporttool_export_after', ['profile' => $this, 'filepath' => $root, 'filename' => $fileName]);
                        $output = null;
                        if ($increment > 1) {
                            $filesList[] = $fileName;
                        }
                        // upload to ftp if needed
                        if ($this->_params['ftp_enabled'] && $increment > 1) {
                            $this->ftpHelper->ftpUpload($this->_params['use_sftp'], $this->_params['ftp_active'], $this->_params['ftp_host'], $this->_params['ftp_port'], $this->_params['ftp_login'], $this->_params['ftp_password'], $this->_params['ftp_dir'], $this->_params['path'], $fileName);
                            $this->_eventManager->dispatch("ordersexporttool_upload_after", ['profile' => $this, 'filepath' => $this->_params['path'], 'filename' => $fileName]);
                        }
                    }
                    // register last order
                    $lastOrder = $order;
                }
                // change order status if needed
                if ($this->_params['update_status'] && !$this->_isPreview && $orderPattern != '') {
                    $status = explode('-', $this->_params['update_status_to']);
                    $order->setData('state', $status[0]);
                    $order->setStatus($status[1]);
                    $history = $order->addStatusHistoryComment($this->_params['update_status_message'], false);
                    $history->setIsCustomerNotified(false);
                    $order->save();
                    $this->progressHelper->log('[' . $order->getIncrementId() . '] Status updated to ' . $status[1], !$this->isPreview, ProgressHelper::PROCESSING, $percent);
                }
                // update exported orders flag
                if ($this->_params['flag'] && !$this->_isPreview && $orderPattern != '') {
                    $flags = explode(',', $order->getExportedTo());
                    $flags[] = $this->_params['id'];
                    $flags = array_unique($flags);
                    // update order
                    $order->setExportedTo(implode(',', $flags));
                    $order->save();
                    $connection = $this->appResource->getConnection($this->helper->getConnection('sales'));
                    $tableSog = $this->appResource->getTableName('sales_order_grid');
                    try {
                        $connection->update($tableSog, ['exported_to' => implode(',', $flags)], "entity_id = '" . $order->getId() . "'");
                    } catch (\Exception $e) {
                        $this->addError($e->getMessage());
                    }
                    $this->progressHelper->log('[' . $order->getIncrementId() . '] Flag updated to ' . implode(',', $flags), !$this->isPreview, ProgressHelper::PROCESSING, $percent);
                }
                $this->_eventManager->dispatch('ordersexporttool_export_order', ['order' => $order, 'preview' => $this->_isPreview]);
            }
            //end foreach
            unset($collection);
            $extraFooter = $this->helper->executePhpScripts($this->_isPreview, $this->_params['extra_footer']);
            $this->progressHelper->log('Php scripts parsed for Extra Footer', !$this->isPreview, ProgressHelper::PROCESSING, 100);
            $extraFooter = $this->outputHelper->encode($extraFooter, $this->_params['encoding']);
            $this->progressHelper->log('Extra Footer encoded', !$this->isPreview, ProgressHelper::PROCESSING, 100);
            // Finalisation and add footer
            if (!$this->_isPreview && !$this->_params['repeat_for_each']) {
                $ioFull->write($output);
                if ($this->_params['type'] == 1) {
                    if (strlen(trim($this->_params['footer'])) > 1) {
                        $ioFull->write($this->_params['footer']);
                    }
                } else {
                    if (strlen(trim($extraFooter)) > 1) {
                        $ioFull->write($extraFooter);
                    }
                }
                $ioFull->close();
            }
            if ($this->_isPreview) {
                $display .= $output;
                if ($this->helper->isXml($this->_params)) {
                    $display .= $this->_params['footer'] . "\r
";
                } else {
                    $display .= $extraFooter;
                }
                if (!$this->helper->isXmlOrAdvanced($this->_params)) {
                    if (!count($this->_counter)) {
                        $display .= '<tr><td><b>' . __('No data') . '</b></td></tr>';
                    }
                    $display .= '</table>';
                }
                return $display;
            } else {
                // end
                if (!$this->_params['repeat_for_each']) {
                    $root = $this->storageHelper->getAbsoluteRootDir() . $this->_params['path'];
                    $fileName = $this->storageHelper->getFileName($this->_params['date_format'], $this->_params['name'], $this->_params['type'], $this->_currentTime);
                    if (count($this->_counter)) {
                        // don't create the final file is no order
                        $fileNameFalse = $this->storageHelper->getFileName($this->_params['date_format'], $this->_params['name'], $this->_params['type'], $this->_currentTime, false);
                        $this->ioFile->rm($root . $fileNameFalse);
                        $this->ioFile->mv($root . $fileName, $root . $fileNameFalse);
                        $this->_eventManager->dispatch("ordersexporttool_export_after", ['profile' => $this, 'filepath' => $root, 'filename' => $fileNameFalse]);
                        $filesList[] = $fileNameFalse;
                    }
                    $this->ioFile->rm($root . $fileName);
                }
                // profile update
                $this->setUpdatedAt($this->_currentTime);
                if ($this->_params['automatically_update_last_order_id'] && !empty($lastOrder)) {
                    if (!is_numeric($lastOrder->getIncrement_id())) {
                        $this->setLastExportedId($lastOrder->getIncrement_id());
                    } else {
                        $this->setLastExportedId($lastOrder->getIncrement_id() + 1);
                    }
                }
                $this->save();
                // upload to ftp
                $fileNameFalse = $this->storageHelper->getFileName($this->_params['date_format'], $this->_params['name'], $this->_params['type'], $this->_currentTime, false);
                if ($this->_params['ftp_enabled'] && !$this->_params['repeat_for_each'] && $increment > 1) {
                    $this->ftpHelper->ftpUpload($this->_params['use_sftp'], $this->_params['ftp_active'], $this->_params['ftp_host'], $this->_params['ftp_port'], $this->_params['ftp_login'], $this->_params['ftp_password'], $this->_params['ftp_dir'], $this->_params['path'], $fileNameFalse);
                    $this->_eventManager->dispatch("ordersexporttool_upload_after", ['profile' => $this, 'filepath' => $this->_params['path'], 'filename' => $fileNameFalse]);
                }
                // send by email
                if ($this->_params['mail_enabled'] && count($filesList) > 0 && $increment > 1) {
                    if ($this->_params['mail_one_report']) {
                        if ($this->_params['mail_zip']) {
                            $zip = new \ZipArchive();
                            $zipname = $fileNameFalse . ".zip";
                            if ($zip->open($this->storageHelper->getAbsoluteRootDir() . $this->_params['path'] . $zipname, \ZipArchive::CREATE) !== true) {
                                $this->addError(__("Cannot create Zip file"));
                            }
                            foreach ($filesList as $file) {
                                $zip->addFromString($file, file_get_contents($this->storageHelper->getAbsoluteRootDir() . $this->_params['path'] . $file));
                            }
                            $zip->close();
                            $mails = explode(',', $this->_params['mail_recipients']);
                            foreach ($mails as $mail) {
                                $mail = trim($mail);
                                if ($mail != "") {
                                    if ($this->emailHelper->mailWithAttachment($this->_params['mail_sender'], $mail, $this->_params['mail_subject'], $this->_params['mail_message'], $zipname, $this->_params['path'], 'zip')) {
                                        $this->addSuccess(sprintf(__("Files successfully sent to %s."), $mail));
                                    }
                                }
                            }
                            $this->ioFile->rm($zipname);
                        } else {
                            $mails = explode(',', $this->_params['mail_recipients']);
                            foreach ($mails as $mail) {
                                $mail = trim($mail);
                                if ($mail != "") {
                                    if ($this->emailHelper->mailWithAttachment($this->_params['mail_sender'], $mail, $this->_params['mail_subject'], $this->_params['mail_message'], $filesList, $this->_params['path'], $this->storageHelper->getFileType($this->_params['type']))) {
                                        $this->addSuccess(sprintf(__("Files successfully sent to %s."), $mail));
                                    }
                                }
                            }
                        }
                    } else {
                        $mails = explode(',', $this->_params['mail_recipients']);
                        foreach ($filesList as $file) {
                            foreach ($mails as $mail) {
                                $mail = trim($mail);
                                if ($mail != "") {
                                    if ($this->emailHelper->mailWithAttachment($this->_params['mail_sender'], $mail, $this->_params['mail_subject'], $this->_params['mail_message'], $file, $this->_params['path'], $this->storageHelper->getFileType($this->_params['type']))) {
                                        $this->addSuccess(sprintf(__("File " . $file . " successfully sent to %s."), $mail));
                                    }
                                }
                            }
                        }
                    }
                }
                $root = $this->storageHelper->getAbsoluteRootDir() . $this->_params['path'];
                if ($this->_params['storage_enabled'] && count($this->_counter)) {
                    foreach ($filesList as $file) {
                        $this->addSuccess(sprintf(__("File %s exported to %s."), $file, $this->_params['path']));
                    }
                }
                if (!$this->_params['storage_enabled']) {
                    foreach ($filesList as $file) {
                        $this->ioFile->rm($root . $file);
                    }
                }
                $msg = __("Profile %1 successfully executed,  %2  orders exported", $this->_params['name'], count($this->_counter));
                $this->progressHelper->log($msg, true, ProgressHelper::SUCCEEDED, 100);
                $this->addSuccess("<b><span title='" . implode(",", $this->_counter) . "'> " . $msg . ".</span></b> ");
                return $this;
            }
        } catch (\Exception $e) {
            $this->progressHelper->log($e->getMessage(), true, ProgressHelper::ERROR);
            throw new \Exception($e->getMessage());
        }
    }
    /**
     * @param $msg
     */
    protected function addSuccess($msg)
    {
        if ($this->framework->isAdmin()) {
            $this->messageManager->addSuccess($msg);
        }
    }
    /**
     * @param $msg
     */
    protected function addError($msg)
    {
        if ($this->framework->isAdmin()) {
            $this->messageManager->addError($msg);
        }
    }
    /**
     * Is log file enabled
     */
    protected function isLogEnabled()
    {
        return $this->framework->getStoreConfig('ordersexporttool/advanced/enable_log') ? true : false;
    }
}