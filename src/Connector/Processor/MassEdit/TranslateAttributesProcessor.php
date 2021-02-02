<?php


namespace Piotrmus\Translator\Connector\Processor\MassEdit;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\MassEdit\AbstractProcessor;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use InvalidArgumentException;
use Piotrmus\Translator\Translator\Language;
use Piotrmus\Translator\Translator\TranslatorInterface;

class TranslateAttributesProcessor extends AbstractProcessor
{
    /**
     * @var TranslatorInterface
     */
    private $translator;
    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    public function __construct(
        TranslatorInterface $translator,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->attributeRepository = $attributeRepository;
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
            $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);
            if (!$attribute->isScopable()) {
                $sourceScope = null;
                $targetScope = null;
            }
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

            $this->replaceProductValue($product, $attribute, $targetLocaleAkeneo, $targetScope, $translatedValue);
        }
        return $product;
    }

    /**
     * @param ProductInterface $product
     * @param AttributeInterface $attribute
     * @param string $targetLocale
     * @param string|null $targetScope
     * @param string $newValue
     */
    private function replaceProductValue(
        ProductInterface $product,
        AttributeInterface $attribute,
        string $targetLocale,
        ?string $targetScope,
        string $newValue
    ): void {
        $targetAttributeValue = $product->getValue($attribute->getCode(), $targetLocale, $targetScope);

        $isScopable = $attribute->isScopable();
        $isLocalizable = $attribute->isLocalizable();

        if ($isScopable && $isLocalizable) {
            $value = ScalarValue::scopableLocalizableValue(
                $attribute->getCode(),
                $newValue,
                $targetScope,
                $targetLocale
            );
        } elseif ($isScopable) {
            $value = ScalarValue::scopableValue($attribute->getCode(), $newValue, $targetScope);
        } elseif ($isLocalizable) {
            $value = ScalarValue::localizableValue($attribute->getCode(), $newValue, $targetLocale);
        } else {
            $value = ScalarValue::value($attribute->getCode(), $newValue);
        }

        if ($targetAttributeValue !== null) {
            $product->removeValue($targetAttributeValue);
        }

        $product->addValue($value);
    }
}
