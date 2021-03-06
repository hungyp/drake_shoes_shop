<?php

namespace Vnpayment\VNPAY\Controller\Order;

use Magento\Framework\App\Action\Context;

class Pay extends \Magento\Framework\App\Action\Action {

    /** @var  \Magento\Sales\Model\Order */
    protected $order;

    /** @var  \Magento\Checkout\Model\Session */
    protected $checkoutSession;

    /** @var  \Magento\Framework\App\Config\ScopeConfigInterface */
    protected $scopeConfig;

    public function __construct(Context $context, \Magento\Sales\Model\Order $order, \Magento\Checkout\Model\Session $checkoutSession, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);
        $this->order = $order;
        $this->checkoutSession = $checkoutSession;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Order success action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute() {
        $vnp_SecureHash = $this->getRequest()->getParam('vnp_SecureHash', '');
        $SECURE_SECRET = $this->scopeConfig->getValue('payment/vnpay/hash_code');
        $responseParams = $this->getRequest()->getParams();
        $vnp_ResponseCode = $this->getRequest()->getParam('vnp_ResponseCode', '');
        $inputData = array();
        foreach ($responseParams as $key => $value) {
            $inputData[$key] = $value;
        }
        unset($inputData['vnp_SecureHashType']);
        unset($inputData['vnp_SecureHash']);
        ksort($inputData);
        $i = 0;
        $hashData = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $SECURE_SECRET);
        if ($secureHash == $vnp_SecureHash) {
            if ($vnp_ResponseCode == '00') {
                $this->messageManager->addSuccess('Thanh to??n th??nh c??ng qua VNPAY');
                return $this->resultRedirectFactory->create()->setPath('checkout/onepage/success');
            } else {
                $this->messageManager->addError('Thanh to??n qua VNPAY th???t b???i. ' . $this->getResponseDescription($vnp_ResponseCode));
                return $this->resultRedirectFactory->create()->setPath('checkout/onepage/failure');
            }
        } else {
             $this->messageManager->addError('Thanh to??n qua VNPAY th???t b???i.');
             return $this->resultRedirectFactory->create()->setPath('checkout/onepage/failure');
        }
    }

    public function getResponseDescription($responseCode) {

        switch ($responseCode) {
            case "00" :
                $result = "Giao d???ch th??nh c??ng";
                break;
            case "01" :
                $result = "Giao d???ch ???? t???n t???i";
                break;
            case "02" :
                $result = "Merchant kh??ng h???p l??? (ki???m tra l???i vnp_TmnCode)";
                break;
            case "03" :
                $result = "D??? li???u g???i sang kh??ng ????ng ?????nh d???ng";
                break;
            case "04" :
                $result = "Kh???i t???o GD kh??ng th??nh c??ng do Website ??ang b??? t???m kh??a";
                break;
            case "05" :
                $result = "Giao d???ch kh??ng th??nh c??ng do: Qu?? kh??ch nh???p sai m???t kh???u qu?? s??? l???n quy ?????nh. Xin qu?? kh??ch vui l??ng th???c hi???n l???i giao d???ch";
                break;
            case "06" :
                $result = "Giao d???ch kh??ng th??nh c??ng do Qu?? kh??ch nh???p sai m???t kh???u x??c th???c giao d???ch (OTP). Xin qu?? kh??ch vui l??ng th???c hi???n l???i giao d???ch.";
                break;
            case "07" :
                $result = "Giao d???ch b??? nghi ng??? l?? giao d???ch gian l???n";
                break;
            case "09" :
                $result = "Giao d???ch kh??ng th??nh c??ng do: Th???/T??i kho???n c???a kh??ch h??ng ch??a ????ng k?? d???ch v??? InternetBanking t???i ng??n h??ng.";
                break;
            case "10" :
                $result = "Giao d???ch kh??ng th??nh c??ng do: Kh??ch h??ng x??c th???c th??ng tin th???/t??i kho???n kh??ng ????ng qu?? 3 l???n";
                break;
            case "11" :
                $result = "Giao d???ch kh??ng th??nh c??ng do: ???? h???t h???n ch??? thanh to??n. Xin qu?? kh??ch vui l??ng th???c hi???n l???i giao d???ch.";
                break;
            case "12" :
                $result = "Giao d???ch kh??ng th??nh c??ng do: Th???/T??i kho???n c???a kh??ch h??ng b??? kh??a.";
                break;
            case "51" :
                $result = "Giao d???ch kh??ng th??nh c??ng do: T??i kho???n c???a qu?? kh??ch kh??ng ????? s??? d?? ????? th???c hi???n giao d???ch.";
                break;
            case "65" :
                $result = "Giao d???ch kh??ng th??nh c??ng do: T??i kho???n c???a Qu?? kh??ch ???? v?????t qu?? h???n m???c giao d???ch trong ng??y.";
                break;
            case "08" :
                $result = "Giao d???ch kh??ng th??nh c??ng do: H??? th???ng Ng??n h??ng ??ang b???o tr??. Xin qu?? kh??ch t???m th???i kh??ng th???c hi???n giao d???ch b???ng th???/t??i kho???n c???a Ng??n h??ng n??y.";
                break;
            case "99" :
                $result = "C?? l???i s???y ra trong qu?? tr??nh th???c hi???n giao d???ch";
                break;
            default :
                $result = "Giao d???ch th???t b???i - Failured";
        }
        return $result;
    }

}
