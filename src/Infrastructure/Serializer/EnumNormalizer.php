<?php
/**
 * DaData Client
 *
 * @author Vlad Shashkov <v.shashkov@pos-credit.ru>
 * @copyright Copyright (c) 2023, The Vanta
 */

declare(strict_types=1);

namespace Vanta\Integration\DaData\Infrastructure\Serializer;

use MyCLabs\Enum\Enum;
use Symfony\Component\PropertyInfo\Type;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface as Denormalizer;

final class EnumNormalizer implements Denormalizer
{
    /**
     * @return array<class-string, true>
     */
    public function getSupportedTypes(?string $format): array
    {
        return [Enum::class => true];
    }

    /**
     * @psalm-suppress MissingParamType, MethodSignatureMismatch
     */
    public function supportsDenormalization($data, ?string $type = null, ?string $format = null, array $context = []): bool
    {
        return is_subclass_of($type ?? '', Enum::class);
    }

    /**
     * @psalm-suppress InvalidArgument,MoreSpecificImplementedParamType, MethodSignatureMismatch, ImplementedReturnTypeMismatch
     *
     * @template T
     *
     * @param T                     $data
     * @param class-string<Enum<T>> $type
     * @param array<string>         $context
     *
     * @return Enum<T>
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): Enum
    {
        if (!$type::isValid($data)) {
            throw NotNormalizableValueException::createForUnexpectedDataType(
                sprintf(
                    'Ожидали enum: %s, получили: %s',
                    $type::toArray(),
                    get_debug_type($type)
                ),
                $data,
                [Type::BUILTIN_TYPE_STRING],
                $context['deserialization_path'] ?? null,
                true
            );
        }

        try {
            return new $type($data);
        } catch (\UnexpectedValueException $e) {
            throw new UnexpectedValueException($e->getMessage(), 0, $e);
        }
    }
}
