<?php

namespace AnyPlaceMedia\SendSMS\Block;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\Store;

class CheckButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        $phonesno = 0;
        $price = 0;
        if ($this->registry->registry('phonesno')) {
            $phonesno = $this->registry->registry('phonesno');
            $configs = $this->collection
                ->addFieldToFilter('scope', ScopeConfigInterface::SCOPE_TYPE_DEFAULT)
                ->addFieldToFilter('scope_id', Store::DEFAULT_STORE_ID)
                ->addFieldToFilter('path', ['in' => ['sendsms_settings/sendsms/sendsms_settings_price']])
                ->getData();
            if (!empty($configs)) {
                $price = $configs[0]['value'];
            }
        }
        return [
            'label' => __('Check price'),
            'on_click' => "checkPrice($phonesno, $price)",
            'class' => 'primary',
            'sort_order' => 100
        ];
    }
}
