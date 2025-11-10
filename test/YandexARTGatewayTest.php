<?php

namespace losthost\YandexAI\Test;

use PHPUnit\Framework\TestCase;

use losthost\YandexAI\YandexARTGateway;

class YandexARTGatewayTest extends TestCase {
    
    public function testImageGeneration() {
        
        global $folder_id;
        
        $art = new YandexARTGateway($folder_id);
        $image = $art->generate("Нарисуй красную звезду");
        
        file_put_contents('image.jpg', $image);
        
        $this->assertStringContainsString("\xFF\xD8\xFF", $image);
    }
}
