<?php
/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrdersExportTool\Controller\Adminhtml\Functions;

/**
 * Delete action
 */
abstract class AbstractFunctions extends \Wyomind\OrdersExportTool\Controller\Adminhtml\AbstractAction
{
    public $title = 'Mass Order Export > Custom Functions';
    public $breadcrumbFirst = 'Mass Order Export';
    public $breadcrumbSecond = 'Manage Custom Functions';
    public $model = 'Wyomind\OrdersExportTool\Model\Functions';
    public $errorDoesntExist = "This function doesn't exist anymore.";
    public $successDelete = 'The function has been deleted.';
    public $msgModify = 'Modify custom function';
    public $msgNew = 'New custom function';
    public $registryName = 'function';
    public $menu = 'functions';
}