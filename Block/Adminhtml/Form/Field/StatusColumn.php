<?php

declare(strict_types=1);

namespace AnyPlaceMedia\SendSMS\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Html\Select;

class StatusColumn extends Select
{
    /**
     * Set "name" for <select> element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Set "id" for <select> element
     *
     * @param $value
     * @return $this
     */
    public function setInputId($value)
    {
        return $this->setId($value);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->getSourceOptions());
        }
        return parent::_toHtml();
    }

    private function getSourceOptions(): array
    {
        $manager = \Magento\Framework\App\ObjectManager::getInstance();
        $statuses = $manager->create('\Magento\Sales\Model\ResourceModel\Order\Status\Collection::class')
            ->toOptionArray();
        return $statuses;
    }
}
