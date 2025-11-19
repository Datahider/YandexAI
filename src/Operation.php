<?php

namespace losthost\YandexAI;

class Operation {

    protected \stdClass $data;
    protected ?OperationError $error = null;

    public function __construct(array|string|stdClass $input) {
        if (is_string($input)) {
            $decoded = json_decode($input);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new InvalidArgumentException("Invalid JSON string");
            }
            $this->data = $decoded;
        } elseif (is_array($input)) {
            $this->data = json_decode(json_encode($input));
        } elseif ($input instanceof \stdClass) {
            $this->data = $input;
        } else {
            throw new InvalidArgumentException("Unsupported input type");
        }

        // Автоматическое создание вложенного объекта error
        if (isset($this->data->error)) {
            $this->error = new OperationError($this->data->error);
        }
    }

    public static function fromResponse(array|string|stdClass $input): self {
        return new self($input);
    }

    public function __get(string $name) {
        return $this->data->$name ?? null;
    }
    
    public function getId() : string {
        return $this->data->id;
    }
    
    public function getError() : OperationError {
        return $this->error;
    }
    
    public function getResponse() {
        return $this->data->response;
    }
    public function isDone() : bool {
        return $this->data->done;
    }

    public function wait(int $timeout=0) {
    
        $waiting = 0;
        while ($this->isDone() === false) {
            $this->renew();
            if ($timeout && $waiting>=$timeout) {
                return false;
            }
            $waiting++;
            sleep(2);
        }
        
        return true;

    }
    
    public function renew() {

        $url = 'https://operation.api.cloud.yandex.net/operations/'. $this->getId();

        $token = new IAMToken();
        $headers = [
            "Content-Type: application/json",
            "Authorization: Bearer ". $token->get(),
        ];
        
        $result = $this->get($url, [], $headers);
        
        if (!$result['error']) {
            $decoded = json_decode($result['response']);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new InvalidArgumentException("Invalid JSON string");
            }
            $this->data = $decoded;
        }
    }
    
    protected function get($url, $params=[], $headers=[]) {

        // Добавляем параметры к URL, если они есть
        if (!empty($params)) {
            $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($params);
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CAINFO, __DIR__. '/cacert.pem');

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        return [
            'response' => $response,
            'http_code' => $httpCode,
            'error' => $error
        ];
    }
    
}
