<?php

namespace Wyomind\OrderUpdater\Model\ResourceModel\Modules;

/**
 * Class AbstractResource
 * @package Wyomind\OrderUpdater\Model\ResourceModel\Modules
 */
abstract class AbstractResource extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     *
     */
    const ENABLE = ["true", "yes", "in stock", "enable", "enabled"];
    /**
     *
     */
    const DISABLE = ["false", "no", "out of stock", "disable", "disabled"];
    /**
     * @var string
     */
    public $decimal = "Float Number or Integer Number";
    /**
     * @var string
     */
    public $datetime = "Date + Time GMT (yyyy-mm-dd hh:mm:ss)";
    /**
     * @var string
     */
    public $smallint = "Boolean value";
    /**
     * @var string
     */
    public $int = "Integer number";
    /**
     * @var string
     */
    public $static = "Static";
    /**
     * @var string
     */
    public $text = "Text";
    /**
     * @var string
     */
    public $varchar = "Small text (255 characters maximum)";
    /**
     * @var string
     */
    public $uniqueIdentifier = "Unique Identifier";
    /**
     *
     */
    const QUERY_INDEXER_INCREMENT = 1000;
    /**
     * @var
     */
    public $table;
    /**
     * @var array
     */
    public $queries = array();
    /**
     * @var int
     */
    public $queryIndexer = 0;
    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory|null
     */
    protected $entityAttributeCollection = null;
    public function __construct(\Wyomind\OrderUpdater\Helper\Delegate $wyomind, \Magento\Framework\Model\ResourceModel\Db\Context $context, \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory $entityAttributeCollection, $connectionName = null)
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        $this->entityAttributeCollection = $entityAttributeCollection;
        parent::__construct($context, $connectionName);
    }
    /**
     * construct
     */
    public function _construct()
    {
        $this->queries[$this->queryIndexer] = array();
    }
    /**
     * Reset method
     */
    public function reset()
    {
    }
    /**
     * Increment the indexer
     */
    public function incrementQueryIndexer()
    {
        if (!isset($this->queries[$this->queryIndexer])) {
            $this->queries[$this->queryIndexer] = array();
        }
        if (count($this->queries[$this->queryIndexer]) >= self::QUERY_INDEXER_INCREMENT) {
            $this->queryIndexer++;
            $this->queries[$this->queryIndexer] = array();
        }
    }
    /**
     * Return the indexes to refresh
     * @param array $mapping
     * @return array
     */
    public function getIndexes($mapping = [])
    {
        return [];
    }
    /**
     * Action to perform when at the end of the process
     * @param \Wyomind\MassSockUpdate\Model\ResourceModel\Profile $profile
     */
    public function afterProcess($profile)
    {
    }
    /**
     * Check if the module has fields to add
     * @return boolean
     */
    public function hasFields()
    {
        return $this->getFields();
    }
    /**
     * List all fields to add
     * @param object $fieldset
     * @param object $form
     * @param object $class
     * @return boolean
     */
    public function getFields($fieldset = null, $form = null, $class = null)
    {
        return false;
    }
    /**
     * List all module to add
     * @param \Wyomind\MassSockUpdate\Model\ResourceModel\Profile $profile
     * @return array
     */
    public function addModuleIf($profile)
    {
        return [];
    }
    /**
     * List of new mapping attributes
     * @return array
     */
    public function getDropdown()
    {
        return [];
    }
    /**
     * Transform enable/disable values to 0/1
     * @param string $value
     * @return string
     */
    public function getValue($value)
    {
        if (in_array(strtolower($value), self::ENABLE)) {
            return 1;
        } else {
            if (in_array(strtolower($value), self::DISABLE)) {
                return 0;
            }
        }
        return (string) $value;
    }
    /** Sort attribut list
     * @param $a
     * @param $b
     * @return int
     */
    public function attributesSort($a, $b)
    {
        return $a['frontend_label'] < $b['frontend_label'] ? -1 : 1;
    }
    /** insert ignore ... on duplicate key update ... query
     * @param $table
     * @param $data
     * @param bool $ignoreStatement
     * @return string
     */
    public function createInsertOnDuplicateUpdate($table, $data, $ignoreStatement = false)
    {
        $fields = array();
        $values = array();
        $update = array();
        $ignore = "";
        if ($ignoreStatement) {
            $ignore = "IGNORE";
        }
        foreach ($data as $field => $value) {
            $val = $this->getValue((string) $value);
            $fields[] = "`" . $field . "`";
            $values[] = $val;
            $update[] = $field . "=" . $val . "";
        }
        return "INSERT " . $ignore . " INTO  `" . $table . "` (" . implode(",", $fields) . ") " . " VALUES (" . implode(",", $values) . ") ON DUPLICATE KEY UPDATE " . implode(",", $update) . ";";
    }
    /** Delete query
     * @param $table
     * @param $data
     * @return string
     */
    public function _delete($table, $data)
    {
        $delete = array();
        foreach ($data as $field => $value) {
            $val = $this->getValue((string) $value);
            $delete[] = $field . "=" . $val . "";
        }
        return "DELETE FROM `" . $table . "`WHERE " . implode(" AND ", $delete) . ";";
    }
}