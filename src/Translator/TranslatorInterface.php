<?php


namespace Macopedia\Translator\Translator;

interface TranslatorInterface
{
    public function translate(string $text, Language $originalLanguageCode, Language $targetLanguageCode): string;
}
