<?php

namespace Wyomind\OrderUpdater\Block\Adminhtml\Profiles\Edit\Tab;

class Rules extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    public $module = "OrderUpdater";
    public function __construct(\Wyomind\OrderUpdater\Helper\Delegate $wyomind, \Magento\Backend\Block\Template\Context $context, \Magento\Framework\Registry $registry, \Magento\Framework\Data\FormFactory $formFactory, array $data = [])
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        parent::__construct($context, $registry, $formFactory, $data);
    }
    public function getModel()
    {
        $model = $this->_coreRegistry->registry('profile');
        return $model;
    }
    public function getActions()
    {
        $mapping = $this->dataHelper->getJsonAttributes($this->dataHelper::MODULES_ACTIONS);
        return $mapping;
    }
    public function getOperands()
    {
        $operands = ['and' => 'And', 'or' => 'Or'];
        return json_encode($operands);
    }
    public function getConditions()
    {
        $conditions = [];
        $conditions[0]['values'] = ['all' => '-- Select an element --'];
        // Add the file elements to the conditions
        $conditions[1]['groupname'] = __('File');
        foreach ($this->getModel()->getFileHeader() as $key => $label) {
            $conditions[1]['values'][$key] = $label;
        }
        // Add the Order attributes to the conditions
        $conditions[2]['groupname'] = __('Order');
        foreach ($this->dataHelper->getOrderConditions() as $key => $label) {
            $conditions[2]['values'][$key] = $label;
        }
        return json_encode($conditions);
    }
    public function getConditionOperands()
    {
        $conditionOperands = ['eq' => 'equal', 'gt' => 'greater', 'lt' => 'lower', 'gteq' => 'greater or equal', 'lteq' => 'lower or equal', 'neq' => 'not equal', 'like' => 'like', 'nlike' => 'not like', 'null' => 'null', 'notnull' => 'not null', 'in' => 'in', 'nin' => 'not in'];
        return json_encode($conditionOperands);
    }
    /**
     * Get Profile Id
     * @return mixed
     */
    public function getProfileId()
    {
        $model = $this->_coreRegistry->registry('profile');
        return $model->getId();
    }
    /**
     * @inheritdoc
     */
    public function getTabLabel()
    {
        return __('Rules');
    }
    /**
     * @inheritdoc
     */
    public function getTabTitle()
    {
        return __('Rules');
    }
    /**
     * @inheritdoc
     */
    public function canShowTab()
    {
        return true;
    }
    /**
     * @inheritdoc
     */
    public function isHidden()
    {
        return false;
    }
    /**
     * Get order statuses
     * @return string
     */
    public function getOrderStatuses()
    {
        $orderStatuses = [];
        foreach ($this->orderConfig->getStates() as $stateKey => $stateLabel) {
            foreach ($this->orderConfig->getStateStatuses($stateKey) as $statusKey => $statusLabel) {
                $orderStatuses[$statusKey] = $statusLabel;
            }
        }
        return json_encode($orderStatuses);
    }
    /**
     * Get order states
     * @return string
     */
    public function getOrderStates()
    {
        $orderStates = [];
        foreach ($this->orderConfig->getStates() as $stateKey => $stateLabel) {
            $orderStates[$stateKey] = $stateLabel;
        }
        return json_encode($orderStates);
    }
    /**
     * Get store ids
     * @return string
     */
    public function getStores()
    {
        $stores = [];
        foreach ($this->storeRepository->getList() as $store) {
            $stores[$store->getId()] = $store->getName();
        }
        return json_encode($stores);
    }
}