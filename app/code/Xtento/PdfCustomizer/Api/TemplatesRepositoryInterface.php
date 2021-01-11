<?php

/**
 * Product:       Xtento_PdfCustomizer
 * ID:            TP2Z1gIjMryzjs+kTRDh6aWTwEp5w7T8imVFGAtG5js=
 * Last Modified: 2019-02-19T17:03:40+00:00
 * File:          app/code/Xtento/PdfCustomizer/Api/TemplatesRepositoryInterface.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\PdfCustomizer\Api;

use Xtento\PdfCustomizer\Api\Data\TemplatesInterface;

interface TemplatesRepositoryInterface
{

    /**
     * @param TemplatesInterface $templates
     * @return mixed
     */
    public function save(TemplatesInterface $templates);

    /**
     * @param $value the template id
     * @return mixed
     */
    public function getById($value);

    /**
     * @param TemplatesInterface $templates
     * @return mixed
     */
    public function delete(TemplatesInterface $templates);

    /**
     * @param $value the template id
     * @return mixed
     */
    public function deleteById($value);
}
