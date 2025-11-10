<?php

namespace losthost\YandexAI;

class YandexARTGateway extends YandexAbstractGateway {
    
    const URL_GENERATE = 'https://llm.api.cloud.yandex.net/foundationModels/v1/imageGenerationAsync';
    const URL_OPERATION_GET = 'https://operation.api.cloud.yandex.net/operations/';
    
    const MIME_JPEG = 'image/jpeg';
    const MIME_PNG = 'image/png';
    
    const GENERATION_TIMEOUT = 300;
    const GENERATION_SLEEP_TIME = 10;
    
    protected string $folder_id;
    protected string $model_uri;
    
    public function __construct(string $folder_id) {
        parent::__construct($folder_id);
        $this->model_uri = "art://{$folder_id}/yandex-art/latest";
    }
    
    public function generate(string|array $prompt, int $width_ratio=1, int $height_ratio=1, string $mime_type=self::MIME_JPEG, int $seed=0) : string {
        $operation = $this->generateAsync($prompt, $width_ratio, $height_ratio, $mime_type, $seed);
        $operation_id = $operation->id;
    
        $timeout = static::GENERATION_TIMEOUT;
        while (true) {
            $operation = $this->operationGet($operation_id);
            if ($operation->error) {
                throw new \Exception($operation->error->description);
            } elseif ($operation->done) {
                $result = base64_decode($operation->response->image);
                return $result;
            } else {
                sleep(static::GENERATION_SLEEP_TIME);
                $timeout -= static::GENERATION_SLEEP_TIME;
                if ($timeout < 0) {
                    throw new \Exception("Image generation timeout exceeded");
                }
            }
        }
    }
    
    public function generateAsync(string|array $prompt, int $width_ratio=1, int $height_ratio=1, string $mime_type=self::MIME_JPEG, int $seed=0) : \stdClass {
    
        if (!is_array($prompt)) {
            $prompt = [$prompt];
        }
        
        $this->checkNormalizePromptArray($prompt);
        
        return $this->generateFromArray($prompt, $width_ratio, $height_ratio, $mime_type, $seed);

    }
    
    public function operationGet(string $operation_id) : \stdClass {
        
        $token = new IAMToken();
        $headers = [
            "Authorization: Bearer ". $token->get(),
            "Content-Type: application/json",
        ];
        
        $result = $this->get(static::URL_OPERATION_GET. $operation_id, [], $headers);

        if ($result['http_code'] != 200 && $result['error']) {
            $error_message = "$result[http_code] - $result[error]";
            throw new \Exception($error_message);
        }
        
        return json_decode($result['response']);
        
    }
    
    protected function checkNormalizePromptArray(array &$prompt) : void {
        
        foreach($prompt as $key => $value) {
            if (!is_array($value)) {
                $prompt[$key] = [
                    'text' => $value,
                    'weight' => 1
                ];
            }
        }
    }
    
    protected function generateFromArray(array $prompt, int $width_ratio, int $height_ratio, string $mime_type, int $seed) : \stdClass {
    
        $data = [
            'modelUri' => $this->model_uri,
            'messages' => $prompt,
            'generationOptions' => [
                'mimeType' => $mime_type,
                'seed' => (string)$seed,
                'aspectRatio' => [
                    'widthRatio' => (string)$width_ratio,
                    'heightRatio' => (string)$height_ratio
                ]
            ]
        ];
        
        $token = new IAMToken();
        $headers = [
            "Authorization: Bearer ". $token->get(),
            "Content-Type: application/json",
        ];
        
        $result = $this->postJson(static::URL_GENERATE, $data, $headers);
        
        if ($result['http_code'] != 200 && $result['error']) {
            $error_message = "$result[http_code] - $result[error]";
            throw new \Exception($error_message);
        }
        
        return json_decode($result['response']);
    }
    
}
