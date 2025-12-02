<?php

namespace losthost\YandexAI;

class YandexSpeachKitGateway extends YandexAbstractGateway {
 
    public function recognize(string $bytes) : string {
        
        $token = new IAMToken();
        $headers = [
            "Content-Type: application/json",
            "Authorization: Bearer ". $token->get(),
            "x-folder-id: $this->folder_id",
            "x-data-logging-enabled: true",
        ];
        $url = "https://stt.api.cloud.yandex.net/speech/v1/stt:recognize?lang=ru-RU&folderId={$this->folder_id}&format=oggopus";
        
        $response = $this->post($url, $bytes, $headers);
        $result = json_decode($response["response"]);
        return $result->result;
    }
    
    public function recognizeAsync(string $bytes) : ?Operation {
        
        $request = [
            'content' => base64_encode($bytes),
            'recognitionModel' => [
                'model' => 'general',
                'audioFormat' => [
                    'containerAudio' => [
                        'containerAudioType' => 'OGG_OPUS'
                    ],
                ]
            ]
        ];
        
        $token = new IAMToken();
        $headers = [
            "Content-Type: application/json",
            "Authorization: Bearer ". $token->get(),
            "x-folder-id: $this->folder_id",
            "x-data-logging-enabled: true",
        ];
        $url = 'https://stt.api.cloud.yandex.net/stt/v3/recognizeFileAsync';
        
        $result = $this->postJson($url, $request, $headers);
        if (!$result["error"]) {
            return Operation::fromResponse($result["response"]);
        }
        return null;
    }

    public function recognizeSync(string $bytes, bool $refine=true) : string {
        
        $operation = $this->recognizeAsync($bytes);
        $operation->wait();
        return $this->getRecognitionResult($operation, $refine);
        
    }
    
    public function getRecognitionResult(Operation $operation, bool $refine=true) : string {
        
        $url = 'https://stt.api.cloud.yandex.net/stt/v3/getRecognition';
        
        $token = new IAMToken();
        $headers = [
            "Content-Type: application/json",
            "Authorization: Bearer ". $token->get(),
            "x-folder-id: $this->folder_id",
            "x-data-logging-enabled: true",
        ];

        $result = $this->get($url, ['operationId' => $operation->getId()], $headers);
        
        if (!$result['error']) {
            $lines = array_filter(explode("\n", $result['response']));
            
            $result_array = [];
            foreach ($lines as $line) {
                $response = json_decode($line);
                if ($refine && isset($response->result->finalRefinement)) {
                    $result_array[] = $response->result->finalRefinement->normalizedText->alternatives[0]->text;
                } elseif (!$refine && isset($response->result->final)) {
                    $result_array[] = $response->result->final->alternatives[0]->text;
                }
            }
            
            return implode("\n\n", $result_array);
        }
        
        return null;
    }
}
