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
            $last = count($lines)-1;
            if ($refine) {
                $response = json_decode($lines[$last-1]);
                return $response->result->finalRefinement->normalizedText->alternatives[0]->text;
            } else {
                $response = json_decode($lines[$last-2]);
                return $response->result->final->alternatives[0]->text;
            }
        }
        
        return null;
    }
}
