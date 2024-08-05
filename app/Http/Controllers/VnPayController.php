<?php

namespace App\Http\Controllers;

use App\Models\DonHang;
use Illuminate\Http\Request;

class VnPayController extends Controller
{
    //


    public function vnpay_payment(Request $request)
    {

      
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        // Assuming $request is a valid request object
        $data = $request->all();
        $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
        $vnp_Returnurl = "https://localhost/vnpay_php/vnpay_return.php";
        $vnp_TmnCode = "60Z1TD8C"; // Mã website tại VNPAY 
        $vnp_HashSecret = "LXS0PA1SWMYQZ2G2JM5CPHRYX1RS0MLD"; // Chuỗi bí mật

        // Define constants
        $vnp_TxnRef = "123";
        $vnp_OrderInfo = "DEVELOP SHOP";
        $vnp_OrderType = "Ahihi";
        $vnp_Amount = $data['total'] * 100; // Convert to cents
        $vnp_Locale = "VN";
        $vnp_BankCode = "NCB";
        $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];




        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef,

        );

        if (isset($vnp_BankCode) && $vnp_BankCode != "") {
            $inputData['vnp_BankCode'] = $vnp_BankCode;
        }

        // Sort parameters
        ksort($inputData);

        // Generate hash data
        $hashdata = "";
        $query = "";
        $i = 0;
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        // Final URL
        $vnp_Url = $vnp_Url . "?" . rtrim($query, '&');

        // Generate secure hash
        if (isset($vnp_HashSecret)) {
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
            $vnp_Url .= '&vnp_SecureHash=' . $vnpSecureHash;
        }

        // Return URL or redirect
        $returnData = array(
            'code' => '00',
            'message' => 'success',
            'data' => $vnp_Url
        );

        if (isset($_POST['total'])) {
            header('Location: ' . $vnp_Url);
            exit();
        } else {
            echo json_encode($returnData);
        }
    }
}
