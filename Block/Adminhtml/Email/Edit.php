<?php
/**
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 * http://www.magepal.com | support@magepal.com
 */

namespace MagePal\EditOrderEmail\Block\Adminhtml\Email;

class Edit extends \Magento\Backend\Block\Template
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $authorization;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\AuthorizationInterface $authorization,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->coreRegistry = $coreRegistry;
        $this->authorization = $authorization;
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

    public function getEmailAddress()
    {
        /** @var \Magento\Sales\Api\Data\OrderInterface $order */
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
