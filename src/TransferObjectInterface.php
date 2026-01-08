<?php

declare(strict_types=1);

namespace Phprise\DataTransferObject;

interface TransferObjectInterface
{
    /** @psalm-return array<array-key, mixed> */
    public function toArray(): array;

    public static function fromArray(array $array): static;

    /** @psalm-return array<string, mixed> */
    public function toSnakeCaseArray(): array;

    public function toJson(): string;

    public static function fromJson(string $json): static;

    public function toSnakeCaseJson(): string;

    /**
     * @param array<string>|string $queries
     * @param array<string> $aliases
     */
    public function format(
        array|string $queries,
        array $aliases = [],
        string $separator = PHP_EOL,
        string $marker = ':'
    ): string;

    /**
     * @template T of object
     * @param class-string<T>|T $entity
     * @return T
     */
    public function toEntity(string|object $entity): object;
}