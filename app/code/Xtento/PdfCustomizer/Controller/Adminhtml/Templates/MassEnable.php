<?php

/**
 * Product:       Xtento_PdfCustomizer
 * ID:            TP2Z1gIjMryzjs+kTRDh6aWTwEp5w7T8imVFGAtG5js=
 * Last Modified: 2019-02-19T17:03:40+00:00
 * File:          app/code/Xtento/PdfCustomizer/Controller/Adminhtml/Templates/MassEnable.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\PdfCustomizer\Controller\Adminhtml\Templates;

use Magento\Framework\Controller\ResultFactory;

class MassEnable extends MassAction
{

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->templateCollectionFactory->create());

        foreach ($collection as $item) {
            $item->setIsActive(true);
            //@codingStandardsIgnoreLine
            $item->save();
        }

        $this->messageManager->addSuccessMessage(
            __(
                'A total of %1 record(s) have been enabled.',
                $collection->getSize()
            )
        );

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
