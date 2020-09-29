<?php
/**
 * PSiGate Payment Module.
 *
 * @category  Payment Integration
 * @package   Rootways_Psigate
 * @author    Developer RootwaysInc <developer@rootways.com>
 * @copyright 2017 Rootways Inc. (https://www.rootways.com)
 * @license   Rootways Custom License
 * @link      https://www.rootways.com/pub/media/extension_doc/license_agreement.pdf
 */
namespace Rootways\Psigate\Model\Source;

use Magento\Payment\Model\Method\AbstractMethod;

class PaymentAction implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Possible actions on order place.
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => AbstractMethod::ACTION_AUTHORIZE,
                'label' => __('Authorize'),
            ],
            [
                'value' => AbstractMethod::ACTION_AUTHORIZE_CAPTURE,
                'label' => __('Authorize and Capture'),
            ],
        ];
    }
}
