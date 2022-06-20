<?php

namespace Connector\Processor\MassEdit;

use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\CheckAttributeEditable;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertySetterInterface;
use Exception;
use Macopedia\Translator\Connector\Processor\MassEdit\TranslateAttributesProcessor;
use PHPUnit\Framework\TestCase;
use Macopedia\Translator\Translator\TranslatorInterface;

final class TranslateAttributesProcessorTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testCreateProcessor(): void
    {
        $translatorMock = $this->getMockBuilder(TranslatorInterface::class)->getMock();
        $attributeRepositoryMock = $this->getMockBuilder(AttributeRepositoryInterface::class)->getMock();
        $checkAttributeEditableMock = $this->getMockBuilder(CheckAttributeEditable::class)->getMock();
        $propertySetterInterfaceMock = $this->getMockBuilder(PropertySetterInterface::class)->getMock();

        $attributeMock = $this->getMockBuilder(AttributeInterface::class)->getMock();
        $attributeMock->method('isScopable')->willReturn(true);

        $attributeRepositoryMock->method('findOneByIdentifier')->willReturn(
            $attributeMock
        );

        $translatorMock->expects(self::once())
            ->method('translate')
            ->with(
                self::equalTo('Product name')
            )
            ->willReturn('Nazwa produktu');

        $checkAttributeEditableMock->method('isEditable')->willReturn(true);

        $processor = new TranslateAttributesProcessor(
            $translatorMock,
            $attributeRepositoryMock,
            $checkAttributeEditableMock,
            $propertySetterInterfaceMock
        );

        $productMock = $this->getMockBuilder(ProductInterface::class)->getMock();

        $stepExecutionMock = $this->getMockBuilder(StepExecution::class)
            ->disableOriginalConstructor()
            ->getMock();

        $jobParametersMock = $this->getMockBuilder(JobParameters::class)
            ->disableOriginalConstructor()
            ->getMock();

        $stepExecutionMock->method('getJobParameters')->willReturn($jobParametersMock);

        $jobParametersMock->method('get')
            ->with(self::equalTo('actions'))
            ->willReturn(
                [
                    [
                        'sourceChannel' => 'ecommerce',
                        'targetChannel' => 'print',
                        'sourceLocale' => 'en_US',
                        'targetLocale' => 'pl_PL',
                        'translatedAttributes' => ['name'],
                    ]
                ]
            );

        $processor->setStepExecution($stepExecutionMock);

        $productMock
            ->method('getValue')
            ->with(
                self::equalTo('name'),
                self::equalTo('en_US'),
                self::equalTo('ecommerce')
            )
            ->willReturn(
                ScalarValue::scopableLocalizableValue(
                    'name',
                    'Product name',
                    'ecommerce',
                    'en_US',
                )
            );

        $propertySetterInterfaceMock->expects(self::once())
            ->method('setData')
            ->with(
                self::equalTo($productMock),
                self::equalTo('name'),
                self::equalTo('Nazwa produktu'),
                self::equalTo(
                    [
                        'locale' => 'pl_PL',
                        'scope' => 'print'
                    ]
                )
            );

        $processor->process($productMock);
    }
}
