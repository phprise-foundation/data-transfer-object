<?php

declare(strict_types=1);

namespace Phprise\DataTransferObject;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Phprise\Common\ValueObject\StringObject;
use ReflectionClass;
use ReflectionProperty;
use RuntimeException;
use Stringable;

abstract class TransferObject implements Stringable, TransferObjectInterface
{
    public function toArray(): array
    {
        $ref    =   new ReflectionClass($this);
        $props  =   $ref->getProperties();

        return $this->extractProperties($props);
    }

    public static function fromArray(array $array): static
    {
        $static =   new static();
        $ref    =   new ReflectionClass($static);
        $props  =   $ref->getProperties();
        $array  =   self::arrayKeysSnakeToCamel($array);

        foreach ($props as $prop) {
            self::hydrateProperty($static, $prop, $array);
        }

        return $static;
    }

    public function toSnakeCaseArray(): array
    {
        $array = $this->toArray();
        return $this->arrayKeysCamelToSnake($array);
    }

    public function toJson(): string
    {
        $json = json_encode($this->toArray());

        if ($json === false) {
            throw new RuntimeException('Failed to encode JSON: ' . json_last_error_msg());
        }

        return $json;
    }

    public static function fromJson(string $json): static
    {
        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('Invalid JSON plain text.');
        }

        return static::fromArray($decoded);
    }

    public function toSnakeCaseJson(): string
    {
        $json = json_encode($this->toSnakeCaseArray());

        if ($json === false) {
            throw new RuntimeException('Failed to encode JSON: ' . json_last_error_msg());
        }

        return $json;
    }

    /**
     * @psalm-suppress MixedAssignment
     */
    private static function arrayKeysSnakeToCamel(array $array): array
    {
        $camelCaseArray = [];
        foreach ($array as $key => $value) {
            $camelCaseKey = (new StringObject((string) $key))->toCamel();
            if (is_array($value)) {
                $value = self::arrayKeysSnakeToCamel($value);
            }
            $camelCaseArray[$camelCaseKey] = $value;
        }
        return $camelCaseArray;
    }

    /**
     * @psalm-suppress MixedAssignment
     */
    private function arrayKeysCamelToSnake(array $array): array
    {
        $snakeCaseArray = [];
        foreach ($array as $key => $value) {
            $snakeCaseKey = (new StringObject((string) $key))->toSnake();
            if (is_array($value)) {
                $value = $this->arrayKeysCamelToSnake($value);
            }
            $snakeCaseArray[$snakeCaseKey] = $value;
        }
        return $snakeCaseArray;
    }

    /** Implicit conversion to string */
    #[\Override]
    public function __toString(): string
    {
        return $this->toJson();
    }

    /** Explicit conversion to string */
    public function toString(): string
    {
        return $this->__toString();
    }

    /**
     * Format using aliases
     *
     * Using default aliases
     *  <code>
     *      $this->format('property: :p1');
     *  </code>
     *
     * Using custom aliases
     *  <code>
     *      $this->format(
     *          queries: ['address: %address', 'date: %date'],
     *          aliases: ['address'=>'appointmentAddress', 'date'=>'appointmentDate'],
     *          separator: '<br>',
     *          marker: '%'
     *      );
     *  </code>
     *
     * @param array<string>|string $queries The output format
     * @param array<string> $aliases Short aliases to call the properties.
     * By default is the first letter with counter.
     * @param string $separator Separator of terms. Common values: '\n', '<br>', ',', '\t'
     * @param string $marker Indicates a variable into the query (Default ':')
     * @return string formated string
     */
    public function format(
        array|string $queries,
        array $aliases = [],
        string $separator = PHP_EOL,
        string $marker = ':'
    ): string {
        if (is_array($queries)) {
            /** @var array<string> $queries */
            $queries = implode($separator, $queries);
        }

        /** @var string $queries */

        $array = $this->toArray();

        if (empty($aliases)) {
            $aliases = $this->generateDefaultAliases(array_keys($array));
        }

        $keys   = array_map(fn ($k) => $marker . $k, array_keys($aliases));
        $values = array_map(fn ($v) => $this->getAttributeAsString($v), $aliases);

        return str_replace($keys, $values, $queries);
    }

    /**
     * @template T of object
     * @param class-string<T>|T $entity
     * @return T
     */
    public function toEntity(string|object $entity): object
    {
        /** @psalm-suppress MixedArgument */
        $instance = $this->resolveEntityInstance($entity);
        $refDto     =   new ReflectionClass($this);
        $refEntity  =   new ReflectionClass($instance);
        $props      =   $refDto->getProperties();

        foreach ($props as $prop) {
            $this->transferPropertyToEntity($prop, $refEntity, $instance);
        }

        return $instance;
    }

    protected function dateFormat(DateTimeInterface $date): string
    {
        return $date->format(DateTime::RFC3339);
    }

    /**
     * @template T
     * @param class-string<T> $className
     * @return T
     * @psalm-suppress MixedMethodCall
     */
    protected function createClassFromString(string $className): object
    {
        if (!class_exists($className)) {
            throw new RuntimeException(sprintf('Entity %s not found', $className));
        }

        return new $className();
    }

    protected function getAttributeAsString(string $attributeName): string
    {
        $getter = 'get' . ucfirst($attributeName);
        if (method_exists($this, $getter)) {
            /** @psalm-suppress MixedMethodCall */
            return $this->getStrVal($this->$getter());
        }

        if (property_exists($this, $attributeName)) {
            /** @psalm-suppress MixedPropertyFetch */
            return $this->getStrVal($this->$attributeName);
        }

        throw new RuntimeException(
            sprintf('Undefined attribute %s::%s', get_class($this), $attributeName)
        );
    }

    protected function getStrVal(mixed $val): string
    {
        if (!is_array($val) && !is_object($val)) {
            return (string) $val;
        }

        if ($val instanceof Stringable) {
            return (string) $val;
        }

        if ($val instanceof DateTimeInterface) {
            return $this->dateFormat($val);
        }

        $json = json_encode($val);
        if ($json === false) {
             throw new RuntimeException('Failed to encode JSON: ' . json_last_error_msg());
        }
        return $json;
    }

    /** @param ReflectionProperty[] $props */
    private function extractProperties(array $props): array
    {
        $data = [];
        foreach ($props as $prop) {
            $this->extractProperty($prop, $data);
        }
        return $data;
    }

    /**
     * @psalm-suppress MixedAssignment
     */
    private function extractProperty(ReflectionProperty $prop, array &$data): void
    {
        $name   =   $prop->getName();
        $value  =   $prop->getValue($this);

        if ($value instanceof TransferObject) {
            $data[$name] = $value->toArray();
            return;
        }

        if ($value instanceof DateTimeInterface) {
            $data[$name] = $this->dateFormat($value);
            return;
        }

        $data[$name] = $value;
    }

    /**
     * @psalm-suppress MixedAssignment
     */
    private static function hydrateProperty(object $static, ReflectionProperty $prop, array $array): void
    {
        $name = $prop->getName();
        if (!array_key_exists($name, $array)) {
            return;
        }

        $value = $array[$name];
        $typeReflection  = $prop->getType();

        if (!$typeReflection instanceof \ReflectionNamedType) {
            return;
        }

        $type = $typeReflection->getName();

        $value = self::hydrateNestedTransferObject($type, $value);
        $value = self::hydrateDateTime($type, $value);

        self::setPropertyValue($static, $name, $value);
    }

    /**
     * @psalm-suppress MixedAssignment
     */
    private static function hydrateNestedTransferObject(string $type, mixed $value): mixed
    {
        if (is_array($value) && is_subclass_of($type, TransferObject::class)) {
            return $type::fromArray($value);
        }
        return $value;
    }

    /**
     * @psalm-suppress MixedAssignment
     */
    private static function hydrateDateTime(string $type, mixed $value): mixed
    {
        if (is_string($value) && in_array($type, [DateTimeInterface::class, DateTimeImmutable::class])) {
            return new DateTimeImmutable($value);
        }
        return $value;
    }

    private static function setPropertyValue(object $object, string $name, mixed $value): void
    {
        $setter = 'set' . ucfirst($name);
        if (method_exists($object, $setter)) {
            $object->$setter($value);
            return;
        }

        if (property_exists($object, $name)) {
            $object->$name = $value;
        }
    }

    /**
     * @psalm-suppress MixedArgument
     * @return array<string>
     */
    private function generateDefaultAliases(array $keys): array
    {
        $aliases = [];
        array_map(function ($propName) use (&$aliases) {
            $this->addUniqueAlias($aliases, $propName);
        }, $keys);
        return $aliases;
    }

    /**
     * @param array<string> &$aliases
     */
    private function addUniqueAlias(array &$aliases, string $propName): void
    {
        $count = 1;
        do {
            $alias = substr($propName, 0, 1) . $count++;
        } while (isset($aliases[$alias]));
        $aliases[$alias] = $propName;
    }

    /**
     * @template T of object
     * @param class-string<T>|T $entity
     * @return T
     * @psalm-suppress InvalidReturnType, InvalidReturnStatement, ArgumentTypeCoercion, MixedMethodCall, NoValue
     */
    private function resolveEntityInstance(string|object $entity): object
    {
        if (is_string($entity)) {
            return $this->createClassFromString($entity);
        }
        return $entity;
    }

    /**
     * @psalm-suppress MixedAssignment, MixedArgument, ArgumentTypeCoercion, MixedMethodCall
     */
    private function transferPropertyToEntity(ReflectionProperty $propDto, ReflectionClass $refEntity, object $instance): void
    {
        $propName  = $propDto->getName();
        $propValue = $propDto->getValue($this);

        if ($propValue === null) {
            return;
        }

        $propEntity = $refEntity->getProperty($propName);
        $typeReflection = $propEntity->getType();

        if (!$typeReflection instanceof \ReflectionNamedType) {
            return;
        }

        $typeEntity = $typeReflection->getName();

        if ($propValue instanceof TransferObject) {
            $propValue = $propValue->toEntity($typeEntity);
        }

        $this->setEntityValue($instance, $refEntity, $propName, $propValue, $propEntity);
    }

    /**
     * @psalm-suppress MixedMethodCall
     */
    private function setEntityValue(object $instance, ReflectionClass $refEntity, string $propName, mixed $value, ReflectionProperty $propEntity): void
    {
        $setter = 'set' . ucfirst($propName);
        if ($refEntity->hasMethod($setter)) {
            $instance->$setter($value);
            return;
        }

        if ($propEntity->isPublic()) {
            $instance->$propName = $value;
        }
    }
}
