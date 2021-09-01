<?php

namespace AnyPlaceMedia\SendSMS\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

/**
 * Class OrderSave
 *
 * Watch for order status change
 *
 */
class OrderSave implements ObserverInterface
{
    protected $scopeConfig;
    protected $storeDate;
    protected $history;
    protected $helper;
    protected $pricingHelper;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \AnyPlaceMedia\SendSMS\Model\HistoryFactory $history,
        \AnyPlaceMedia\SendSMS\Helper\SendSMS $helper,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeDate = $date;
        $this->history = $history;
        $this->helper = $helper;
        $this->pricingHelper = $pricingHelper;
    }

    public function execute(Observer $observer)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $order = $observer->getEvent()->getOrder();
        $status = $order->getStatus();

        $data = $this->scopeConfig->getValue(
            'sendsms_settings/sendsms/sendsms_settings_messages',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($data) {
            $text = $objectManager->get(\Magento\Framework\Serialize\SerializerInterface::class)
                ->unserialize($data);
            foreach ($text as $config) {
                if ($status == $config['status']) {
                    $message = $config['message'];
                    $gdpr = $config['gdpr'] === "1" ? true : false;
                    $short = $config['short'] === "1" ? true : false;
                }
            }

            if (!empty($message)) {
                $message = $this->replaceVariables($message, $order);
                $this->helper->sendSMS($order->getBillingAddress()->getTelephone(), $message, 'order', $gdpr, $short);
            }
        }
    }

    /**
     * @param $message
     * @param $order
     * @return string
     */
    private function replaceVariables($message, $order)
    {
        $billingAddress = $order->getBillingAddress()->getData();
        $shippingAddress = $order->getShippingAddress()->getData();
        $formattedPrice = $this->pricingHelper->currency($order->getGrandTotal(), true, false);
        $tracksCollection = $order->getTracksCollection();
        $trackNumbers = [];
        foreach ($tracksCollection->getItems() as $track) {
            $trackNumbers[] = $track->getTrackNumber();
        }
        $trackingNumbers = implode(", ", $trackNumbers);
        $replace = [
            '{billing_first_name}' => $billingAddress['firstname'],
            '{billing_last_name}' => $billingAddress['lastname'],
            '{shipping_first_name}' => $shippingAddress['firstname'],
            '{shipping_last_name}' => $shippingAddress['lastname'],
            '{order_number}' => $order->getRealOrderId(),
            '{order_date}' => date('d.m.Y', strtotime($order->getCreatedAt())),
            '{order_total}' => $formattedPrice,
            '{tracking_numbers}' => $trackingNumbers
        ];
        foreach ($replace as $key => $value) {
            $message = str_replace($key, $value, $message);
        }

        return $message;
    }
}
