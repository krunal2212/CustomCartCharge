<?php

namespace Krunal\PercentShipping\Model\Carrier;

use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\Cart;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Myshipping
 *
 * @package Krunal\PercentShipping\Model\Carrier
 */
class Myshipping extends AbstractCarrier implements
    CarrierInterface
{
    /**
     * @var string
     */
    protected $_code = 'myshipping';
    /**
     * @var bool
     */
    protected $_isFixed = false;
    /**
     * @var ResultFactory
     */
    protected $_rateResultFactory;
    /**
     * @var MethodFactory
     */
    protected $_rateMethodFactory;
    /**
     * @var Cart
     */
    protected $_cart;
    /**
     * @var Product
     */
    protected $_product;
    /**
     * @var StoreManagerInterface
     */
    protected $_store;
    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * Constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param ResultFactory $rateResultFactory
     * @param MethodFactory $rateMethodFactory
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        Cart $cartData,
        Product $product,
        StoreManagerInterface $storeManager,
        Session $customerSession,
        array $data = []
    ) {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->_cart = $cartData;
        $this->_product = $product;
        $this->_store = $storeManager;
        $this->_customerSession = $customerSession;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function collectRates(RateRequest $request)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $shippingPrice = $this->getConfigData('price');
        $configCustomerGroup = explode(',', $this->getConfigData('allowed_customer_groups'));
        $customerGroup = 0; // Not loggedin ID
        if ($this->_customerSession->isLoggedIn()) {
            $customerGroup = $this->_customerSession->getCustomer()->getGroupId();
        }

        if (!$this->getConfigFlag('active') || !in_array($customerGroup, $configCustomerGroup)) {
            return false;
        }

        $shippingPriceValue = 0;
        foreach ($this->_cart->getQuote()->getAllItems() as $item) {
            $productType = $item->getProduct()->getTypeId();
            $qty = $item->getQty();
            if ($productType == 'configurable') {
                continue;
            }
            if(!empty($item->getParentItem()))
            {
                $qty = $item->getParentItem()->getTotalQty();
            }
            $productId = $item->getProduct()->getId();
            $product = $objectManager->create('Magento\Catalog\Model\Product')->load($productId);
            $price = $qty * $product->getPrice();
            $ignoreShipping = $product->getIgnoreFromShipping();
            $producShippingPrice = $product->getAddShippingPrice();
            $priceCalculate = $price * ($shippingPrice / 100);
            if ($producShippingPrice != '' && $ignoreShipping == false) {
                $shippingPriceValue += $producShippingPrice;
            } elseif ($ignoreShipping == true) {
                $shippingPriceValue += 0;
            } else {
                $shippingPriceValue += $priceCalculate;
            }
        }

        $result = $this->_rateResultFactory->create();

        if ($shippingPrice !== false) {
            $method = $this->_rateMethodFactory->create();
            $method->setCarrier($this->_code);
            $method->setCarrierTitle($this->getConfigData('title'));
            $method->setMethod($this->_code);
            $method->setMethodTitle($this->getConfigData('name'));
            $method->setPrice($shippingPriceValue);
            $method->setCost($shippingPriceValue);
            $result->append($method);
        }

        return $result;
    }

    /**
     * getAllowedMethods
     *
     * @param array
     */
    public function getAllowedMethods()
    {
        return [$this->_code => $this->getConfigData('name')];
    }
}
