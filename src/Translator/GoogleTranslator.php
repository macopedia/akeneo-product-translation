<?php


namespace Macopedia\Translator\Translator;

use Google\Cloud\Translate\V2\TranslateClient;

final class GoogleTranslator implements TranslatorInterface
{
    /**
     * @var TranslateClient
     */
    private $client;

    public function __construct(
        TranslateClient $client
    ) {
        $this->client = $client;
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
