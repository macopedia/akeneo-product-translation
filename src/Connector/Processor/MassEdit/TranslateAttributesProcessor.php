<?php


namespace Piotrmus\Translator\Connector\Processor\MassEdit;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\MassEdit\AbstractProcessor;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use InvalidArgumentException;
use Piotrmus\Translator\Translator\Language;
use Piotrmus\Translator\Translator\TranslatorInterface;

class TranslateAttributesProcessor extends AbstractProcessor
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(
        TranslatorInterface $translator
    ) {
        $this->translator = $translator;
    }

    /**
     * @param mixed $item
     * @return ProductInterface|ProductModelInterface
     */
    public function process($item)
    {
        if (!$item instanceof ProductInterface && !$item instanceof ProductModelInterface) {
            throw new InvalidArgumentException("Invalid $item type to this processor");
        }
        $actions = $this->getConfiguredActions();
        $action = $actions[0];
        $item = $this->translateAttributes($item, $action);

        return $item;
    }

    /**
     * @param ProductInterface|ProductModelInterface $product
     * @param array<string, string|array<int, string>> $action
     * @return ProductInterface|ProductModelInterface
     */
    private function translateAttributes($product, array $action)
    {
        $sourceScope = $action['sourceChannel'];
        $targetScope = $action['targetChannel'];
        $sourceLocaleAkeneo = $action['sourceLocale'];
        $targetLocaleAkeneo = $action['targetLocale'];
        $sourceLocale = Language::fromCode(explode('_', $sourceLocaleAkeneo)[0]);
        $targetLocale = Language::fromCode(explode('_', $targetLocaleAkeneo)[0]);
        $attributeCodes = $action['translatedAttributes'];

        foreach ($attributeCodes as $attributeCode) {
            /** @var ValueInterface|null $attributeValue */
            $attributeValue = $product->getValue($attributeCode, $sourceLocaleAkeneo, $sourceScope);

            if ($attributeValue === null) {
                continue;
            }

            $sourceText = $attributeValue->getData();

            $translatedValue = $this->translator->translate(
                $sourceText,
                $sourceLocale,
                $targetLocale
            );

            $this->replaceProductValue($product, $attributeCode, $targetLocaleAkeneo, $targetScope, $translatedValue);
        }
        return $product;
    }

    /**
     * @param ProductInterface $product
     * @param string $attributeCode
     * @param string $targetLocale
     * @param string $targetScope
     * @param string $newValue
     */
    private function replaceProductValue(
        ProductInterface $product,
        string $attributeCode,
        string $targetLocale,
        string $targetScope,
        string $newValue
    ): void {
        $targetAttributeValue = $product->getValue($attributeCode, $targetLocale, $targetScope);

        if ($targetAttributeValue !== null) {
            $product->removeValue($targetAttributeValue);
        }

        $product->addValue(
            ScalarValue::scopableLocalizableValue(
                $attributeCode,
                $newValue,
                $targetScope,
                $targetLocale
            )
        );
    }
}
