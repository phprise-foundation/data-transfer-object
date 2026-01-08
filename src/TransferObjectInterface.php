<?php

declare(strict_types=1);

namespace Phprise\DataTransferObject;

interface TransferObjectInterface
{
    public function toArray(): array;
    public static function fromArray(array $array): static;
    public function toSnakeCaseArray(): array;
    public function toJson(): string;
    public static function fromJson(string $json): static;
    public function toSnakeCaseJson(): string;
    public function format(
        array|string $queries,
        array $aliases = [],
        string $separator = PHP_EOL,
        string $marker = ':'
    ): string;
    public function toEntity(string|object $entity): object;
}