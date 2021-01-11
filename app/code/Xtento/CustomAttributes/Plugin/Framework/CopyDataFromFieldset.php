<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-01-12T17:30:34+00:00
 * File:          app/code/Xtento/CustomAttributes/Plugin/Framework/CopyDataFromFieldset.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Plugin\Framework;

use Magento\Framework\DataObject\Copy as DataCopy;
use Magento\Framework\DataObject\Copy\Config as FieldsetConfig;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\App\State;

class CopyDataFromFieldset
{

    /**
     * @var DataCopy
     */
    private $dataCopy;

    /**
     * @var FieldsetConfig
     */
    private $fieldsetConfig;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var State
     */
    private $state;

    /**
     * CopyDataFromFieldset constructor.
     * @param DataCopy $dataCopy
     * @param FieldsetConfig $fieldsetConfig
     * @param ManagerInterface $eventManager
     * @param DataObjectFactory $dataObjectFactory
     * @param State $state
     */
    public function __construct(
        DataCopy $dataCopy,
        FieldsetConfig $fieldsetConfig,
        ManagerInterface $eventManager,
        DataObjectFactory $dataObjectFactory,
        State $state
    ) {
        $this->dataCopy          = $dataCopy;
        $this->fieldsetConfig    = $fieldsetConfig;
        $this->eventManager      = $eventManager;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->state             = $state;
    }

    /**
     * @param $subject
     * @param \Closure $procede
     * @param $fieldset
     * @param $aspect
     * @param $source
     * @param string $root
     * @return array|null
     */
    public function aroundGetDataFromFieldset(
        $subject,
        callable $proceed,
        $fieldset,
        $aspect,
        $source,
        $root = 'global'
    ) {
        if (!(is_array($source) || $source instanceof \Magento\Framework\DataObject)) {
            return null;
        }
        $fields = $this->fieldsetConfig->getFieldset($fieldset, $root);
        if ($fields === null) {
            return null;
        }

        $data = [];
        foreach ($fields as $code => $node) {
            if (empty($node[$aspect])) {
                continue;
            }

            $value = $this->getFieldsetFieldValue($source, $code);

            $targetCode = (string)$node[$aspect];
            $targetCode = $targetCode == '*' ? $code : $targetCode;
            $data[$targetCode] = $value;
        }

        $sourceData = $this->dataObjectFactory->create()->setData($data);

        $this->dispatchGetDataFromFieldSetEvent($fieldset, $aspect, $source, $root, $sourceData);

        $proceed($fieldset, $aspect, $source, $root);

        $data = $sourceData->getData();

        return $data;
    }

    /**
     * @param $fieldset
     * @param $aspect
     * @param $source
     * @param $root
     * @param $sourceData
     * @return array
     */
    private function dispatchGetDataFromFieldSetEvent($fieldset, $aspect, $source, $root, $sourceData)
    {
        $state = $this->state->getAreaCode();
        $getDataFieldSet = 'get_data_fieldset_%s_%s';
        if ($state === 'adminhtml') {
            $getDataFieldSet = 'get_data_fieldset_admin_%s_%s';
        }

        $eventName = sprintf($getDataFieldSet, $fieldset, $aspect);

        $this->eventManager->dispatch(
            $eventName,
            ['source_data' => $sourceData, 'source' => $source, 'root' => $root]
        );

        $data = $sourceData->getData();

        return $data;
    }

    /**
     * @param $source
     * @param $code
     * @return mixed|null
     */
    private function getFieldsetFieldValue($source, $code)
    {
        if (is_array($source)) {
            $value = isset($source[$code]) ? $source[$code] : null;
        } elseif ($source instanceof \Magento\Framework\DataObject) {
            $value = $source->getDataUsingMethod($code);
        } elseif ($source instanceof \Magento\Framework\Api\ExtensibleDataInterface) {
            $value = $this->getAttributeValueFromExtensibleDataObject($source, $code);
        } elseif ($source instanceof \Magento\Framework\Api\AbstractSimpleObject) {
            $sourceArray = $source->__toArray();
            $value = isset($sourceArray[$code]) ? $sourceArray[$code] : null;
        } else {
            throw new \InvalidArgumentException(
                'Source should be array, Magento Object, ExtensibleDataInterface, or AbstractSimpleObject'
            );
        }
        return $value;
    }

    /**
     * @param $source
     * @param $code
     * @return mixed
     */
    protected function getAttributeValueFromExtensibleDataObject($source, $code)
    {
        $method = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $code)));

        $methodExists = method_exists($source, $method);
        if ($methodExists == true) {
            $value = $source->{$method}();
        } else {
            // If we couldn't find the method, check if we can get it from the extension attributes
            $extensionAttributes = $source->getExtensionAttributes();
            if ($extensionAttributes == null) {
                throw new \InvalidArgumentException('Method in extension does not exist.');
            } else {
                $extensionMethodExists = method_exists($extensionAttributes, $method);
                if ($extensionMethodExists == true) {
                    $value = $extensionAttributes->{$method}();
                } else {
                    throw new \InvalidArgumentException('Attribute in object does not exist.');
                }
            }
        }
        return $value;
    }
}
