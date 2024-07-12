<?php declare(strict_types=1);


namespace SdsDev\RestBundleDoctrine\Metadata\Common;

use InvalidArgumentException;

class Asserted
{
    /**
     * @template T
     * @param T|null $value
     * @return T
     * @psalm-assert !null $value
     */
    public static function notNull(mixed $value, ?string $message = null)
    {
        if (null === $value) {
            throw new InvalidArgumentException($message ?? 'Provided value must not be null');
        }

        return $value;
    }

    /**
     * @psalm-assert !null $value
     */
    public static function string(mixed $value, ?string $message = null): string
    {
        if (!is_string($value)) {
            throw new InvalidArgumentException(
                $message ?? 'Provided value must be a string but was ' . TypeUtils::getType($value)
            );
        }

        return $value;
    }

    /**
     * @psalm-assert string|null $value
     */
    public static function stringOrNull(mixed $value, ?string $message = null): ?string
    {
        if (null === $value) {
            return null;
        }

        return self::string($value, $message);
    }

    /**
     * @return non-empty-string
     * @psalm-assert non-empty-string $value
     */
    public static function nonEmptyString(mixed $value, ?string $message = null): string
    {
        if (!is_string($value) || '' === $value) {
            throw new InvalidArgumentException(
                $message ?? 'Provided value must be a non empty string, was ' . gettype($value)
            );
        }

        return $value;
    }

    /**
     * @psalm-assert int $value
     */
    public static function int(mixed $value, ?string $message = null): int
    {
        if (!is_int($value)) {
            throw new InvalidArgumentException(
                $message ?? 'Provided value must be an int but was ' . TypeUtils::getType($value)
            );
        }

        return $value;
    }

    /**
     * @psalm-assert int|null $value
     */
    public static function intOrNull(mixed $value, ?string $message = null): ?int
    {
        if (null === $value) {
            return null;
        }

        return self::int($value, $message);
    }

    /**
     * @psalm-assert positive-int $value
     * @return positive-int
     */
    public static function positiveInt(mixed $value, ?string $message = null): int
    {
        $intVal = self::int($value);

        if ($intVal < 1) {
            throw new InvalidArgumentException($message ?? 'Provided value must be a positive int');
        }

        return $intVal;
    }

    /**
     * @psalm-assert positive-int|null $value
     * @return positive-int|null
     */
    public static function positiveIntOrNull(mixed $value, ?string $message = null): ?int
    {
        if (null === $value) {
            return null;
        }

        return self::positiveInt($value, $message);
    }

    public static function integerish(mixed $value, ?string $message = null): int
    {
        $intVal = (int)$value;

        if ((string)$intVal != (string)$value) {
            throw new InvalidArgumentException($message ?? 'Provided value must be integerish');
        }

        return $intVal;
    }

    public static function integerishOrNull(mixed $value, ?string $message = null): ?int
    {
        if (null === $value) {
            return null;
        }

        return self::integerish($value, $message);
    }

    /**
     * @psalm-assert float $value
     */
    public static function float(mixed $value, ?string $message = null): float
    {
        if (!is_float($value)) {
            throw new InvalidArgumentException(
                $message ?? 'Provided value must be a float but was ' . TypeUtils::getType($value)
            );
        }

        return $value;
    }

    /**
     * @psalm-assert float|null $value
     */
    public static function floatOrNull(mixed $value, ?string $message = null): ?float
    {
        if (null === $value) {
            return null;
        }

        return self::float($value, $message);
    }

    public static function floatish(mixed $value, ?string $message = null): float
    {
        $floatVal = (float)$value;

        if ((string)$floatVal != (string)$value) {
            throw new InvalidArgumentException($message ?? 'Provided value must be floatish');
        }

        return $floatVal;
    }

    public static function floatishOrNull(mixed $value, ?string $message = null): ?float
    {
        if (null === $value) {
            return null;
        }

        return self::floatish($value, $message);
    }

    /**
     * @psalm-assert array $value
     */
    public static function array(mixed $value, ?string $message = null): array
    {
        if (!is_array($value)) {
            throw new InvalidArgumentException(
                $message ?? 'Provided value must be an array but was ' . TypeUtils::getType($value)
            );
        }

        return $value;
    }

    /**
     * @psalm-assert array|null $value
     */
    public static function arrayOrNull(mixed $value, ?string $message = null): ?array
    {
        if (null === $value) {
            return null;
        }

        return self::array($value, $message);
    }

    /**
     * @psalm-assert bool $value
     */
    public static function bool(mixed $value, ?string $message = null): bool
    {
        if (!is_bool($value)) {
            throw new InvalidArgumentException(
                $message ?? 'Provided value must be a bool but was ' . TypeUtils::getType($value)
            );
        }

        return $value;
    }

    /**
     * @psalm-assert bool|null $value
     */
    public static function boolOrNull(mixed $value, ?string $message = null): ?bool
    {
        if (null === $value) {
            return null;
        }

        return self::bool($value, $message);
    }

    /**
     * @template T
     * @return iterable<T>
     * @psalm-assert iterable<T> $value
     */
    public static function iterable(mixed $value, ?string $message = null): iterable
    {
        if (!is_iterable($value)) {
            throw new InvalidArgumentException(
                $message ?? 'Provided value must be iterable but was ' . TypeUtils::getType($value)
            );
        }

        return $value;
    }

    /**
     * @template T
     * @return iterable<T>|null
     * @psalm-assert iterable<T>|null $value
     */
    public static function iterableOrNull(mixed $value, ?string $message = null): ?iterable
    {
        if (null === $value) {
            return null;
        }

        return self::iterable($value, $message);
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @return T
     * @psalm-assert =T $value
     */
    public static function instanceOf(mixed $value, string $class, ?string $message = null): object
    {
        if (!$value instanceof $class) {
            throw new InvalidArgumentException(
                $message ?? 'Provided value must be of class ' . $class . ' but was ' . TypeUtils::getType($value)
            );
        }

        return $value;
    }

    /**
     * @template T of object
     * @param mixed $value
     * @param class-string<T> $class
     * @return T|null
     * @psalm-assert ?T $value
     */
    public static function instanceOfOrNull(?object $value, string $class, ?string $message = null): ?object
    {
        if (null === $value) {
            return null;
        }

        return self::instanceOf($value, $class, $message);
    }
}
