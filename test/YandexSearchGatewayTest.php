<?php

namespace losthost\YandexAI\Test;

use PHPUnit\Framework\TestCase;
use losthost\YandexAI\YandexSearchGateway;

class YandexSearchGatewayTest extends TestCase
{
    private $folderId;
    private $gateway;

    protected function setUp(): void
    {
        global $folder_id;
        $this->folderId = $folder_id;

        $this->gateway = new YandexSearchGateway($this->folderId);
    }

    public function testSearchRealRequest(): void
    {
        $query = 'Что такое искусственный интеллект?';

        $result = $this->gateway->search($query);

        // Проверяем, что получили не ошибку
        $this->assertNotEquals('Не удалось получить результат поиска из за ошибки связи.', $result);
        $this->assertStringNotContainsString('При обращении к поисковому серверу произошла ошибка', $result);

        // Проверяем, что результат не пустой
        $this->assertNotEmpty($result);
        $this->assertIsString($result);

        // Можно проверить, что результат содержит что-то осмысленное
        // (но это зависит от API Яндекса)
        echo "\nРезультат поиска: " . substr($result, 0, 100) . "...\n";
    }

    public function testSearchWithEmptyQuery(): void
    {
        $result = $this->gateway->search('');

        // Пустой запрос может вернуть ошибку или пустой результат
        // Зависит от API
        $this->assertIsString($result);
    }

    public function testSearchWithSpecialCharacters(): void
    {
        $query = 'PHP & JavaScript: сравнение языков программирования';

        $result = $this->gateway->search($query);

        $this->assertNotEquals('Не удалось получить результат поиска из за ошибки связи.', $result);
        $this->assertStringNotContainsString('При обращении к поисковому серверу произошла ошибка', $result);
        $this->assertNotEmpty($result);
    }

    public function testSearchLongQuery(): void
    {
        $query = str_repeat('тест ', 50); // 250 символов

        $result = $this->gateway->search($query);

        $this->assertNotEquals('Не удалось получить результат поиска из за ошибки связи.', $result);
        $this->assertStringNotContainsString('При обращении к поисковому серверу произошла ошибка', $result);
        $this->assertIsString($result);
    }
}
