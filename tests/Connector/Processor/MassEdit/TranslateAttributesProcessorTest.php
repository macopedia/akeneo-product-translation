<?php

namespace Connector\Processor\MassEdit;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Piotrmus\Translator\Connector\Processor\MassEdit\TranslateAttributesProcessor;
use PHPUnit\Framework\TestCase;
use Piotrmus\Translator\Translator\TranslatorInterface;

class TranslateAttributesProcessorTest extends TestCase
{
    public function testCreateProcessor(): void
    {
        $translatorMock = $this->getMockBuilder(TranslatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $translatorMock->expects(self::once())
            ->method('translate')
            ->with(
                self::equalTo('Product name')
            )
            ->willReturn('Nazwa produktu');

        $processor = new TranslateAttributesProcessor($translatorMock);

        $productMock = $this->getMockBuilder(ProductInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

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

        $productMock->expects(self::at(0))
            ->method('getValue')
            ->willReturn(
                ScalarValue::scopableLocalizableValue(
                    'name',
                    'Product name',
                    'ecommerce',
                    'en_US',
                )
            );

        $productMock->expects(self::once())
            ->method('addValue')
            ->with(
                self::callback(
                    static function ($argument) {
                        if (!$argument instanceof ScalarValue) {
                            return false;
                        }

                        return $argument->isEqual(
                            ScalarValue::scopableLocalizableValue(
                                'name',
                                'Nazwa produktu',
                                'print',
                                'pl_PL',
                            )
                        );
                    }
                )
            );

        $processor->process($productMock);
    }
}
