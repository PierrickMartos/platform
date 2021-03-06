<?php

namespace Oro\Bundle\EntityExtendBundle\Tests\Unit\Form\EventListener;

use Oro\Bundle\EntityExtendBundle\Form\Extension\DynamicFieldsOptionsExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DynamicFieldsOptionsExtensionTest extends \PHPUnit\Framework\TestCase
{
    public function testConfigureOptions(): void
    {
        $optionsResolver = new OptionsResolver();
        $extension = new DynamicFieldsOptionsExtension();
        $extension->configureOptions($optionsResolver);

        $this->assertEquals(['dynamic_fields_disabled' => false], $optionsResolver->resolve());
    }

    public function testGetExtendedTypes(): void
    {
        $this->assertEquals([FormType::class], DynamicFieldsOptionsExtension::getExtendedTypes());
    }
}
