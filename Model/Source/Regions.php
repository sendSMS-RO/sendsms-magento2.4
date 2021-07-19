<?php

namespace AnyPlaceMedia\SendSMS\Model\Source;

class Regions implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Retrieve options array.
     *
     * @return array
     */
    public function toOptionArray()
    {
        $result = [];

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $collectionFactory = $objectManager->get(
            \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory::class
        );
        $resource = $objectManager->get(\Magento\Framework\App\ResourceConnection::class);
        $collection = $collectionFactory->create();
        $collection->getSelect()->join(
            $resource->getTableName('customer_address_entity'),
            'e.entity_id=' . $resource->getTableName('customer_address_entity') . '.parent_id',
            'region'
        );

        $data = $collection->getData();
        foreach ($data as $d) {
            if (!in_array(['value' => $d['region'], 'label' => $d['region']], $result)) {
                $result[] = ['value' => $d['region'], 'label' => $d['region']];
            }
        }
        return $result;
    }
}
