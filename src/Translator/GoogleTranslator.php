<?php


namespace Piotrmus\Translator\Translator;


use Google\Cloud\Translate\V2\TranslateClient;

class GoogleTranslator implements TranslatorInterface
{
    /**
     * @var TranslateClient
     */
    private $client;

    public function __construct(
        string $apiKey
    ) {
        $this->client = new TranslateClient(
            [
                'key' => $apiKey
            ]
        );
    }

    public function translate(string $text, Language $originalLanguageCode, Language $targetLanguageCode): string
    {
        $result = $this->client->translate(
            $text,
            [
                'source' => $originalLanguageCode->asString(),
                'target' => $targetLanguageCode->asString()
            ]
        );

        return $result['text'];
    }
}