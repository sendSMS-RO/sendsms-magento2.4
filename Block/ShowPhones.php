<?php

namespace AnyPlaceMedia\SendSMS\Block;

use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template;
use Magento\Framework\Registry;

class ShowPhones extends Template
{
    protected $_coreRegistry;

    public function __construct(
        Context $context,
        Registry $coreRegistry
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    public function getPhones()
    {
        $phonesno = 0;
        if ($this->_coreRegistry->registry('phonesno')) {
            $phonesno = $this->_coreRegistry->registry('phonesno');
        }
        return "We found $phonesno phone numbers matching the filters.";
    }
}