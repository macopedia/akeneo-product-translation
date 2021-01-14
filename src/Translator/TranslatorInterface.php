<?php


namespace Codehat\TranslationExtension\Translator;


interface TranslatorInterface
{
    /**
     * @param string $text
     * @param Language $originalLanguageCode
     * @param Language $targetLanguageCode
     * @return string
     */
    public function translate(string $text, Language $originalLanguageCode, Language $targetLanguageCode): string;
}