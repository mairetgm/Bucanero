<?php

/**
 * Product:       Xtento_PdfCustomizer
 * ID:            TP2Z1gIjMryzjs+kTRDh6aWTwEp5w7T8imVFGAtG5js=
 * Last Modified: 2020-10-31T15:41:04+00:00
 * File:          app/code/Xtento/PdfCustomizer/Helper/Variable/Custom/Comments.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\PdfCustomizer\Helper\Variable\Custom;

use Magento\Catalog\Model\Product as ProductModel;
use Magento\Sales\Block\Order\Creditmemo;
use Magento\Sales\Model\Order;

class Comments
{
    /**
     * @var Order|Order\Invoice|Creditmemo
     */
    private $source;

    /**
     * @param $source
     * @return $this
     */
    public function entity($source)
    {
        if (is_object($source)) {
            $this->source = $source;
            $this->addComments();
            return $this;
        }
    }

    /**
     * @return $this
     */
    public function addComments()
    {
        if ($this->source instanceof ProductModel) {
            return $this;
        }

        if ($this->source instanceof Order) {
            $allCommentsCollection = $this->source->getAllStatusHistory();
            $commentString = '';
            if (!empty($allCommentsCollection)) {
                foreach ($allCommentsCollection as $comment) {
                    if (!empty($commentString)) $commentString .= '<br/>';
                    $commentString .= $comment->getData('comment');
                }
            }
            $this->source->setData('all_comments_text', $commentString);
        }

        if ($this->source instanceof Order) {
            $commentsCollection = $this->source->getVisibleStatusHistory();
        } else {
            $commentsCollection = $this->source->getCommentsCollection();
        }
        $commentString = '';
        if (!empty($commentsCollection)) {
            foreach ($commentsCollection as $comment) {
                if (!empty($commentString)) $commentString .= '<br/>';
                $commentString .= $comment->getData('comment');
            }
        }
        $this->source->setData('comments_text', $commentString);

        return $this;
    }
}
