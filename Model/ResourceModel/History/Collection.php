<?php namespace AnyPlaceMedia\SendSMS\Model\ResourceModel\History;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'history_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('AnyPlaceMedia\SendSMS\Model\History', 'AnyPlaceMedia\SendSMS\Model\ResourceModel\History');
    }

}