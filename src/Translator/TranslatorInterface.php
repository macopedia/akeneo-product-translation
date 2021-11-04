<?php


namespace Piotrmus\Translator\Translator;

interface TranslatorInterface
{
    public function translate(string $text, Language $originalLanguageCode, Language $targetLanguageCode): string;
}
