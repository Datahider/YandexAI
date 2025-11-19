<?php

namespace losthost\YandexAI\Test;

use PHPUnit\Framework\TestCase;
use losthost\YandexAI\YandexSpeachKitGateway;
use losthost\YandexAI\Operation;

class YandexSpeachKitGatewayTest extends TestCase {

    public function testSTT() {

        global $folder_id;

        $stt = new YandexSpeachKitGateway($folder_id);
        $text = $stt->recognize(file_get_contents('12345.ogg'));
        $this->assertEquals('1 2 3 4 5 вышел зайчик погулять', $text);

    }
    
    public function testSSTAsync() {
        
        global $folder_id;
        
        $sst = new YandexSpeachKitGateway($folder_id);
        $operation = $sst->recognizeAsync(file_get_contents('12345.ogg'));
        var_dump($operation);
        
        $this->asertEquals('123', $operation);
    }
    
}
