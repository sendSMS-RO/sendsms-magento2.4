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
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('customer_address_entity');
        $sql = "SELECT DISTINCT region FROM " . $tableName;
        $results = $connection->fetchAll($sql);

        foreach ($results as $row) {
            $result[] = ['value' => $row['region'], 'label' => $row['region']];
        }

        return $result;
    }
}
