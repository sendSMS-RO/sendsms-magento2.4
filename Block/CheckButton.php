<?php

namespace AnyPlaceMedia\SendSMS\Block;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\Store;
/**
 * Class SaveButton
 */
class CheckButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        $alert = "Please send an SMS first";
        if ($this->registry->registry('phonesno')) {
            $phonesno = $this->registry->registry('phonesno');
            $configs = $this->collection
                ->addFieldToFilter('scope', ScopeConfigInterface::SCOPE_TYPE_DEFAULT)
                ->addFieldToFilter('scope_id', Store::DEFAULT_STORE_ID)
                ->addFieldToFilter('path', ['in' => ['sendsms_settings/sendsms/sendsms_settings_price', 'sendsms_settings/sendsms/sendsms_settings_price_date']])
                ->getData();
        }
        return [
            'label' => __('Check price'),
            'on_click' => "alert('$alert')",
            'class' => 'primary',
            'sort_order' => 100
        ];
    }
}
