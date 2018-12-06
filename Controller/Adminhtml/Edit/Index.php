<?php
/**
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 * http://www.magepal.com | support@magepal.com
*/

namespace MagePal\EditOrderEmail\Controller\Adminhtml\Edit;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

class Index extends Action
{
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magento\Customer\Api\AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * @var \Magento\Sales\Api\OrderCustomerManagementInterface
     */
    protected $orderCustomerService;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * Index constructor.
     * @param Context $context
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Customer\Api\AccountManagementInterface $accountManagement
     * @param \Magento\Sales\Api\OrderCustomerManagementInterface $orderCustomerService
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Customer\Api\AccountManagementInterface $accountManagement,
        \Magento\Sales\Api\OrderCustomerManagementInterface $orderCustomerService,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);

        $this->orderRepository = $orderRepository;
        $this->orderCustomerService = $orderCustomerService;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->accountManagement = $accountManagement;
    }

    /**
     * Index action
     * @return \Magento\Framework\Controller\Result\Json
     * @throws \Exception
     */
    public function execute()
    {
        $request = $this->getRequest();
        $orderId = $request->getPost('order_id', null);
        $emailAddress = $request->getPost('email', null);
        $oldEmailAddress = $request->getPost('old_email', null);
        $resultJson = $this->resultJsonFactory->create();

        if ($orderId) {
            /** @var  $order \Magento\Sales\Api\Data\OrderInterface */
            $order = $this->orderRepository->get($orderId);

            if ($order->getEntityId()
                && \Zend_Validate::is($emailAddress, 'EmailAddress')
            ) {
                try {
                    $order = $this->orderRepository->get($orderId);
                    $order->setCustomerEmail($emailAddress);
                    $this->orderRepository->save($order);

                    //if update customer email
                    if ($this->accountManagement->isEmailAvailable($emailAddress)) {
                    }

                    $this->messageManager->addSuccessMessage(__('Order was successfully converted.'));

                    return $resultJson->setData(
                        [
                            'error' => false,
                            'message' => __('Email address successfully changed.')
                        ]
                    );
                } catch (\Exception $e) {
                    return $resultJson->setData(
                        [
                            'error' => true,
                            'message' => $e->getMessage()
                        ]
                    );
                }
            } else {
                return $resultJson->setData(
                    [
                        'error' => true,
                        'message' => __('Invalid Email address.')
                    ]
                );
            }
        } else {
            return $resultJson->setData(
                [
                    'error' => true,
                    'message' => __('Invalid order id.')
                ]
            );
        }
    }

    /**
     * Is the user allowed to view the blog post grid.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MagePal_EditOrderEmail::magepal_editorderemail');
    }
}
