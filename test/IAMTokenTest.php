<?php

namespace losthost\YandexAI\Test;

use PHPUnit\Framework\TestCase;

use losthost\YandexAI\IAMToken;

class IAMTokenTest extends TestCase {
    
    public function testNoFile() {
        
        $file_name = './authkey.json';
        unlink($file_name);

        $this->expectExceptionMessageMatches("/Failed to read/");
        $iam = new IAMToken('./authkey.json');
        $iam->get();
        
    }
    
    public function testWrongFileFormat() {
        
        $file_name = './authkey.json';
        file_put_contents($file_name, 'some dummy data');
        
        $this->expectExceptionMessageMatches("/Failed to decode/");
        $iam = new IAMToken('./authkey.json');
        $iam->get();
        
        unlink($file_name);
    }
    
    public function testAllOk() {
        
        $key_file_name = "../etc/authorized_key.json";
        $token_file_name = "./token.json";
        
        $iam = new IAMToken($key_file_name, $token_file_name);
        
        $this->assertMatchesRegularExpression("/^t1./", $iam->get());
    }
    
}
