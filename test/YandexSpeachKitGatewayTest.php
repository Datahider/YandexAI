<?php

namespace losthost\YandexAI\Test;

use PHPUnit\Framework\TestCase;
use losthost\YandexAI\YandexSpeachKitGateway;

class YandexSpeachKitGatewayTest extends TestCase {

    public function testSTT() {

        global $folder_id;

        $stt = new YandexSpeachKitGateway($folder_id);
        $text = $stt->recognize(file_get_contents('12345.ogg'));
        $this->assertEquals('1 2 3 4 5 вышел зайчик погулять', $text);

    }
    
    public function testSTTAsync() {
        
        global $folder_id;
        
        $sst = new YandexSpeachKitGateway($folder_id);
        $operation = $sst->recognizeAsync(file_get_contents('12345.ogg'));
        $operation->wait();
        
        $text = $sst->getRecognitionResult($operation);
        $this->assertEquals('1 2 3 4 5 вышел зайчик погулять', $text);
    }
    
    public function testSTTSync() {
        
        global $folder_id;
        
        $sst = new YandexSpeachKitGateway($folder_id);
        $text = $sst->recognizeSync(file_get_contents('12345.ogg'), false);
        $this->assertEquals('раз два три четыре пять вышел зайчик погулять', $text);
    }
    
}
