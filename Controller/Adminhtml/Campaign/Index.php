<?php
namespace AnyPlaceMedia\SendSMS\Controller\Adminhtml\Campaign;

class Index extends \Magento\Backend\App\Action
{
    protected $resultPageFactory = false;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('AnyPlaceMedia_SendSMS::campaign');
        $resultPage->getConfig()->getTitle()->prepend(__('Campaign'));
        $resultPage->addBreadcrumb(__('AnyPlaceMedia'), __('AnyPlaceMedia'));
        $resultPage->addBreadcrumb(__('SendSMS'), __('Campaign'));

        $postData = $this->getRequest()->getParam('campaign_form');
        if (is_array($postData)) {
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/filtered', [
                '_query' => $postData
            ]);
        }

        $message = $this->getRequest()->getParam('sent');
        if (!empty($message)) {
            $messageBlock = $resultPage->getLayout()->createBlock('Magento\Framework\View\Element\Messages', 'answer');
            $messageBlock->addSuccess('The messages have been sent.');
            $resultPage->getLayout()->setChild('sendsms_messages', $messageBlock->getNameInLayout(), 'answer_alias');
        }
        return $resultPage;
    }

    /*
     * Check permission via ACL resource
     */
    protected function _isAllowed()
    {
        return true;
    }
}
