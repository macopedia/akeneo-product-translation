<?php

namespace Translator;

use InvalidArgumentException;
use Macopedia\Translator\Translator\Language;
use PHPUnit\Framework\TestCase;

final class LanguageTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     * @param string $code
     * @param bool $correct
     */
    public function testCreateCorrectLanguage(string $code, bool $correct): void
    {
        if (!$correct) {
            $this->expectException(InvalidArgumentException::class);
        }

        Language::fromCode($code);

        if ($correct) {
            $this->addToAssertionCount(1);
        }
    }

    /**
     * @return array<int, array<int, string|bool>>
     */
    public function dataProvider(): array
    {
        return [
            ['sg', true],
            ['sa', true],
            ['sc', true],
            ['sr', true],
            ['sn', true],
            ['SN', false],
            ['SR', false],
            ['gb', false],
            ['Pl', false],
            ['pln', false],
            ['pl_PL', false],
        ];
    }
}
