<?php

namespace AnyPlaceMedia\SendSMS\Controller\Adminhtml\Campaign;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Setup\Declaration\Schema\Dto\Factories\Unique;

class Filtered extends \Magento\Backend\App\Action
{
    protected $collectionFactory;

    protected $resultPageFactory;

    protected $resultJsonFactory;

    protected $_coreRegistry;

    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        Registry $coreRegistry,
        JsonFactory $resultJsonFactory,
        \Magento\Sales\Model\ResourceModel\Order\Collectionfactory $collectionFactory
    ) {
        $this->resultPageFactory = $pageFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        # send message
        $postData = $this->getRequest()->getParam('campaign_filtered_form');

        # data
        $startDate = $this->getRequest()->getParam('start_date');
        $endDate = $this->getRequest()->getParam('end_date');
        $minSum = $this->getRequest()->getParam('min_sum');
        $product = $this->getRequest()->getParam('product');
        $country = $this->getRequest()->getParam('county');

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get(\Magento\Framework\App\ResourceConnection::class);

        $results = $this->getOrderPhones($startDate, $endDate, $minSum, $product, $country, $resource);

        $registry = $objectManager->get(\Magento\Framework\Registry::class);

        $registry->register('sendsms_filters', $results);

        if (is_array($postData)) {
            $message = $postData['message'];
            $phones = $results;
            if (!empty($message) && count($phones)) {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $helper = $objectManager->get(\AnyPlaceMedia\SendSMS\Helper\SendSMS::class);
                $helper->batchCreate($phones, $message);
            }
            # redirect back
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/index', [
                '_query' => ['sent' => 1]
            ]);
        }
        $this->_coreRegistry->register('phonesno', count($results));

        return $this->resultPageFactory->create();
    }

    /**
     * Return phone of each order
     *
     * @return Countable|array
     */
    public function getOrderPhones($startDate, $endDate, $minSum, $product, $country, $resource)
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToSelect('entity_id');
        $collection->getSelect()->join(
            $resource->getTableName('sales_order_item'),
            'main_table.entity_id=' . $resource->getTableName('sales_order_item') . '.order_id',
            ''
        );
        $collection->getSelect()->join(
            $resource->getTableName('sales_order_address'),
            'main_table.entity_id=' . $resource->getTableName('sales_order_address') . '.parent_id',
            'telephone'
        );
        $collection->addFieldToFilter('status', 'complete');
        if (!empty($startDate)) {
            $startDate = date('Y-m-d', strtotime($startDate)) . ' 00:00:00';
            $collection->addFieldToFilter('created_at', ['gteq' => $startDate]);
        }
        if (!empty($endDate)) {
            $endDate = date('Y-m-d', strtotime($endDate)) . ' 23:59:59';
            $collection->addFieldToFilter('created_at', ['lteq' => $endDate]);
        }
        if (!empty($minSum)) {
            $collection->addFieldToFilter('base_grand_total', ['gteq' => $minSum]);
        }
        if (!empty($product)) {
            $collection->addFieldToFilter('product_id', ['in' => $product]);
        }
        if (!empty($country)) {
            $collection->addFieldToFilter('region', ['in' => $country]);
        }
        $data = $collection->getData();
        $uniquePhones = [];
        foreach ($data as $d) {
            $uniquePhones[] = $d['telephone'];
        }
        $uniquePhones = array_unique($uniquePhones);
        return $uniquePhones;
    }

    /*
     * Check permission via ACL resource
     */
    protected function _isAllowed()
    {
        return true;
    }
}
