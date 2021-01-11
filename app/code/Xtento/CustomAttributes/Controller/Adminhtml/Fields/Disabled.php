<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-01-11T23:10:03+00:00
 * File:          app/code/Xtento/CustomAttributes/Controller/Adminhtml/Fields/Disabled.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Controller\Adminhtml\Fields;

use Magento\Backend\App\Action;

class Disabled extends Action
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $this->messageManager->addWarningMessage(
            __(
                'The extension is currently disabled. Please go to System > XTENTO Extensions > Custom Attributes to enable it. After that access the module here again.'
            )
        );
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);
        return $resultPage;
    }
}
