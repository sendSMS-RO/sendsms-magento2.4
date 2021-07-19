<?php


namespace AnyPlaceMedia\SendSMS\Block\Adminhtml\System;

use Magento\Framework\View\Element\AbstractBlock;
use Magento\Config\Model\Config\CommentInterface;

class DynamicComment extends AbstractBlock implements CommentInterface
{
    /**
     * @param string $elementValue
     * @return string
     */
    public function getCommentText($elementValue)
    {
        $url = 'https://www.sendsms.ro/en/contact-2/';
        return "If you don't have a sender ID, contact our team <a href='$url' target='_blank'>here</a>";
    }
}
