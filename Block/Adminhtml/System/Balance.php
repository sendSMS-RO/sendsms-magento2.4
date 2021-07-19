<?php


namespace AnyPlaceMedia\SendSMS\Block\Adminhtml\System;

use Magento\Framework\View\Element\AbstractBlock;
use Magento\Config\Model\Config\CommentInterface;

class Balance extends AbstractBlock implements CommentInterface
{
    /**
     * @param string $elementValue
     * @return string
     */
    public function getCommentText($elementValue)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helper = $objectManager->get(\AnyPlaceMedia\SendSMS\Helper\SendSMS::class);
        if ($helper->getBalance()) {
            if ($helper->getBalance()['status'] == 0) {
                return "You have " . $helper->getBalance()['details'] . " euro in your account";
            }
        }
        return "Please configure your module first";
    }
}
