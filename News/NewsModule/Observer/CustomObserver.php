<?php

namespace News\NewsModule\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;

class CustomObserver implements ObserverInterface
{
    protected $_product;
    protected $_cart;

    protected $formKey;

    protected $request;
    /**
     * @var \Magento\Quote\Model\Quote\Item\Repository
     */
    protected $quoteRepository;

    private $config;


    public function __construct(
        \Magento\Catalog\Model\ProductFactory      $product,
        \Magento\Framework\Data\Form\FormKey       $formKey,
        \Magento\Checkout\Model\Cart               $cart,
        \Magento\Framework\App\RequestInterface    $request,
        \Magento\Quote\Model\Quote\Item\Repository $QuoteRepository,
        \News\NewsModule\Model\Config              $config
    )
    {
        $this->_product = $product;
        $this->formKey = $formKey;
        $this->_cart = $cart;
        $this->request = $request;
        $this->quoteRepository = $QuoteRepository;
        $this->config = $config;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $ProductSku1 = $this->config->getSku1();
        $ProductSku2 = $this->config->getSku2();
        $isFreeProduct = 0;
        $isMainProduct = 0;
        $existMainProduct = false;
        $items = $this->quoteRepository->getList($observer->getCart()->getQuote()->getId());

        foreach ($items as $item) {
            $item2 = $item->getData();
            if ($item2['sku'] == $ProductSku1) {
                $isMainProduct = 1;
            }
            if ($item2['sku'] == $ProductSku2) {
                $isFreeProduct = 1;
            }
        }

        if (!$isFreeProduct && $isMainProduct) {
            $params = array(
                'form_key' => $this->formKey->getFormKey(),
                'product_id' => 2113,
                'qty' => 1
            );
            $_product = $this->_product->create()->load(2113);
            $this->_cart->addProduct($_product, $params);
            $this->_cart->save();
        }

        foreach ($items as $item) {
            $item3 = $item->getData();
            if ($item3['sku'] == $ProductSku1) {
                $existMainProduct = true;
            }
            if ($item3['sku'] == $ProductSku2 && !$existMainProduct) {
                $this->quoteRepository->deleteById($item->getData('quote_id'), $item->getId());
            }
        }
    }
}
