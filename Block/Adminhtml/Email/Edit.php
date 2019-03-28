<?php
/**
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 * http://www.magepal.com | support@magepal.com
 */

namespace MagePal\EditOrderEmail\Block\Adminhtml\Email;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Registry;
use Magento\Sales\Api\Data\OrderInterface;
use MagePal\EditOrderEmail\Helper\Data;

class Edit extends Template
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry = null;

    /**
     * MagePal Helper
     *
     * @var Data
     */
    protected $_helper;

    /**
     * @var Context $context
     * @var Registry $coreRegistry
     * @var AuthorizationInterface
     * @var Data $helper
     */
    protected $authorization;

    public function __construct(
        Context $context,
        Registry $coreRegistry,
        AuthorizationInterface $authorization,
        Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->coreRegistry = $coreRegistry;
        $this->authorization = $authorization;
        $this->_helper = $helper;
    }

    /**
     * @return string
     */
    public function getAdminPostUrl()
    {
        return $this->getUrl('editorderemail/edit/index');
    }

    /**
     * @return int
     */
    public function getOrderId()
    {
        return $this->getRequest()->getParam('order_id');
    }

    /**
     * @return int
     */
    public function getAutocheckEmail()
    {
        return $this->_helper->getConfigValue('magepal_editorderemail/general/update_customer_email');
    }

    public function getEmailAddress()
    {
        /** @var OrderInterface $order */
        if ($order = $this->coreRegistry->registry('sales_order')) {
            return $order->getCustomerEmail();
        }

        return '';
    }

    protected function _toHtml()
    {
        if (!$this->_authorization->isAllowed('MagePal_EditOrderEmail::magepal_editorderemail')) {
            return '';
        }

        return parent::_toHtml();
    }
}
