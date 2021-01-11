<?php

/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\OrdersExportTool\Block\Adminhtml\Profiles;

/**
 * return the preview data
 */
class Preview extends \Magento\Backend\Block\Template
{
    public function __construct(\Wyomind\OrdersExportTool\Helper\Delegate $wyomind, \Magento\Backend\Block\Template\Context $context, array $data = [])
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        parent::__construct($context, $data);
    }
    /**
     * Get the content of the preview
     * @return string
     * @throws \Exception
     */
    public function getContent()
    {
        $request = $this->getRequest();
        $id = $request->getParam('id');
        $model = $this->_model;
        $model->limit = $this->_helper->getStoreConfig('ordersexporttool/system/preview');
        $model->setDisplay(true);
        if ($model->load($id)) {
            try {
                $content = $model->generate($request, true);
                return $content;
            } catch (\Exception $e) {
                return __('Unable to generate the profile : ' . nl2br($e->getMessage()));
            }
        }
    }
    public function needsCodeMirror()
    {
        $model = $this->_model;
        return $model->getType() == 1 || $model->getType() > 1 && $model->getFormat() == 2;
    }
}