<?php
namespace AnyPlaceMedia\SendSMS\Controller\Adminhtml\Test;

class Index extends \Magento\Backend\App\Action
{
    protected $resultPageFactory = false;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('AnyPlaceMedia_SendSMS::test');
        $resultPage->getConfig()->getTitle()->prepend(__('Test'));
        $resultPage->addBreadcrumb(__('AnyPlaceMedia'), __('AnyPlaceMedia'));
        $resultPage->addBreadcrumb(__('SendSMS'), __('Test'));

        # POST
        $phone = $this->getRequest()->getParam('phone');
        $message = $this->getRequest()->getParam('message');
        $gdpr = $this->getRequest()->getParam('gdpr');
        $short = $this->getRequest()->getParam('short');


        if (!empty($phone) && !empty($message)) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $helper = $objectManager->get('AnyPlaceMedia\SendSMS\Helper\SendSMS');
            $helper->sendSMS($phone, $message, 'test', $gdpr, $short);

            $messageBlock = $resultPage->getLayout()->createBlock(
                'Magento\Framework\View\Element\Messages',
                'answer'
            );
            $messageBlock->addSuccess('The message was sent.');
            $resultPage->getLayout()->setChild(
                'sendsms_messages',
                $messageBlock->getNameInLayout(),
                'answer_alias'
            );
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
