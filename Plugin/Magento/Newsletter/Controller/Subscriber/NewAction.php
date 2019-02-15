<?php
/**
 * Copyright © Spencer Steiner.
 * See LICENSE.txt for license details.
 */

namespace SS\NewsletterAjax\Plugin\Magento\Newsletter\Controller\Subscriber;

use Magento\Customer\Api\AccountManagementInterface as CustomerAccountManagement;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Message\Manager;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class NewAction
 *
 * @package SS\NewsletterAjax\Plugin\Magento\Newsletter\Controller\Subscriber
 */
class NewAction extends \Magento\Newsletter\Controller\Subscriber\NewAction
{
    /**
     * @var RequestInterface
     */
    protected $request;
    /**
     * @var JsonFactory
     */
    protected $jsonFactory;
    /**
     * @var Manager
     */
    protected $messageManager;

    /**
     * NewAction constructor.
     *
     * @param RequestInterface $request
     * @param JsonFactory $jsonFactory
     * @param Manager $messageManager
     * @param Context $context
     * @param SubscriberFactory $subscriberFactory
     * @param Session $customerSession
     * @param StoreManagerInterface $storeManager
     * @param CustomerUrl $customerUrl
     * @param CustomerAccountManagement $customerAccountManagement
     */
    public function __construct(
        RequestInterface $request,
        JsonFactory $jsonFactory,
        Manager $messageManager,
        Context $context,
        SubscriberFactory $subscriberFactory,
        Session $customerSession,
        StoreManagerInterface $storeManager,
        CustomerUrl $customerUrl,
        CustomerAccountManagement $customerAccountManagement
    ) {
        $this->request = $request;
        $this->jsonFactory = $jsonFactory;
        $this->messageManager = $messageManager;
        parent::__construct(
            $context,
            $subscriberFactory,
            $customerSession,
            $storeManager,
            $customerUrl,
            $customerAccountManagement
        );
    }

    /**
     * Executes method around
     *
     * @param \Magento\Newsletter\Controller\Subscriber\NewAction $subject
     * @param callable $proceed
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function aroundExecute(\Magento\Newsletter\Controller\Subscriber\NewAction $subject, callable $proceed)
    {
        $isAjax = $this->request->isXmlHttpRequest();

        if ($isAjax) {
            $this->messageManager->getMessages(true);
            return $this->saveSubscription();
        }

        return $proceed();
    }

    /**
     * Save subscription, validation used from parent class
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function saveSubscription()
    {
        $email = $this->request->getParam('email');
        $jsonFactory = $this->jsonFactory->create();

        try {
            $this->validateEmailFormat($email);
            $this->validateGuestSubscription();
            $this->validateEmailAvailable($email);

            $subscriber = $this->_subscriberFactory->create()->loadByEmail($email);
            if ($subscriber->getId()
                && $subscriber->getSubscriberStatus() == \Magento\Newsletter\Model\Subscriber::STATUS_SUBSCRIBED
            ) {
                $jsonData = [
                    'status' => 'error',
                    'message' => __('This email address is already subscribed.')
                ];

                return $jsonFactory->setData($jsonData);
            }

            $status = $this->_subscriberFactory->create()->subscribe($email);

            if ($status == \Magento\Newsletter\Model\Subscriber::STATUS_NOT_ACTIVE) {
                $jsonData = [
                    'status' => 'success',
                    'message' => __('The confirmation request has been sent.')
                ];
            } else {
                $jsonData = [
                    'status' => 'success',
                    'message' => __('Thank you for your subscription.')
                ];
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $jsonData = [
                'status' => 'error',
                'message' => __('There was a problem with the subscription: %1', $e->getMessage())
            ];
        } catch (\Exception $e) {
            $jsonData = [
                'status' => 'error',
                'message' => __('Something went wrong with the subscription.')
            ];
        }

        return $jsonFactory->setData($jsonData);
    }
}
