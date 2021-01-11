<?php

/**
 * Product:       Xtento_PdfCustomizer
 * ID:            TP2Z1gIjMryzjs+kTRDh6aWTwEp5w7T8imVFGAtG5js=
 * Last Modified: 2019-02-19T17:03:40+00:00
 * File:          app/code/Xtento/PdfCustomizer/Helper/Variable/Custom/CustomInterface.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */


namespace Xtento\PdfCustomizer\Helper\Variable\Custom;

interface CustomInterface
{
    /**
     * @return object
     */
    public function processAndReadVariables();

    /**
     * @param $source
     * @return object
     */
    public function entity($source);
}
