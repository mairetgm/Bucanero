<?php

/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\OrderUpdater\Model;

use Wyomind\Framework\Helper\Progress as ProgressHelper;
class Profiles extends \Magento\Framework\Model\AbstractModel
{
    const DEFAULT_ORDER_BATCH_SIZE = 100;
    /*
     * $var int
     */
    protected $orderBatchSize;
    /**
     * @var string
     */
    public $module = 'OrderUpdater';
    /**
     * @var string
     */
    public $modules = [];
    /**
     * @var string
     */
    public $name = 'Mass Order Update';
    /**
     * @var null|string
     */
    protected $helperClass = null;
    /**
     * @var array
     */
    public $params = [];
    /**
     * @var string
     */
    protected $identifierCode;
    /**
     * @var array
     */
    public $success = array();
    /**
     * @var array
     */
    public $warnings = array();
    /**
     * @var array
     */
    public $notices = array();
    /**
     * @var string
     */
    public $error = 'Invalid License!';
    /**
     * @var \Magento\Framework\Indexer\IndexerInterfaceFactory
     */
    protected $indexerFactory;
    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface|null
     */
    protected $ioWrite = null;
    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface|null
     */
    protected $ioRead = null;
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     */
    protected $orderCollectionFactory;
    /**
     * @var array
     */
    protected $fileHeader = [];
    public function __construct(\Wyomind\OrderUpdater\Helper\Delegate $wyomind, \Magento\Framework\Model\Context $context, \Magento\Framework\Registry $registry, \Magento\Framework\Indexer\IndexerInterfaceFactory $indexerFactory, \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory, \Magento\Framework\Model\ResourceModel\AbstractResource $abstractResource = null, \Magento\Framework\Data\Collection\AbstractDb $abstractDb = null, array $data = [])
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        $this->framework->constructor($this, func_get_args(), __CLASS__);
        $this->helperClass = '\\Wyomind\\' . $this->module . '\\Helper\\Data';
        $this->error = $this->name . ' - ' . $this->error;
        $this->indexerFactory = $indexerFactory;
        $resource = $this->appResource;
        $read = $resource->getConnection('core_read');
        $this->ioWrite = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::ROOT);
        $this->ioRead = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::ROOT);
        $this->orderCollectionFactory = $orderCollectionFactory;
        parent::__construct($context, $registry, $abstractResource, $abstractDb, $data);
        $this->progressHelper = $this->objectManager->create('Wyomind\\' . $this->module . '\\Helper\\Progress');
        // order batch size initialization. Fallback to the default value if the inputted value is not a positive integer
        $orderBatchSize = $this->framework->getStoreConfig(strtolower($this->module) . '/settings/order_batch_size');
        if (!(filter_var($orderBatchSize, FILTER_VALIDATE_INT) === FALSE) && $orderBatchSize > 0) {
            $this->orderBatchSize = $orderBatchSize;
        } else {
            $this->orderBatchSize = $this::DEFAULT_ORDER_BATCH_SIZE;
        }
    }
    /**
     *
     */
    protected function _construct()
    {
        $this->_init('Wyomind\\' . $this->module . '\\Model\\ResourceModel\\Profiles');
    }
    /**
     * load the file header if it is available
     */
    public function load($id, $field = null)
    {
        parent::load($id, $field);
        $file = $this->getImportData(null, 1, false);
        if (isset($file['header']) && !empty($file['header'])) {
            $this->fileHeader = $file['header'];
        }
        return $this;
    }
    /*
     * Get the file header
     */
    public function getFileHeader()
    {
        return $this->fileHeader;
    }
    /** Get the profile configuration
     * @param null $request
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function extractParams($request = null)
    {
        $this->progressHelper->log(__('Collecting parameters'), false);
        $resource = $this->appResource;
        $read = $resource->getConnection('core_read');
        $table = $resource->getTableName(strtolower($this->module) . '_profiles');
        $fields = $read->describeTable($table);
        foreach (array_keys($fields) as $field) {
            if (in_array($field, ['rules'])) {
                $this->params[$field] = $request !== null && is_string($request->getParam($field)) ? json_decode($request->getParam($field)) : json_decode($this->getData($field));
            } else {
                $this->params[$field] = $request !== null && (is_string($request->getParam($field)) || is_array($request->getParam($field))) ? $request->getParam($field) : $this->getData($field);
            }
        }
        $this->progressHelper->log(__('Parameters collected'), false);
        return $this->params;
    }
    /** Get the data to import
     * @param null $request
     * @param $limit
     * @param bool $isOutput
     * @return array|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getImportData($request = null, $limit = INF, $isOutput = false)
    {
        try {
            $this->progressHelper->log(__('Retrieving data'), false);
            if ($request == null) {
                $params = $this->extractParams($request);
            } else {
                $params = $request->getParams();
            }
            if ($params['file_path'] == '') {
                return ['error' => true, 'message' => __('No data preview available until source file is added.<br/><br/>Minimize this screen, and add a new source file under the \'File Location\' settings.')];
            }
            /* retrieve the file containing the data to update */
            $tmpFile = $this->storageHelper->getImportFile($params);
            if ($tmpFile == '') {
                return ['error' => true, 'message' => __('No data preview available until the configured source file is available.<br/><br/>Minimize this screen, and make sure the source file under the \'File Location\' settings is reachable.')];
            }
            /* retrieve the data contained in the file to update */
            $data = $this->dataHelper->getData($tmpFile, $params, $limit, $isOutput);
            if (isset($data['data'])) {
                $this->progressHelper->log(__('Data retrieved : %1 rows found', count($data['data'])), false);
            }
            /* remove tmp file */
            $this->progressHelper->log(__('Removing tmp file : %1', $tmpFile), false);
            $this->storageHelper->deleteFile(dirname($tmpFile), basename($tmpFile));
            return $data;
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Error: %1', $e->getMessage()));
        }
    }
    /** Handle multiple files
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function multipleImport($preview)
    {
        $filePath = $this->getFilePath();
        $files = $this->storageHelper->evalRegexp($filePath, $this->getFileSystemType(), true);
        $rtn = array();
        if (is_array($files)) {
            foreach ($files as $key => $file) {
                $import = $this->setFilePath($file)->import($key, $filePath, $preview);
                $rtn = array_merge($rtn, $import);
            }
        }
        return $rtn;
    }
    /** Core process (entry point)
     * @param $nth
     * @param $filePath
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function import($nth, $filePath, $preview)
    {
        try {
            if ($preview) {
                $this->progressHelper->startObservingProgress($this->isLogEnabled(), $this->getId() . '-preview', $this->getName(), true);
            } else {
                $this->progressHelper->startObservingProgress($this->isLogEnabled(), $this->getId(), $this->getName(), true);
            }
            $this->_eventManager->dispatch('orderupdater_start', ['profile' => $this]);
            $this->progressHelper->log('Starting ' . $this->getName(), true);
            $this->progressHelper->log('Current import file ' . $this->getFilePath(), true, progressHelper::PROCESSING, 0);
            $this->progressHelper->log('Importing data ');
            // retrieving data from file
            $data = $this->getImportData();
            if (isset($data['error']) && $data['error'] == 'true') {
                $this->progressHelper->log('' . $data['message'], true, progressHelper::FAILED, 0);
                return;
            }
            // check orders existence
            $this->progressHelper->log(__('Checking orders'));
            $this->identifierCode = $this->params['order_identification'] ?? 'increment_id';
            $this->fileidentifierIndex = $this->params['identifier_offset'] ?? 0;
            $order = null;
            $nbFoundOrders = 0;
            $nbNotFoundOrders = 0;
            $nbNotProcessedOrders = 0;
            $nbProcessedOrders = 0;
            $nbErrorOrders = 0;
            // Set file limit if preview mode has been set
            if ($preview == 1) {
                $entities = array_slice($data['data'], 0, $this->configHelper->getSettingsNbPreview());
            } else {
                $entities = $data['data'];
            }
            $batches = array_chunk($entities, $this->orderBatchSize);
            /*
             * load orders by batch from a collection
             */
            foreach ($batches as &$batch) {
                $batchIds = array_column($batch, $this->fileidentifierIndex);
                $orders = $this->orderCollectionFactory->create()->addAttributeToFilter($this->identifierCode, array('in' => $batchIds));
                foreach ($batch as $key => &$entity) {
                    $entity['log'] = [];
                    $orderIdentifier = $entity[$this->fileidentifierIndex];
                    $order = $orders->getItemByColumnValue($this->identifierCode, $orderIdentifier);
                    if (!is_null($order)) {
                        $entity['order'] = $order;
                        $nbFoundOrders++;
                    } else {
                        $entity['log'][] = ['type' => 'error', 'message' => __('Order not found')];
                        $nbNotFoundOrders++;
                    }
                }
                $this->progressHelper->log(__('%1 orders collected %2 orders not found', $nbFoundOrders, $nbErrorOrders));
                // Check entity conditions
                foreach ($batch as $key => &$entity) {
                    $orderProcessStatus = 0;
                    // not processed
                    if (!isset($entity['order'])) {
                        continue;
                    }
                    foreach ($this->params['rules'] as $ruleKey => $rule) {
                        if (isset($rule->disabled) && $rule->disabled == true) {
                            // ignore rule if it's disabled
                            continue;
                        }
                        if ($this->checkConditions($rule->conditions, $entity)) {
                            // execute actions on entity
                            $result = $this->executeActions($rule->actions, $entity, $preview);
                            // tenter la transmission des données en utilisant $data['data'][$key] et en ajoutant un & dans le param d'executeactions
                            // set order process status
                            if ($result && $orderProcessStatus < 1) {
                                $orderProcessStatus = 1;
                                // processed
                            } elseif (!$result && $orderProcessStatus < 2) {
                                $orderProcessStatus = 2;
                                // error
                            }
                        } else {
                            // notify the rule doesn't apply
                            $entity['log'][] = ['type' => 'notice', 'message' => __('Conditions not fulfilled for rule %1', $rule->name)];
                        }
                    }
                    if ($orderProcessStatus == 0) {
                        $nbNotProcessedOrders++;
                    } elseif ($orderProcessStatus == 1) {
                        $nbProcessedOrders++;
                    } elseif ($orderProcessStatus == 2) {
                        $nbErrorOrders++;
                    }
                }
            }
            $report = $this->generateReport($batches, $preview);
            if ($preview == 1) {
                return [$report];
            } else {
                $this->setLastImportReport($report);
            }
            $this->postProcess();
            $this->progressHelper->log('Profiles execution finished');
            $log = array('notice' => [], 'warning' => [], 'success' => []);
            $msg = '';
            $progressMsg = '';
            if ($nbNotFoundOrders > 0) {
                $msg = $nbNotFoundOrders . ' ' . __('orders not found');
                $progressMsg .= $msg . "<br />";
                $log['warning'] = $msg;
            }
            if ($nbNotProcessedOrders > 0) {
                $msg = $nbNotProcessedOrders . ' ' . __('orders not processed');
                $progressMsg .= $msg . "<br />";
                $log['notice'] = $msg;
            }
            if ($nbProcessedOrders > 0) {
                $msg = $nbProcessedOrders . ' ' . __('orders processed succesfully');
                $progressMsg .= $msg . "<br />";
                $log['success'] = $msg;
            }
            if ($nbErrorOrders > 0) {
                $msg = $nbErrorOrders . ' ' . __('orders processed with errors');
                $progressMsg .= $msg . "<br />";
                $log['error'] = $msg;
            }
            $this->progressHelper->log(print_r($progressMsg, true), true, 'SUCCEEDED', 100);
            $this->setImportedAt($this->dateTime->gmtDate('Y-m-d H:i:s'));
            $this->save();
            $this->_eventManager->dispatch('orderupdatersuccess', ['profile' => $this]);
            $this->progressHelper->stopObservingProgress();
            return $log;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->progressHelper->log('' . $e->getMessage(), true, progressHelper::FAILED, 0);
            $this->_eventManager->dispatch('orderupdater_failure', ['profile' => $this, 'error' => $e]);
            throw new \Magento\Framework\Exception\LocalizedException(__('<b>Unable to process the profile</b><br> %1', $e->getMessage()));
        }
    }
    /** Conditions evaluation
     * @param $conditions
     * @param $entity
     * @return boolean
     */
    protected function checkConditions($conditions, &$entity)
    {
        $result = true;
        /*
         * evaluate condition - a condition is considered true until all its AND groups are false
         * => any OR operand resets condition to true
         * => optimization: if result is already true when an OR operand is met, no need to evaluate other conditions, result is true
         */
        foreach ($conditions as $conditionIndex => $condition) {
            // check the logical operand
            if (isset($condition->operand) && $condition->operand == 'or') {
                if ($result == true) {
                    break;
                    // when meeting a 'OR', if the existing result is true the whole condition is true
                } else {
                    $result = true;
                }
            }
            if (isset($condition->operand) && $condition->operand == 'and') {
                if ($result == false) {
                    continue;
                    // when meeting a 'AND', if the existing result is false, the whole AND group is false, no need to evaluate the rest of the group
                }
            }
            if (!$this->checkCondition($condition, $entity)) {
                $result = false;
                // only set the result (to false) if fhe checkCondition function returns false, because $result is already true otherwise
            }
        }
        return $result;
    }
    /** Condition evaluation
     * @param $condition
     * @param $entity
     * @return boolean
     */
    protected function checkCondition($condition, &$entity)
    {
        // @TODO traduire les textes, et utiliser des simple quotes
        if (!isset($condition->condition)) {
            $entity['log'][] = ['type' => 'error', 'message' => __('Condition source not found')];
            return false;
        } elseif ($condition->condition == 'all') {
            return true;
        } else {
            $subject = $this->getConditionSubject($condition->condition, $entity);
            if ($subject === false) {
                $entity['log'][] = ['type' => 'error', 'message' => __('Field %1 not found in import entity', $condition->condition)];
                return false;
                // if condition field is not found, the condition can't be true
            }
        }
        if (isset($condition->value)) {
            $target = $condition->value;
        } else {
            $target = '';
        }
        if (!isset($condition->{'condition-operand'})) {
            $entity['log'][] = ['type' => 'error', 'message' => __('Condition operand not set for rule')];
            return false;
        } else {
            $conditionOperand = $condition->{'condition-operand'};
        }
        $result = false;
        switch ($conditionOperand) {
            case 'eq':
                $result = $subject == $target;
                break;
            case 'gt':
                $result = $subject > $target;
                break;
            case 'lt':
                $result = $subject < $target;
                break;
            case 'gteq':
                $result = $subject >= $target;
                break;
            case 'lteq':
                $result = $subject <= $target;
                break;
            case 'neq':
                $result = $subject != $target;
                break;
            case 'like':
                $pattern = str_replace('%', '.*', preg_quote($target, '/'));
                $result = preg_match('/^{$pattern}$/i', $subject);
                break;
            case 'nlike':
                $result = $subject == $target;
                $pattern = str_replace('%', '.*', preg_quote($target, '/'));
                $result = !preg_match('/^{$pattern}$/i', $subject);
                break;
            case 'null':
                $result = is_null($subject);
                break;
            case 'notnull':
                $result = !is_null($target);
                break;
            case 'in':
                $result = in_array($subject, is_array($target) ? $target : explode(',', $target));
                break;
            case 'nin':
                $result = $subject == $target;
                $result = !in_array($subject, is_array($target) ? $target : explode(',', $target));
                break;
            // @TODO ajouter le regexp
            default:
                $entity['log'][] = ['type' => 'error', 'message' => __('Condition operand not recognized: %1', $conditionOperand)];
                $result = false;
        }
        return $result;
    }
    /** Condition evaluation
     * @param $condition
     * @param $entity
     * @return boolean
     */
    protected function getConditionSubject($condition, &$entity)
    {
        if (substr($condition, 0, 6) == 'order.' && in_array($condition, array_keys($this->dataHelper->getOrderConditions()))) {
            $order = $entity['order'];
            return $order->{substr($condition, 6)}();
        } elseif (isset($entity[$condition])) {
            return $entity[$condition];
        } else {
            return false;
        }
    }
    /** Actions execution
     * @param $actions
     * @param $entity
     * @return boolean
     */
    protected function executeActions($actions, &$entity, $preview)
    {
        $globalResult = true;
        foreach ($actions as $actionKey => $action) {
            if ($action->action != 'Ignored/ignored') {
                $module = $this->getModule($action->action);
                $result = $module->execute($action, $entity, $preview);
                if ($result == false && $globalResult == true) {
                    $globalResult = false;
                }
            }
        }
        return $globalResult;
    }
    /** Execution modules getter
     * @param $action
     * @return Object
     */
    protected function getModule($action)
    {
        if (!isset($this->modules[$action])) {
            $moduleName = 'Action' . str_replace(' ', '', ucwords(str_replace('_', ' ', $action)));
            // get module name by capitalizing action
            $this->modules[$action] = $this->objectManager->get('\\Wyomind\\' . $this->module . '\\Model\\ResourceModel\\Modules\\Actions\\' . $moduleName);
        }
        return $this->modules[$action];
    }
    /**
     * @return bool
     */
    protected function isLogEnabled()
    {
        return $this->framework->getStoreConfig(strtolower($this->module) . '/settings/log') ? true : false;
    }
    /**
     * Execute post process action
     */
    public function postProcess()
    {
        $helperClass = $this->helperClass;
        $rootdir = rtrim($this->storageHelper->getMageRootDir(), '/');
        if ($this->params['post_process_action'] == $helperClass::POST_PROCESS_ACTION_MOVE) {
            $this->storageHelper->moveFile($rootdir . DIRECTORY_SEPARATOR . ltrim(dirname($this->getFilePath()), '\\/'), basename($this->getFilePath()), $rootdir . DIRECTORY_SEPARATOR . ltrim($this->params['post_process_move_folder'], '\\/'), basename($this->getFilePath()));
        } elseif ($this->params['post_process_action'] == $helperClass::POST_PROCESS_ACTION_DELETE) {
            $this->storageHelper->deleteFile($rootdir . DIRECTORY_SEPARATOR . ltrim(dirname($this->getFilePath()), '\\/'), basename($this->getFilePath()));
        }
    }
    /** Prepare the report
     * @param $batches
     * @return string
     */
    public function generateReport($batches, $preview)
    {
        // generate report
        $html = '';
        foreach ($batches as $batch) {
            foreach ($batch as $entity) {
                $html .= '<b>' . __('Order %1', $entity[$this->fileidentifierIndex]) . '</b><br />';
                if ($preview) {
                    foreach ($entity['log'] as $log) {
                        switch ($log['type']) {
                            case 'notice':
                                $html .= '<div class=\\"message message-notice notice\\"><div data-ui-id=\\"messages-message-notice\\">';
                                break;
                            case 'success':
                                $html .= '<div class=\\"message message-success success\\"><div data-ui-id=\\"messages-message-success\\">';
                                break;
                            case 'warning':
                                $html .= '<div class=\\"message message-warning warning\\"><div data-ui-id=\\"messages-message-warning\\">';
                                break;
                            case 'error':
                                $html .= '<div class=\\"message message-error error\\"><div data-ui-id=\\"messages-message-error\\">';
                                break;
                            default:
                                $html .= '<div class=\\"message\\"><div>';
                        }
                        $html .= $log['message'] . '</div></div></p>';
                    }
                } else {
                    foreach ($entity['log'] as $log) {
                        switch ($log['type']) {
                            case 'notice':
                                $html .= '<div class="message message-notice notice"><div data-ui-id="messages-message-notice">';
                                break;
                            case 'success':
                                $html .= '<div class="message message-success success"><div data-ui-id="messages-message-success">';
                                break;
                            case 'warning':
                                $html .= '<div class="message message-warning warning"><div data-ui-id="messages-message-warning">';
                                break;
                            case 'error':
                                $html .= '<div class="message message-error error"><div data-ui-id="messages-message-error">';
                                break;
                            default:
                                $html .= '<div class="message"><div>';
                        }
                        $html .= $log['message'] . '</div></div></p>';
                    }
                }
            }
        }
        return $html;
    }
}