<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_OrderStatus
 */


namespace Amasty\OrderStatus\Model\ResourceModel\Order\Status\Plugin;

use Amasty\OrderStatus\Model\ResourceModel\Status\Collection as AmastyOrderStatusCollection;
use Magento\Framework\App\Request\Http;

class Collection
{
    /**
     * @var AmastyOrderStatusCollection
     */
    private $amastyOrderStatusCollection;

    /**
     * @var Http
     */
    private $request;

    public function __construct(
        AmastyOrderStatusCollection $amastyOrderStatusCollection,
        Http $request
    ) {
        $this->amastyOrderStatusCollection = $amastyOrderStatusCollection;
        $this->request = $request;
    }

    public function afterToOptionArray($subject, $result)
    {
        if ($this->request->getControllerName() !== 'order_status') {
            $result = $this->amastyOrderStatusCollection->toOptionArray();
        }

        return $result;
    }
}
