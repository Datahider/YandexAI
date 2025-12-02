<?php

namespace losthost\YandexAI;

use losthost\YandexAI\YandexAbstractGateway;

class YandexSearchGateway extends YandexAbstractGateway {
    
    const SEARCH_URL = 'https://searchapi.api.cloud.yandex.net/v2/gen/search';
    const ROLE_USER = 'ROLE_USER'; // strange but as in doc
    
    public function search(string $query) : string {
        
        $data = [
            'messages' => [
                'content' => $query,
                'role' => self::ROLE_USER
            ],
            'folderId' => $this->folder_id,
            'fixMisspell' => false,
            
        ];
        
        $token = new IAMToken();
        $headers = [
            "Content-Type: application/json",
            "Authorization: Bearer ". $token->get(),
            "x-folder-id: $this->folder_id",
            "x-data-logging-enabled: true",
        ];
        
        $result = $this->postJson(self::SEARCH_URL, $data, $headers);

        $decoded = json_decode($result['response'], true);  
        
        if ($result['response'] === false) {
            return "Не удалось получить результат поиска из за ошибки связи.";
        }
        
        if ($result['http_code'] !== 200) {
            return "При обращении к поисковому серверу произошла ошибка $result[http_code] - $decoded[message]";
        }
        
        return $decoded[0]['message']['content'];
        
    }
}
