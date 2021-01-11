<?php

/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\OrderUpdater\Helper;

/**
 * Class Data
 * @package Wyomind\OrderUpdater\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var string
     */
    public $module = "OrderUpdater";
    /**
     *
     */
    const FIELD_IMPLODE = ",";
    /**
     *
     */
    const LOCATION_MAGENTO = 1;
    /**
     *
     */
    const LOCATION_FTP = 2;
    /**
     *
     */
    const LOCATION_URL = 3;
    /**
     *
     */
    const LOCATION_WEBSERVICE = 4;
    /**
     *
     */
    const LOCATION_DROPBOX = 5;
    /**
     *
     */
    const IS_MAGENTO_EXPORT_YES = 1;
    /**
     *
     */
    const IS_MAGENTO_EXPORT_NO = 2;
    /**
     *
     */
    const POST_PROCESS_ACTION_NOTHING = 0;
    /**
     *
     */
    const POST_PROCESS_ACTION_DELETE = 1;
    /**
     *
     */
    const POST_PROCESS_ACTION_MOVE = 2;
    /**
     *
     */
    const POST_PROCESS_INDEXERS_DISABLED = 0;
    /**
     *
     */
    const POST_PROCESS_INDEXERS_AUTOMATICALLY = 1;
    /**
     *
     */
    const POST_PROCESS_INDEXERS_ONLY_SELECTED = 2;
    /**
     *
     */
    const NO = 0;
    /**
     *
     */
    const YES = 1;
    /**
     *
     */
    const TMP_FOLDER = "/var/tmp/orderupdater/";
    /**
     *
     */
    const UPLOAD_DIR = "/var/upload/";
    /**
     *
     */
    const TMP_FILE_PREFIX = "orderupdater_";
    /**
     *
     */
    const TMP_FILE_EXT = "orig";
    /**
     *
     */
    const CSV = 1;
    /**
     *
     */
    const XML = 2;
    /**
     *
     */
    const UPDATE = 1;
    /**
     *
     */
    const IMPORT = 2;
    /**
     *
     */
    const UPDATEIMPORT = 3;
    /**
     *
     */
    const MODULES_MAPPING = [];
    /**
     *
     */
    const MODULES_ACTIONS = [10 => "Actions\\ActionSetStatus", 20 => "Actions\\ActionAddComment", 30 => "Actions\\ActionInvoice", 40 => "Actions\\ActionCreditmemo", 50 => "Actions\\ActionShip", 60 => "Actions\\ActionCancel"];
    /**
     * @var \Magento\Framework\Filesystem\Driver\FileFactory|null
     */
    protected $driverFileFactory = null;
    /**
     * @var null|\Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     */
    protected $orderCollectionFactory;
    public function __construct(\Wyomind\OrderUpdater\Helper\Delegate $wyomind, \Magento\Framework\App\Helper\Context $context, \Magento\Framework\Filesystem\Driver\FileFactory $driverFileFactory, \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory)
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        parent::__construct($context);
        $this->driverFileFactory = $driverFileFactory;
        $this->orderCollectionFactory = $orderCollectionFactory;
    }
    /**
     * @return array
     */
    public function getFileHeader()
    {
        $fileHeader = [];
        $model = $this->coreRegistry->registry('profile');
        if (isset($model) && $model->getId()) {
            $fileHeader = $model->getFileHeader();
        }
        return $fileHeader;
    }
    /**
     * @return string
     */
    public function getMaxFileSize()
    {
        static $max_size = -1;
        if ($max_size < 0) {
            // Start with post_max_size.
            $post_max_size = $this->parseSize(ini_get('post_max_size'));
            if ($post_max_size > 0) {
                $max_size = $post_max_size;
            }
            // If upload_max_size is less, then reduce. Except if upload_max_size is
            // zero, which indicates no limit.
            $upload_max = $this->parseSize(ini_get('upload_max_filesize'));
            if ($upload_max > 0 && $upload_max < $max_size) {
                $max_size = $upload_max;
            }
        }
        return $this->readableSize($max_size);
    }
    /**
     * @param string $size
     * @return float
     */
    public function parseSize($size)
    {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size);
        // Remove the non-unit characters from the size.
        $size = preg_replace('/[^0-9\\.]/', '', $size);
        // Remove the non-numeric characters from the size.
        if ($unit) {
            // Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
            return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
        } else {
            return round($size);
        }
    }
    /**
     * @param string $size
     * @return string
     */
    public function readableSize($size)
    {
        $i = 0;
        $unit = array("b", "kb", "mb", "gb", "tb", "pb", "eb", "zb", "yb");
        while ($size > 1024) {
            $i++;
            $size = $size / 1024;
        }
        return $size . ucfirst($unit[$i]);
    }
    /**
     * @param string $string
     * @return bool
     */
    function isJSON($string)
    {
        return is_string($string) && is_array(json_decode($string, true)) && json_last_error() == JSON_ERROR_NONE ? true : false;
    }
    /**
     * @param array $array1
     * @param array $array2
     * @return array
     */
    function arrayMerge($array1 = array(), $array2 = array())
    {
        foreach ($array1 as $key => $value) {
            if (isset($array2[$key]) && $array2[$key] != "") {
                $array1[$key] .= self::FIELD_IMPLODE . $array2[$key];
            }
        }
        return $array1;
    }
    /**
     * @param $file
     * @param $params
     * @param $limit
     * @param bool $isOutput
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getData($file, $params, $limit = INF, $isOutput = false)
    {
        try {
            $data = array();
            $driverFile = $this->driverFileFactory->create();
            $counter = 0;
            $mapping = json_decode($params['mapping']);
            $headers = array();
            $colors = array(null);
            $tags = array(null);
            if ($isOutput) {
                $headers[] = $params["identifier"];
                if ($mapping) {
                    foreach ($mapping as $column) {
                        if ($column->enabled) {
                            $headers[] = $column->label;
                            $colors[] = $column->color;
                            $tags[] = $column->tag;
                        }
                    }
                }
            }
            switch ($params["file_type"]) {
                case self::CSV:
                    $inCh = $driverFile->fileOpen($file, 'r');
                    // if no header row reserve the first row to place the headers
                    if (!$params['has_header']) {
                        $counter++;
                    }
                    $i = 0;
                    $previous = array();
                    while ($counter <= $limit && ($cell = $driverFile->fileGetCsv($inCh, 0, $params['field_delimiter'])) != false) {
                        $cell = array_map(function ($tmp) {
                            if (!mb_detect_encoding($tmp, 'UTF-8', true)) {
                                return utf8_encode($tmp);
                            } else {
                                return $tmp;
                            }
                        }, $cell);
                        if (isset($cell[(int) $params["identifier_offset"]])) {
                            $rangeCondition = $this->getLineRangeCondition($params['line_filter'], $i, $cell[(int) $params["identifier_offset"]], $cell, $params['has_header']);
                        } else {
                            $rangeCondition = FALSE;
                        }
                        $i++;
                        // if range condition returns FALSE
                        if (!$params['has_header'] && $rangeCondition == false || $params['has_header'] && $counter > 0 && $rangeCondition == false) {
                            continue;
                        }
                        if ($isOutput) {
                            $skipped = false;
                            $data[$counter] = array();
                            try {
                                $identifier_value = $cell[(int) $params["identifier_offset"]];
                            } catch (\Exception $e) {
                                $rtn['status'] = "error";
                                $rtn['message'] = __("Error in script for {$column->label} :") . nl2br(htmlentities($e->getMessage()));
                                return $rtn;
                            }
                            if ($identifier_value === FALSE) {
                                $skipped = true;
                                $identifier_value = "<i class='skipped'> " . __("skip this cell and next cells") . "</i>";
                            } else {
                                if ($identifier_value === TRUE) {
                                    $identifier_value = "<i class='skipped'> " . __("skip only this cell") . "</i>";
                                }
                            }
                            $data[$counter][] = $identifier_value;
                            $cell["identifier"] = $identifier_value;
                            foreach ($mapping as $column) {
                                if (isset($column->index) && $column->index != "") {
                                    $cell[$column->source] = $cell[$column->index];
                                }
                            }
                            if ($mapping) {
                                foreach ($mapping as $column) {
                                    $self = "";
                                    if ($column->enabled) {
                                        if ($skipped === true) {
                                            $self = "<i class='skipped'> " . __("skipped") . "</i>";
                                            $data[$counter][] = $self;
                                            continue;
                                        }
                                        if (isset($column->index) && $column->index != "") {
                                            // attribute is mapped with one data source
                                            $self = $cell[$column->index];
                                        } else {
                                            // attribute is mapped with a custom value
                                            if ($column->scripting == "") {
                                                $self = $column->default;
                                            }
                                        }
                                        if ($column->scripting != "") {
                                            $before = $self;
                                            try {
                                                $self = $this->execPhp($column->scripting, $cell, $self);
                                                if ($self === FALSE) {
                                                    $skipped = true;
                                                    $self = "<i class='skipped'> " . __("skip this cell and next cells") . "</i>";
                                                    $data[$counter][] = $self;
                                                    continue;
                                                } else {
                                                    if ($self === TRUE) {
                                                        $self = "<i class='skipped'> " . __("skip only this cell") . "</i>";
                                                        $data[$counter][] = $self;
                                                        continue;
                                                    }
                                                }
                                            } catch (\Exception $e) {
                                                $rtn['status'] = "error";
                                                $rtn['message'] = __("Error in script for {$column->label} :") . nl2br(htmlentities($e->getMessage()));
                                                return $rtn;
                                            }
                                            $after = $self;
                                            if ($before != $after) {
                                                if ($before == "") {
                                                    $before = __("null");
                                                }
                                                if ($after == "") {
                                                    $after = __("null");
                                                }
                                                $self = "<span class='dynamic'>" . __("Dynamic value = ") . "<i> " . $after . "</i></span>" . "<br><span class='previous'>" . __("Original value = ") . " <i>" . $before . "</i></span>";
                                            }
                                        }
                                        $data[$counter][] = $self;
                                    }
                                }
                            }
                            /**
                             * MAGENTO EXPORT FILE READER
                             */
                            if ($params['is_magento_export'] == self::IS_MAGENTO_EXPORT_YES) {
                                if (empty($data[$counter][(int) $params["identifier_offset"]]) && !empty($previous)) {
                                    $previous = $this->arrayMerge($previous, $data[$counter]);
                                    $data[$counter - 1] = $previous;
                                    continue;
                                } else {
                                    $previous = $data[$counter];
                                }
                            }
                        } else {
                            $data[$counter] = $cell;
                        }
                        $counter++;
                    }
                    // if has header then get the first row
                    if ($params['has_header']) {
                        if (!$isOutput) {
                            $headers = array_shift($data);
                            $length = !empty($headers);
                            for ($i = 0; $i < $length; $i++) {
                                if (trim($headers[$i]) == "") {
                                    $headers[$i] = 'Empty header ' . $i;
                                }
                            }
                        } else {
                            array_shift($data);
                        }
                    } else {
                        if (!$isOutput) {
                            $nbColumns = 0;
                            if (isset($data[1])) {
                                $nbColumns = count($data[1]);
                            }
                            for ($i = 0; $i < $nbColumns; $i++) {
                                if (false === array_key_exists($i, $headers)) {
                                    $headers[$i] = 'Empty header ' . $i;
                                }
                            }
                        }
                    }
                    break;
                case self::XML:
                    $search = array("g:", "ss:", "x:", "xs:", "xmlns:", "xmlns:msdata", "msdata:", "xmlns");
                    $replace = array("g_", "ss_", "x_", "xs_", "xmlns_", "xmlns_msdata", "msdata_", "xmlnamespace");
                    $xml = (new \SimpleXMLElement(str_replace($search, $replace, $driverFile->fileGetContents($file))))->xpath($params['xml_xpath_to_order']);
                    if (!count($xml)) {
                        if ($params["preserve_xml_column_mapping"]) {
                            try {
                                if (!isset($structure)) {
                                    $structure = json_decode($params["xml_column_mapping"], true);
                                }
                            } catch (\Exception $e) {
                                $exc = new \Magento\Framework\Exception\LocalizedException(__("Invalid Json string for the XML structure."));
                                throw $exc;
                            }
                            if (!count($headers) && !$isOutput) {
                                $headers = array_keys($structure);
                            }
                        } else {
                            $exc = new \Magento\Framework\Exception\LocalizedException(__("No orders were found for `%1`. Please check the XPath.", $params['xml_xpath_to_order']));
                            throw $exc;
                        }
                    }
                    $i = 0;
                    foreach ($xml as $order) {
                        $cell = array();
                        // automatic XML Structure
                        if ($limit != -1 && $counter > $limit) {
                            break;
                        }
                        if (!$params["preserve_xml_column_mapping"]) {
                            //use the longest headers rows
                            if (count($headers) < count(array_keys((array) $order)) && !$isOutput) {
                                $headers = array_keys((array) $order);
                                $headers = array_unique($headers);
                            }
                            $columns = array_keys((array) $order);
                            foreach ($columns as $x => $key) {
                                $xmlElement = (array) $order->{$key};
                                if (count($xmlElement) === 1) {
                                    if (trim($order->{$key}->__toString()) != "") {
                                        $cell[$x] = $order->{$key}->__toString();
                                    } else {
                                        $cell[$x] = $order->{$key};
                                    }
                                } else {
                                    $cell[$x] = "";
                                }
                                if ($isOutput) {
                                    $cell[$key] = $cell[$x];
                                }
                            }
                        } else {
                            try {
                                if (!isset($structure)) {
                                    $structure = json_decode($params["xml_column_mapping"], true);
                                    if (json_last_error() != JSON_ERROR_NONE) {
                                        $exc = new \Magento\Framework\Exception\LocalizedException(__("Invalid Json string for the XML structure."));
                                        throw $exc;
                                    }
                                }
                            } catch (\Exception $e) {
                                $exc = new \Magento\Framework\Exception\LocalizedException(__("Invalid Json string for the XML structure."));
                                throw $exc;
                            }
                            if (!count($headers) && !$isOutput) {
                                $headers = array_keys($structure);
                            }
                            //                            $order = new \SimpleXMLElement($order->asXML());
                            $x = 0;
                            foreach ($structure as $header => $xpath) {
                                $result = $order->xpath($xpath);
                                if (isset($result[0])) {
                                    $cell[$x] = trim($result[0]->__toString());
                                    if (count($result[0]) != 0) {
                                        $cell[$x] = $result[0];
                                    }
                                    $splitted = explode("/", $xpath);
                                    $last = array_pop($splitted);
                                    if (substr($last, 0, 1) == "@") {
                                        $cell[$x] = (string) $result[0][substr($last, 1)];
                                    }
                                } else {
                                    $cell[$x] = "";
                                }
                                if ($isOutput) {
                                    $cell[$header] = $cell[$x];
                                }
                                $x++;
                            }
                        }
                        if (!isset($cell[(int) $params["identifier_offset"]])) {
                            continue;
                        }
                        $rangeCondition = $this->getLineRangeCondition($params['line_filter'], $i, $cell[(int) $params["identifier_offset"]], $cell, false);
                        $i++;
                        if ($rangeCondition == false) {
                            continue;
                        }
                        if ($isOutput) {
                            $skipped = false;
                            $data[$counter] = array();
                            try {
                                $identifier_value = $cell[(int) $params["identifier_offset"]];
                            } catch (\Exception $e) {
                                $rtn['status'] = "error";
                                $rtn['message'] = __("Error in script for {$column->label} :") . nl2br(htmlentities($e->getMessage()));
                                return $rtn;
                            }
                            if ($identifier_value === FALSE) {
                                $skipped = true;
                                $identifier_value = "<i class='skipped'> " . __("skip this cell and next cells") . "</i>";
                            } else {
                                if ($identifier_value === TRUE) {
                                    $identifier_value = "<i class='skipped'> " . __("skip only this cell") . "</i>";
                                }
                            }
                            $data[$counter][] = $identifier_value;
                            $cell["identifier"] = $identifier_value;
                            foreach ($mapping as $column) {
                                $self = "";
                                if (isset($column->index) && $column->index != "" && isset($cell[$column->index])) {
                                    //
                                    $cell[$column->source] = $cell[$column->index];
                                } else {
                                    $cell[$column->source] = "";
                                }
                                if ($column->enabled) {
                                    if ($skipped === true) {
                                        $self = "<i class='skipped'> " . __("skipped") . "</i>";
                                        $data[$counter][] = $self;
                                        continue;
                                    }
                                    if (isset($column->index) && $column->index != "" && isset($cell[$column->index])) {
                                        // attribute is mapped with one data source
                                        $self = $cell[$column->index];
                                    } else {
                                        // attribute is mapped with a custom value
                                        if ($column->scripting == "") {
                                            $self = $column->default;
                                        }
                                    }
                                    if ($column->scripting != "") {
                                        $before = $self;
                                        try {
                                            $self = $this->execPhp($column->scripting, $cell, $self);
                                            if ($self === FALSE) {
                                                $skipped = true;
                                                $self = "<i class='skipped'> " . __("skip this cell and next cells") . "</i>";
                                                $data[$counter][] = $self;
                                                continue;
                                            } else {
                                                if ($self === TRUE) {
                                                    $self = "<i class='skipped'> " . __("skip only this cell") . "</i>";
                                                    $data[$counter][] = $self;
                                                    continue;
                                                }
                                            }
                                        } catch (\Exception $e) {
                                            $rtn['status'] = "error";
                                            $rtn['message'] = __("Error in script for {$column->label} :") . nl2br(htmlentities($e->getMessage()));
                                            return $rtn;
                                        }
                                        $after = $self;
                                        if ($before != $after) {
                                            if ($before == "") {
                                                $before = __("null");
                                            }
                                            if ($after == "") {
                                                $after = __("null");
                                            }
                                            $self = "<span class='dynamic'>" . __("Dynamic value = ") . "<i> " . $after . "</i></span>" . "<br><span class='previous'>" . __("Original value = ") . " <i>" . $before . "</i></span>";
                                        }
                                    }
                                    $data[$counter][] = $self;
                                }
                            }
                        } else {
                            $data[$counter] = $cell;
                        }
                        $counter++;
                    }
                    break;
            }
            return ['error' => "false", 'header' => $headers, 'tag' => $tags, 'color' => $colors, 'data' => array_values($data)];
        } catch (\Throwable $e) {
            $exc = new \Magento\Framework\Exception\LocalizedException(__("%1", $e->getMessage()));
            throw $exc;
        }
    }
    /**
     * @return array
     */
    public function getJsonAttributes($modules)
    {
        $dropdown = array();
        foreach ($modules as $module) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $resource = $objectManager->get("\\Wyomind\\" . $this->module . "\\Model\\ResourceModel\\Modules\\" . $module);
            $options = $resource->getDropdown($this);
            $dropdown = array_merge($dropdown, $options);
        }
        return json_encode($dropdown);
    }
    /**
     * @return array
     */
    public function getFieldDelimiters()
    {
        return [';' => ';', ', ' => ', ', '|' => '|', "\t" => '\\tab'];
    }
    /**
     * @return array
     */
    public function getFieldEnclosures()
    {
        return ["none" => 'none', '"' => '"', '\'' => '\''];
    }
    /**
     * @return array
     */
    public function getOrderIdentifiers()
    {
        // load the last order
        $collection = $this->orderCollectionFactory->create();
        $collection->getSelect()->order('created_at DESC')->limit(1);
        $order = $collection->getFirstItem();
        return [["label" => "Order Id (e.g. " . $order->getEntityId() . ")", "value" => "entity_id"], ["label" => "Order Increment (e.g. " . $order->getIncrementId() . ")", "value" => "increment_id"]];
    }
    /**
     * Line filter
     * @param string $parameters
     * @param int $lineNumber
     * @param $identifier string
     * @param array $cell
     * @return boolean
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getLineRangeCondition($parameters, $lineNumber, $identifier, $cell = [], $hasHeader = true)
    {
        $upTo = false;
        $range = false;
        $equal = false;
        $pregMatch = false;
        $rangeCondition = true;
        if ($parameters) {
            $rtn = $this->execPhp($parameters, $cell);
            if ($rtn === FALSE || $rtn === TRUE) {
                return $rtn;
            }
            $regExp = "/#([^#]+)#/";
            preg_match_all($regExp, $parameters, $matches);
            $identifiers = $matches[0];
            foreach ($matches[0] as $exp) {
                $parameters = str_replace($exp, "", $parameters);
            }
            $parameters = array_merge(array_filter(explode(',', $parameters)), $identifiers);
            foreach ($parameters as $value) {
                if (preg_match($regExp, $value) && (!$hasHeader || $lineNumber > 0)) {
                    if (false == $pregMatch) {
                        $pregMatch = preg_match($value, $identifier);
                    }
                } elseif (false !== strpos($value, '+')) {
                    $value = str_replace(' ', '', $value);
                    if (false === $upTo) {
                        // From line - to the end (e.g 2+)
                        $upTo = $lineNumber >= $value;
                    }
                } elseif (false !== strpos($value, '-')) {
                    $value = str_replace(' ', '', $value);
                    if (false === $range) {
                        // From - To line (e.g 15-20)
                        $fromTo = explode('-', $value);
                        $from = $lineNumber >= $fromTo[0];
                        $to = $lineNumber <= $fromTo[1];
                        $range = $from && $to;
                    }
                } else {
                    $value = str_replace(' ', '', $value);
                    if (false === $equal) {
                        // One line
                        $equal = $lineNumber == $value;
                    }
                }
            }
            $rangeCondition = $equal || $range || $upTo || $pregMatch;
        }
        return $rangeCondition;
    }
    /**
     * @return array
     */
    public function getBoolean()
    {
        return array(1 => (string) "Enabled", 0 => (string) "Disabled");
    }
    /**
     * @return array
     */
    public function getBackorders()
    {
        return array(0 => (string) "No Backorders", 1 => (string) "Backorders allowed", 2 => (string) "Backorders allowed and notify customer");
    }
    /**
     * @param $value
     * @return string
     */
    function sanitizeField($value)
    {
        return "'" . str_replace("'", "''", $value) . "'";
    }
    /**
     * @param $fields
     * @param $value
     * @param null $uniqueParameter
     * @param bool $multipleGroup
     * @return mixed
     */
    function prepareFields($fields, $value, $uniqueParameter = null, $multipleGroup = false)
    {
        $data = [];
        $groups = $this->parseGroups($value, $multipleGroup);
        if ($multipleGroup) {
            return $groups;
        }
        if (isset($groups)) {
            foreach ($groups as $k => $group) {
                $parameters = $this->parseParameters($group);
                foreach ($parameters["variable"] as $key => $value) {
                    if ($uniqueParameter) {
                        $data[$uniqueParameter] = $parameters["value"][$key];
                    } else {
                        $data[strtolower($parameters["variable"][$key])] = $parameters["value"][$key];
                    }
                }
                break;
            }
        }
        $rtn = [];
        foreach ($fields as $field => $default) {
            if (!isset($data[$field])) {
                $rtn[$field] = $default;
            } else {
                $rtn[$field] = $data[$field];
            }
            if ($field == "price") {
                $rtn["price_type"] = "'fixed'";
                if (stristr($rtn["price"], "%")) {
                    $rtn["price_type"] = "'percent'";
                }
                $rtn["price"] = str_replace(["%", ","], null, $rtn["price"]);
            }
            if ($rtn[$field] instanceof \Zend_Db_Expr) {
                $rtn[$field] = $rtn[$field]->__toString();
            } else {
                $rtn[$field] = $this->sanitizeField($rtn[$field]);
            }
        }
        return $rtn;
    }
    /**
     * Is MSI enabled
     * @return bool
     */
    public function isMsiEnabled()
    {
        return version_compare($this->framework->getMagentoVersion(), "2.3.0", ">=") && $this->framework->moduleIsEnabled("Magento_InventorySales");
    }
    /**
     * @param $input
     * @return string
     */
    public function fromCamelCase($input)
    {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = ucfirst($match);
        }
        return implode(' ', $ret);
    }
    /**
     * @return array
     */
    public function getOrderConditions()
    {
        $orderConditions = ['order.getState' => __('State'), 'order.getStatus' => __('Status'), 'order.getStoreId' => __('Store Id'), 'order.getCreatedAt' => __('Created at'), 'order.getUpdatedAt' => __('Updated at')];
        return $orderConditions;
    }
    /**
     * @param string $script
     * @param null $cell
     * @param null $self
     * @return mixed|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execPhp($script, $self = null, $cell = null)
    {
        // get order if it exists
        if (!is_null($cell) && is_array($cell) && isset($cell['order'])) {
            $order = $cell['order'];
        }
        // Restore all break lines in the php code
        $script = str_replace("__LINE_BREAK__", "
", $script);
        if (preg_match("#^<\\?(php)?(.*)(\\?>)?\$#mi", $script)) {
            try {
                return eval("?>" . $script . " return \$self;");
            } catch (\Throwable $e) {
                $exc = new \Magento\Framework\Exception\LocalizedException(__("
Error in:
 %1 

Error message:n %2 

", $script, $e->getMessage()));
                throw $exc;
            }
        }
        return $self;
    }
}