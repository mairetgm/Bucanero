<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-01-11T23:16:36+00:00
 * File:          app/code/Xtento/CustomAttributes/Model/Customer/Attribute/Backend/File.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Model\Customer\Attribute\Backend;

class File extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    const MEDIA_SUB_FOLDER = 'customer';
    const ALLOWED_EXTENSIONS = [
        'txt',
        'pdf',
        'jpg',
        'jpeg',
        'png'
    ];
}