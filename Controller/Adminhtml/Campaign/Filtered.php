<?php

namespace AnyPlaceMedia\SendSMS\Controller\Adminhtml\Campaign;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;
use Magento\Framework\Controller\Result\JsonFactory;

class Filtered extends \Magento\Backend\App\Action
{

    protected $resultPageFactory;

    protected $resultJsonFactory;

    protected $_coreRegistry;

    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        Registry $coreRegistry,
        JsonFactory $resultJsonFactory
    ) {
        $this->resultPageFactory = $pageFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->resultJsonFactory = $resultJsonFactory;
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
        $county = $this->getRequest()->getParam('county');

        # do query to get phone numbers
        $where = [];
        $binds = [];
        if (!empty($startDate)) {
            $startDate = date('Y-m-d', strtotime($startDate));
            $where[] = 'so.created_at >= :START_DATE';
            $binds['START_DATE'] = $startDate . ' 00:00:00';
        }
        if (!empty($endDate)) {
            $endDate = date('Y-m-d', strtotime($endDate));
            $where[] = 'so.created_at <= :END_DATE';
            $binds['END_DATE'] = $endDate . ' 23:59:59';
        }
        if (!empty($minSum)) {
            $where[] = 'so.base_grand_total >= :MIN_SUM';
            $binds['MIN_SUM'] = $minSum;
        }
        if (!empty($product)) {
            $in = '';
            foreach ($product as $pd) {
                $in .= '\'' . (int)$pd . '\', ';
            }
            if (!empty($in)) {
                $in = substr($in, 0, -2);
            }
            $where[] = "soi.product_id IN ($in)";
        }
        if (!empty($county)) {
            $in = '';
            foreach ($county as $ct) {
                $in .= '\'' . strip_tags($ct) . '\', ';
            }
            if (!empty($in)) {
                $in = substr($in, 0, -2);
            }
            $where[] = "soa.region IN ($in)";
        }

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        if (!empty($product)) {
            $sql = 'SELECT DISTINCT soa.telephone FROM ' . $resource->getTableName('sales_order') . ' AS so, ' . $resource->getTableName('sales_order_item') . ' AS soi, ' . $resource->getTableName('sales_order_address') . ' AS soa WHERE soa.parent_id = so.entity_id AND soi.order_id = so.entity_id AND so.state = \'complete\' AND ' . implode(' AND ', $where);
        } else {
            $sql = 'SELECT DISTINCT soa.telephone FROM ' . $resource->getTableName('sales_order') . ' AS so, ' . $resource->getTableName('sales_order_address') . ' AS soa WHERE soa.parent_id = so.entity_id AND so.state = \'complete\'' . (count($where) ? ' AND ' . implode(' AND ', $where) : '');
        }
        $results = $connection->fetchAll($sql, $binds);
        # send collection to registry
        $registry = $objectManager->get('Magento\Framework\Registry');
        $registry->register('sendsms_filters', $results);

        if (is_array($postData)) {
            $message = $postData['message'];
            $phones = $results;
            if (!empty($message) && count($phones)) {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $helper = $objectManager->get('AnyPlaceMedia\SendSMS\Helper\SendSMS');
                $helper->batchCreate($phones, $message);
            }
            # redirect back
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/index', array(
                '_query' => array('sent' => 1)
            ));
        }
        $this->_coreRegistry->register('phonesno', count($results));

        return $this->resultPageFactory->create();
    }

    public function checkPrice()
    {
        error_log("#232");
    }

    /*
     * Check permission via ACL resource
     */
    protected function _isAllowed()
    {
        return true;
    }
}
