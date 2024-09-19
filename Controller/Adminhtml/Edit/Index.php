<?php
/**
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 * https://www.magepal.com | support@magepal.com
*/

namespace MagePal\EditOrderEmail\Controller\Adminhtml\Edit;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Validator\EmailAddress;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderCustomerManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class Index extends Action
{

    const ADMIN_RESOURCE = 'MagePal_EditOrderEmail::magepal_editorderemail';
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
     * @var EmailAddress
     */
    private $emailAddressValidator;
    /**
     * @var Session
     */
    private $authSession;

    /**
     * Index constructor.
     * @param Context $context
     * @param OrderRepositoryInterface $orderRepository
     * @param AccountManagementInterface $accountManagement
     * @param OrderCustomerManagementInterface $orderCustomerService
     * @param JsonFactory $resultJsonFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param EmailAddress $emailAddressValidator
     * @param Session $authSession
     */
    public function __construct(
        Context $context,
        OrderRepositoryInterface $orderRepository,
        AccountManagementInterface $accountManagement,
        OrderCustomerManagementInterface $orderCustomerService,
        JsonFactory $resultJsonFactory,
        CustomerRepositoryInterface $customerRepository,
        EmailAddress $emailAddressValidator,
        Session $authSession
    ) {
        parent::__construct($context);

        $this->orderRepository = $orderRepository;
        $this->orderCustomerService = $orderCustomerService;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->accountManagement = $accountManagement;
        $this->customerRepository = $customerRepository;
        $this->emailAddressValidator = $emailAddressValidator;
        $this->authSession = $authSession;
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
        $emailAddress = trim($request->getPost('email'));
        $oldEmailAddress = $request->getPost('old_email');
        $updateCustomerEmailRecord = $request->getPost('update_customer_email');
        $resultJson = $this->resultJsonFactory->create();

        if (!isset($orderId)) {
            return $resultJson->setData(
                [
                    'error' => true,
                    'message' => __('Invalid order id.'),
                    'email' => '',
                    'ajaxExpired' => false
                ]
            );
        }

        if (!$this->emailAddressValidator->isValid($emailAddress)) {
            return $resultJson->setData(
                [
                    'error' => true,
                    'message' => __('Invalid Email address.'),
                    'email' => '',
                    'ajaxExpired' => false
                ]
            );
        }

        try {
            /** @var  $order OrderInterface */
            $order = $this->orderRepository->get($orderId);
            if ($order->getEntityId() && $order->getCustomerEmail() == $oldEmailAddress) {
                $comment = sprintf(
                    __("Order email address change from %s to %s by %s"),
                    $oldEmailAddress,
                    $emailAddress,
                    $this->authSession->getUser()->getUserName()
                );

                $order->addStatusHistoryComment($comment);
                $order->setCustomerEmail($emailAddress);
                $this->orderRepository->save($order);

                foreach ($order->getAddressesCollection() as $address)
                {
                    $address->setEmail($emailAddress)->save();
                }
            }

            //if update customer email
            if ($updateCustomerEmailRecord == 1
                && $order->getCustomerId()
                && $this->accountManagement->isEmailAvailable($emailAddress)
            ) {
                $customer = $this->customerRepository->getById($order->getCustomerId());
                if ($customer->getId()) {
                    $customer->setEmail($emailAddress);
                    $this->customerRepository->save($customer);
                }
            }

            return $resultJson->setData(
                [
                    'error' => false,
                    'message' => __('Email address successfully changed.'),
                    'email' => $emailAddress,
                    'ajaxExpired' => false
                ]
            );
        } catch (Exception $e) {
            return $resultJson->setData(
                [
                    'error' => true,
                    'message' => $e->getMessage(),
                    'email' => '',
                    'ajaxExpired' => false
                ]
            );
        }
    }
}
