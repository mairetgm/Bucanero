<?php
/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrdersExportTool\Controller\Adminhtml\Variables;

abstract class AbstractVariables extends \Wyomind\OrdersExportTool\Controller\Adminhtml\AbstractAction
{
    public $title = 'Mass Order Export > Custom Variables';
    public $breadcrumbFirst = 'Mass Order Export';
    public $breadcrumbSecond = 'Manage Custom Variables';
    public $model = 'Wyomind\OrdersExportTool\Model\Variables';
    public $errorDoesntExist = 'This variable no longer exists.';
    public $successDelete = 'The variable has been deleted.';
    public $msgModify = 'Modify custom variable';
    public $msgNew = 'New custom variable';
    public $registryName = 'variable';
    public $menu = 'variables';
}