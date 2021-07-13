<?php
namespace AnyPlaceMedia\SendSMS\Model\Source;

class Products implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Retrieve options array.
     *
     * @return array
     */
    public function toOptionArray()
    {
        $result = [];

        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        $productCollectionFactory = $objectManager->get('\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
        $collection = $productCollectionFactory->create();
        $collection->addAttributeToSelect('*');

        foreach ($collection as $product) {
            $row = $product->getData();
            $result[] = ['value' => $row['entity_id'], 'label' => $row['name']];
        }

        return $result;
    }
}
