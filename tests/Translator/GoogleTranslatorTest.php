<?php

namespace Translator;

use Codehat\TranslationExtension\Translator\GoogleTranslator;
use Codehat\TranslationExtension\Translator\Language;
use PHPUnit\Framework\TestCase;

class GoogleTranslatorTest extends TestCase
{

    public function testTranslate()
    {
        $translator = new GoogleTranslator('AIzaSyBnbLmtj_Ml5aqTaoZgrfHe4jt1zkQZrdg');
        $result = $translator->translate(
            "To rozszerzenie do Akeneo jest supper.",
            new Language("pl"),
            new Language("de")
        );

        dd($result);
    }
}
