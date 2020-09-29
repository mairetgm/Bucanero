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
namespace Rootways\Psigate\Model;

class PsigateException extends \Magento\Framework\Exception\LocalizedException
{
    const AUTHENTICATION_ERROR = 'An authentication error occurred.';
}
