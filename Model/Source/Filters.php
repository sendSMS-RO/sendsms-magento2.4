<?php
namespace AnyPlaceMedia\SendSMS\Model\Source;

class Filters implements \Magento\Framework\Option\ArrayInterface
{
    protected $_coreRegistry = null;

    public function __construct(\Magento\Framework\Registry $_coreRegistry)
    {
        $this->_coreRegistry = $_coreRegistry;
    }
    /**
     * Retrieve options array.
     *
     * @return array
     */
    public function toOptionArray()
    {
        $result = [];
        $filters = $this->_coreRegistry->registry('sendsms_filters');
        if (!empty($filters)) {
            foreach ($filters as $filter) {
                $result[] = [
                    'value' => $filter['telephone'],
                    'label' => $filter['telephone']
                ];
            }
        }

        return $result;
    }
}
