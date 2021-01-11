<?php
/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrdersExportTool\Controller\Adminhtml\Profiles;

/**
 * Delete action
 */
abstract class AbstractProfiles extends \Wyomind\OrdersExportTool\Controller\Adminhtml\AbstractAction
{
    public $title = 'Mass Order Export > Profiles';
    public $breadcrumbFirst = 'Mass Order Export';
    public $breadcrumbSecond = 'Manage Profiles';
    public $model = 'Wyomind\OrdersExportTool\Model\Profiles';
    public $errorDoesntExist = 'This profile no longer exists.';
    public $successDelete = 'The profile has been deleted.';
    public $msgModify = 'Modify profile';
    public $msgNew = 'New profile';
    public $registryName = 'profile';
    public $menu = 'profiles';
}