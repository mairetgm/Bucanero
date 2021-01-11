<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2020-04-09T14:01:11+00:00
 * File:          app/code/Xtento/CustomAttributes/Plugin/Customer/Model/AttributePlugin.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Plugin\Customer\Model;

use Magento\Framework\Api\FilterBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Xtento\CustomAttributes\Helper\Data as DataHelper;
use Magento\Backend\Model\Session;
use Magento\Framework\App\RequestInterface;
use Xtento\CustomAttributes\Plugin\Eav\Model\AbstractAttributePlugin;

class AttributePlugin extends AbstractAttributePlugin
{
    /**
     * @var Session
     */
    protected $backendSession;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * AttributePlugin constructor.
     *
     * @param DataHelper $dataHelper
     * @param FilterBuilder $filterBuilder
     * @param StoreManagerInterface $storeManager
     * @param Session $backendSession
     * @param RequestInterface $request
     */
    public function __construct(
        DataHelper $dataHelper,
        FilterBuilder $filterBuilder,
        StoreManagerInterface $storeManager,
        Session $backendSession,
        RequestInterface $request
    ) {
        parent::__construct($dataHelper, $filterBuilder, $storeManager);
        $this->backendSession = $backendSession;
        $this->request = $request;
    }


    /**
     * This is required - only executed via DI in adminhtml
     *
     * \Magento\Customer\Model\Customer\DataProvider::canShowAttributeInForm checks for these to be there when checking if our custom attributes are supposed to be shown in the backend, but sometimes attributes are not supposed ot be shown in the frontend, so we only add them for admin executions here.
     *
     * @param \Magento\Customer\Model\Attribute $subject
     * @param $result
     *
     * @return mixed
     */
    public function afterGetUsedInForms(\Magento\Customer\Model\Attribute $subject, $result)
    {
        $userDefined = (bool)$subject->getIsUserDefined();
        if ($userDefined) {
            if ($this->checkAttributeScope($subject) === false) {
                return [];
            }
            if (in_array('adminhtml_customer', $result) && !in_array('customer_account_create', $result)) {
                $result[] = 'customer_account_create';
            }
            if (in_array('adminhtml_customer', $result) && !in_array('customer_account_edit', $result)) {
                $result[] = 'customer_account_edit';
            }
            if (in_array('adminhtml_customer_address', $result) && !in_array('customer_address_edit', $result)) {
                $result[] = 'customer_address_edit';
            }
        }
        return $result;
    }

    /**
     * @param \Magento\Customer\Model\Attribute $subject
     * @param callable $proceed
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundGetIsVisible(\Magento\Customer\Model\Attribute $subject, callable $proceed)
    {
        $this->checkAttributeScope($subject, 'is_visible');
        return $proceed();

    }

    /**
     * @param \Magento\Customer\Model\Attribute $subject
     * @param callable $proceed
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundGetIsRequired(\Magento\Customer\Model\Attribute $subject, callable $proceed)
    {
        if ($this->checkAttributeScope($subject, 'is_required') === false) {
            return false;
        }
        return $proceed();
    }

    /**
     * @param $subject
     * @param bool $field
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function checkAttributeScope($subject, $field = false)
    {
        // Should only apply when editing a customer in the backend
        $customerData = $this->backendSession->getCustomerData();
        if (!$customerData || !is_array($customerData) || !isset($customerData['account'])) {
            return true;
        }
        // Check if is "new customer", if so, we don't know the store yet, so field cannot be required
        if ($field === 'is_required') {
            if (isset($customerData['customer_id']) && $customerData['customer_id'] === 0) {
                $subject->setData('scope_' . $field, 0);
                return false;
            }
        }
        //
        $customerAccount = $customerData['account'];
        if (!isset($customerAccount['website_id'])) {
            return true;
        }
        $customerWebsiteId = $customerAccount['website_id'];
        $websiteStores = $this->storeManager->getWebsite($customerWebsiteId)->getStores();

        // Load attributes
        $customAttributesByEntity = $this->fetchCustomAttributes($subject->getEntityTypeId());
        foreach ($customAttributesByEntity as $entity => $customAttributes) {
            foreach ($customAttributes as $attributeCode => $customAttribute) {
                if ($customAttribute->getAttributeCode() == $subject->getAttributeCode()) {
                    if ($field == 'is_required') {
                        // Check is validation, if yes return not required
                        if ($this->request->getModuleName() == 'customer' && $this->request->getActionName() == 'validate') {
                            return false;
                        }
                    }
                    $storeIds = $customAttribute->getStoreId();
                    if (!is_array($storeIds)) {
                        $storeIds = explode(",", $storeIds);
                    }
                    $applyToAllStoreViews = false;
                    foreach ($storeIds as $storeId) {
                        if ($storeId == 0) {
                            $applyToAllStoreViews = true;
                            break 1;
                        }
                    }
                    if (!$applyToAllStoreViews) {
                        $storeViewInCustomerWebsite = false;
                        foreach ($websiteStores as $store) {
                            foreach ($storeIds as $storeId) {
                                if ($storeId == $store->getId()) {
                                    $storeViewInCustomerWebsite = true;
                                    break 2;
                                }
                            }
                        }
                        if (!$storeViewInCustomerWebsite) {
                            // Attribute not enabled for current store, not required thus
                            if ($field !== false) {
                                $subject->setData('scope_' . $field, 0);
                            }
                            return false;
                        }
                    }
                    break 2;
                }
            }
        }
        return true;
    }
}