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
    
    public function recognizeAsync(string $bytes) : Operation {
        
        $request = [
            'content' => base64_encode($bytes),
            'recognitionModel' => [
                'model' => 'general',
                'audioFormat' => [
                    'containerAudio' => [
                        'containerAudioType' => 'OGG_OPUS'
                    ]
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
        
        return Operation::fromResponse($this->post($url, $request, $headers));
    }
    
    public function wait(Operation $operation) {
        
    }
}
