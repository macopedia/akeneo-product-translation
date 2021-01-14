<?php


namespace Codehat\TranslationExtension\Connector\Processor\MassEdit;


use Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\MassEdit\AbstractProcessor;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertySetterInterface;
use Codehat\TranslationExtension\Translator\Language;
use Codehat\TranslationExtension\Translator\TranslatorInterface;

class TranslateAttributesProcessor extends AbstractProcessor
{
    /**
     * @var TranslatorInterface
     */
    private $translator;
    /**
     * @var PropertySetterInterface
     */
    private $propertySetter;

    public function __construct(
        TranslatorInterface $translator,
        PropertySetterInterface $propertySetter
    ) {
        $this->translator = $translator;
        $this->propertySetter = $propertySetter;
    }

    /**
     * @param ProductInterface|ProductModelInterface $item
     * @return void
     */
    public function process($item): void
    {
        $actions = $this->getConfiguredActions();
        $action = $actions[0];
        $this->translateAttributes($item, $action);
    }

    /**
     * @param ProductInterface|ProductModelInterface $product
     * @param array $action
     */
    private function translateAttributes($product, array $action): void
    {
        $sourceScope = $action['sourceScope'];
        $targetScope = $action['targetScope'];
        $sourceLocale = new Language($action['sourceLocale']);
        $targetLocale = new Language($action['targetLocale']);
        $attributeCodes = $action['attributes'];

        foreach ($attributeCodes as $attributeCode) {
            /** @var ValueInterface|null $attributeValue */
            $attributeValue = $product->getValue($attributeCode, $sourceLocale, $sourceScope);

            if ($attributeValue === null) {
                continue;
            }

            $sourceText = $attributeValue->getData();

            $translatedValue = $this->translator->translate(
                $sourceText,
                $sourceLocale,
                $targetLocale
            );
            
            $this->propertySetter->setData(
                $product,
                $attributeCode,
                $translatedValue,
                [
                    'locale' => $targetLocale,
                    'scope' => $targetScope,
                ]
            );
        }
    }
}