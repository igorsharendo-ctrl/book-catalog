<?php

namespace app\components;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\helpers\Json;

/**
 * Клиент HTTP API SMSPilot (https://smspilot.ru/).
 *
 * Для проверки используется ключ-эмулятор: шлюз принимает запрос,
 * но реальной отправки SMS оператору не происходит.
 */
class SmsPilot extends Component
{
    /**
     * @var string API-ключ личного кабинета или ключ-эмулятор
     */
    public $apiKey;

    /**
     * @var string Точка входа API-1
     */
    public $apiUrl = 'https://smspilot.ru/api.php';

    /**
     * @var int 1 — эмулятор без передачи оператору (см. параметр test в API-1)
     */
    public $testMode = 1;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        if ($this->apiKey === null || $this->apiKey === '') {
            throw new InvalidConfigException('Не задан apiKey компонента SmsPilot.');
        }
    }

    /**
     * Отправляет текстовое SMS на номер в международном формате без знака «+».
     *
     * @param string $phone номер вида 79XXXXXXXXX
     * @param string $text текст сообщения (UTF-8)
     * @return bool true, если шлюз принял сообщение без error-кода
     */
    public function send(string $phone, string $text): bool
    {
        $query = http_build_query([
            'send' => $text,
            'to' => $phone,
            'apikey' => $this->apiKey,
            'format' => 'json',
            'charset' => 'utf-8',
            'test' => (int) $this->testMode,
        ], '', '&', PHP_QUERY_RFC3986);

        $url = $this->apiUrl . '?' . $query;
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 15,
                'ignore_errors' => true,
            ],
        ]);

        $raw = @file_get_contents($url, false, $context);
        if ($raw === false) {
            Yii::error('Не удалось выполнить запрос к SMSPilot.', 'sms');
            return false;
        }

        Yii::info('SMSPilot response: ' . $raw, 'sms');

        try {
            $payload = Json::decode($raw);
        } catch (\Throwable $exception) {
            Yii::error('SMSPilot вернул не JSON.', 'sms');
            return false;
        }

        if (isset($payload['error'])) {
            $message = $payload['error']['description_ru'] ?? $payload['error']['description'] ?? 'unknown';
            Yii::error('Ошибка SMSPilot: ' . $message, 'sms');
            return false;
        }

        return isset($payload['send']);
    }
}
