<?php
class OtpKita
{

    private $url = "https://api.otpkita.com/api_handler.php";
    private $apiKey;

    public function __construct($api)
    {
        $this->apiKey = $api;
    }

    public function getBalance()
    {
        return $this->request(
            [
                "api_key" => $this->apiKey,
                "action" => "account_info"
            ],
            "GET",
            2
        );
    }

    public function orderOtp($operator, $service_id)
    {
        return $this->request(
            [
                "api_key" => $this->apiKey,
                "action" => "get_order",
                "operator_id" => $operator,
                "service_id" => $service_id
            ],
            "GET",
            1
        );
    }

    public function getOtp($id)
    {
        return $this->request(
            [
                "api_key" => $this->apiKey,
                "action" => "get_status",
                "order_id" => $id
            ],
            "GET",
            3
        );
    }

    public function setStatus($id, $status)
    {
        return $this->request(
            [
                "api_key" => $this->apiKey,
                "action" => "set_status",
                "order_id" => $id,
                "status" => $status
            ],
            "GET",
            4
        );
    }

    private function request(
        $data,
        $method,
        $getNumber = null
    ) {
        $method = strtoupper($method);

        if (!in_array($method, ["GET", "POST"])) {
            throw new InvalidArgumentException(
                "Method can only be GET or POST"
            );
        }

        $serializedData = http_build_query($data);

        if ($method === "GET") {
            $result = file_get_contents("$this->url?$serializedData");
        } else {
            $options = [
                "http" => [
                    "header" =>
                        "Content-type: application/x-www-form-urlencoded\r\n",
                    "method" => "POST",
                    "content" => $serializedData,
                ],
            ];

            $context = stream_context_create($options);
            $result = file_get_contents($this->url, false, $context);
        }
        $parsedResponse = json_decode($result, true);

        if ($getNumber == 1) {
            $returnNumber = [
                "status" => $parsedResponse["status"],
                "id" => $parsedResponse["data"]["order_id"],
                "number" => $parsedResponse["data"]["number"],
            ];
            return $returnNumber;
        }
        if ($getNumber == 2) {
            $returnStatus = [
                "status" => $parsedResponse["status"],
                "saldo" => $parsedResponse["data"]["saldo"],
            ];
            return $returnStatus;
        }
        if ($getNumber == 3) {
            $returnStatus = [
                "status" => $parsedResponse["status"],
                "sms" => $parsedResponse["data"]["sms"]
            ];
            return $returnStatus;
        }
        if ($getNumber == 4) {
            $returnStatus = ["status" => $parsedResponse["status"]];
            return $returnStatus;
        }

        return $parsedResponse;
    }
}
