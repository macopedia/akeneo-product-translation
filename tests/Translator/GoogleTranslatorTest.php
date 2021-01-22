<?php

namespace Translator;

use Google\Cloud\Translate\V2\TranslateClient;
use PHPUnit\Framework\MockObject\MockObject;
use Piotrmus\Translator\Translator\GoogleTranslator;
use PHPUnit\Framework\TestCase;
use Piotrmus\Translator\Translator\Language;

class GoogleTranslatorTest extends TestCase
{
    public function testTranslate(): void
    {
        /** @var TranslateClient|MockObject $translateClientMock */
        $translateClientMock = $this->getMockBuilder(TranslateClient::class)
            ->disableOriginalConstructor()->getMock();

        $googleTranslator = new GoogleTranslator($translateClientMock);

        $translateClientMock->expects(self::once())
            ->method('translate')
            ->with(
                self::isType('string'),
                self::equalTo(
                    [
                        'source' => 'en',
                        'target' => 'pl'
                    ]
                )
            )->willReturn(
                [
                    'source' => 'en',
                    'input' => 'example translated text',
                    'text' => 'przykładowy przetłumaczony tekst'
                ]
            );

        $googleTranslator->translate(
            'example translated text',
            Language::fromCode('en'),
            Language::fromCode('pl')
        );
    }
}
