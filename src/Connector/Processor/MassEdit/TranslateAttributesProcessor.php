<?php


namespace Macopedia\Translator\Connector\Processor\MassEdit;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\MassEdit\AbstractProcessor;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\CheckAttributeEditable;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertySetterInterface;
use Exception;
use InvalidArgumentException;
use Macopedia\Translator\Translator\Language;
use Macopedia\Translator\Translator\TranslatorInterface;

final class TranslateAttributesProcessor extends AbstractProcessor
{
    /**
     * @var TranslatorInterface
     */
    private $translator;
    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;
    /**
     * @var CheckAttributeEditable
     */
    private $checkAttributeEditable;
    /**
     * @var PropertySetterInterface
     */
    private $propertySetter;

    public function __construct(
        TranslatorInterface $translator,
        AttributeRepositoryInterface $attributeRepository,
        CheckAttributeEditable $checkAttributeEditable,
        PropertySetterInterface $propertySetter
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->translator = $translator;
        $this->checkAttributeEditable = $checkAttributeEditable;
        $this->propertySetter = $propertySetter;
    }

    /**
     * @param mixed $item
     * @return ProductInterface|ProductModelInterface
     * @throws Exception
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
     *  $actions = [
     *      'sourceChannel' => 'ecommerce',
     *      'targetChannel' => 'ecommerce',
     *      'sourceLocale' => 'pl_PL',
     *      'targetLocale' => 'en_US',
     *      'translatedAttributes' => [
     *          'name',
     *          'description',
     *     ]
     *  ];
     * @return ProductInterface|ProductModelInterface
     * @throws Exception
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
            /** @var AttributeInterface|null $attribute */
            $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);
            if (!$this->checkAttributeEditable->isEditable($product, $attribute)) {
                continue;
            }

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

            $translatedText = $this->translator->translate(
                $sourceText,
                $sourceLocale,
                $targetLocale
            );

            $this->propertySetter->setData($product, $attributeCode, $translatedText, [
                'locale' => $targetLocaleAkeneo,
                'scope' => $targetScope,
            ]);
        }

        return $product;
    }
}
