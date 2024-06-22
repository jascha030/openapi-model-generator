<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\Serializer\NameConverter\AdvancedNameConverterInterface;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

return static function (ContainerBuilder $builder): ContainerBuilder {
    $builder->addDefinitions([
        'serializer.normalizers' => static function (ContainerInterface $container): array {
            return [
                $container->get(AbstractObjectNormalizer::class),
                new ArrayDenormalizer(),
            ];
        },
        'serializer.encoders' => static function (): array {
            return [
                new JsonEncoder(),
            ];
        },
        ClassMetadataFactoryInterface::class => static function (): ClassMetadataFactory {
            return new ClassMetadataFactory(new AttributeLoader());
        },
        AdvancedNameConverterInterface::class => static function (ContainerInterface $container): MetadataAwareNameConverter {
            return new MetadataAwareNameConverter($container->get(ClassMetadataFactoryInterface::class));
        },
        PropertyInfoExtractorInterface::class => static function (): PropertyInfoExtractor {
            return new PropertyInfoExtractor(
                typeExtractors: [
                    new PhpDocExtractor(),
                    new ReflectionExtractor(),
                ],
            );
        },
        AbstractObjectNormalizer::class => static function (ContainerInterface $container): ObjectNormalizer {
            return new ObjectNormalizer(
                classMetadataFactory: $container->get(ClassMetadataFactoryInterface::class),
                nameConverter: $container->get(AdvancedNameConverterInterface::class),
                propertyTypeExtractor: $container->get(PropertyInfoExtractorInterface::class),
            );
        },
        Serializer::class => static function (ContainerInterface $container): Serializer {
            /**
             * @var \Symfony\Component\Serializer\Normalizer\NormalizerInterface[] $normalizers
             */
            $normalizers = $container->get('serializer.normalizers');
            /**
             * @var \Symfony\Component\Serializer\Encoder\EncoderInterface[] $encoders
             */
            $encoders    = $container->get('serializer.encoders');

            return new Serializer($normalizers, $encoders);
        },
    ]);

    return $builder;
};
