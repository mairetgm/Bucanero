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
namespace Rootways\Psigate\Model\Response;

class Factory
{
    protected $objectManager;
    protected $instanceName;
    
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objManager,
        $psi_request = 'Rootways\\Psigate\\Model\\Response'
    )
    {
        $this->objectManager = $objManager;
        $this->instanceName = $psi_request;
    }
    
    public function create(array $psi_create = array())
    {
        return $this->objectManager->create($this->instanceName, $psi_create);
    }
}
