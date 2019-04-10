<?php
/**
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 * http://www.magepal.com | support@magepal.com
*/

namespace MagePal\EditOrderEmail\Controller\Adminhtml\Edit;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderCustomerManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Zend_Validate;

class Index extends Action
{
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * @var OrderCustomerManagementInterface
     */
    protected $orderCustomerService;

    /**
     * @var CustomerRepositoryInterface $customerRepository
     */
    protected $customerRepository;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * Index constructor.
     * @param Context $context
     * @param OrderRepositoryInterface $orderRepository
     * @param AccountManagementInterface $accountManagement
     * @param OrderCustomerManagementInterface $orderCustomerService
     * @param JsonFactory $resultJsonFactory
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        Context $context,
        OrderRepositoryInterface $orderRepository,
        AccountManagementInterface $accountManagement,
        OrderCustomerManagementInterface $orderCustomerService,
        JsonFactory $resultJsonFactory,
        CustomerRepositoryInterface $customerRepository
    ) {
        parent::__construct($context);

        $this->orderRepository = $orderRepository;
        $this->orderCustomerService = $orderCustomerService;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->accountManagement = $accountManagement;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Index action
     * @return Json
     * @throws Exception
     */
    public function execute()
    {
        $request = $this->getRequest();
        $orderId = $request->getPost('order_id');
        $emailAddress = $request->getPost('email');
        $oldEmailAddress = $request->getPost('old_email');
        $customerautocheck = $request->getPost('update_customer_email');
        $resultJson = $this->resultJsonFactory->create();

        if (!isset($orderId)) {
            return $resultJson->setData(
                [
                    'error' => true,
                    'message' => __('Invalid order id.')
                ]
            );
        }

        if (!Zend_Validate::is($emailAddress, 'EmailAddress')) {
            return $resultJson->setData(
                [
                    'error' => true,
                    'message' => __('Invalid Email address.')
                ]
            );
        }

        try {
            /** @var  $order OrderInterface */
            $order = $this->orderRepository->get($orderId);
            if ($order->getEntityId() && $order->getCustomerEmail() == $oldEmailAddress) {
                $order->setCustomerEmail($emailAddress);
                $this->orderRepository->save($order);
            }

            //if update customer email
            if ($customerautocheck == 1
                && $order->getCustomerId()
                && $this->accountManagement->isEmailAvailable($emailAddress)
            ) {
                $customer = $this->customerRepository->getById($order->getCustomerId());
                if ($customer->getId()) {
                    $customer->setEmail($emailAddress);
                    $this->customerRepository->save($customer);
                }
            }

            $this->messageManager->addSuccessMessage(__('Order was successfully converted.'));

            return $resultJson->setData(
                [
                    'error' => false,
                    'message' => __('Email address successfully changed.')
                ]
            );
        } catch (Exception $e) {
            return $resultJson->setData(
                [
                    'error' => true,
                    'message' => $e->getMessage()
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
