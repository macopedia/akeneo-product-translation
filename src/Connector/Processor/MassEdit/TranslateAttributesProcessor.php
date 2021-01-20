<?php


namespace Piotrmus\Translator\Connector\Processor\MassEdit;


use Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\MassEdit\AbstractProcessor;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
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
     * @param ProductInterface|ProductModelInterface $product
     * @return ProductInterface
     */
    public function process($product): ProductInterface
    {
        $actions = $this->getConfiguredActions();
        $action = $actions[0];
        $product = $this->translateAttributes($product, $action);
        return $product;
    }

    /**
     * @param ProductInterface|ProductModelInterface $product
     * @param array $action
     */
    private function translateAttributes($product, array $action): ProductInterface
    {
        $sourceScope = $action['sourceChannel'];
        $targetScope = $action['targetChannel'];
        $sourceLocaleAkeneo = $action['sourceLocale'];
        $targetLocaleAkeneo = $action['targetLocale'];
        $sourceLocale = new Language(explode('_', $sourceLocaleAkeneo)[0]);
        $targetLocale = new Language(explode('_', $targetLocaleAkeneo)[0]);
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
     * @param $targetLocale
     * @param $targetScope
     * @param string $newValue
     */
    private function replaceProductValue(
        ProductInterface $product,
        string $attributeCode,
        $targetLocale,
        $targetScope,
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