<?php        
namespace Micerveza\Generalconfig\Model\Observer;
use Magento\Framework\Event\ObserverInterface;    
class Changecurrencyposition implements ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {    
        $currencyOptions = $observer->getEvent()->getCurrencyOptions();    
        $currencyOptions->setData('position', \Magento\Framework\Currency::LEFT);
        return $this;
    }    
}