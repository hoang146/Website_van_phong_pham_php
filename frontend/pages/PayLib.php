<?php
class PayLib
{
    private $requestData = [];
    private $responseData = [];

    // ------------------------ REQUEST DATA ------------------------
    public function AddRequestData($key, $value)
    {
        if (!empty($value)) {
            $this->requestData[$key] = $value;
        }
    }

    public function CreateRequestUrl($baseUrl, $vnp_HashSecret)
    {
        ksort($this->requestData); // sắp xếp theo key như SortedList<>

        $query = [];
        foreach ($this->requestData as $key => $value) {
            $query[] = urlencode($key) . "=" . urlencode($value);
        }
        $queryString = implode('&', $query);

        // Chuỗi để ký (sign)
        $signData = $queryString;

        // Tạo chữ ký
        $vnp_SecureHash = hash_hmac('sha512', $signData, $vnp_HashSecret);

        // Tạo URL đầy đủ
        $url = $baseUrl . "?" . $queryString . "&vnp_SecureHash=" . $vnp_SecureHash;

        return $url;
    }

    // ------------------------ RESPONSE DATA ------------------------
    public function AddResponseData($key, $value)
    {
        if (!empty($value)) {
            $this->responseData[$key] = $value;
        }
    }

    public function GetResponseData($key)
    {
        return $this->responseData[$key] ?? '';
    }

    private function BuildResponseDataString()
    {
        // Loại bỏ chữ ký cũ trước khi xác thực
        unset($this->responseData['vnp_SecureHashType']);
        unset($this->responseData['vnp_SecureHash']);

        ksort($this->responseData);

        $query = [];
        foreach ($this->responseData as $key => $value) {
            if (!empty($value)) {
                $query[] = urlencode($key) . "=" . urlencode($value);
            }
        }

        return implode('&', $query);
    }

    public function ValidateSignature($inputHash, $secretKey)
    {
        $signData = $this->BuildResponseDataString();
        $computedHash = hash_hmac('sha512', $signData, $secretKey);

        return strtoupper($computedHash) === strtoupper($inputHash);
    }
}
?>
