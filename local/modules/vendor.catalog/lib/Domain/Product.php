<?php

declare(strict_types=1);

namespace Vendor\Catalog\Domain;

use DateTimeImmutable;
use DateTimeInterface;

final class Product
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $name,
        public readonly string $code,
        public readonly string $price,
        public readonly ?string $brand,
        public readonly bool $inStock,
        public readonly DateTimeImmutable $createdAt,
        public readonly ?DateTimeImmutable $updatedAt = null
    ) {
    }

    public static function fromRow(array $row): self
    {
        return new self(
            isset($row['ID']) ? (int) $row['ID'] : null,
            (string) ($row['NAME'] ?? ''),
            (string) ($row['CODE'] ?? ''),
            self::formatPrice((string) ($row['PRICE'] ?? '0')),
            $row['BRAND'] === null ? null : (string) $row['BRAND'],
            (bool) ($row['IN_STOCK'] ?? false),
            new DateTimeImmutable((string) ($row['CREATED_AT'] ?? 'now')),
            !empty($row['UPDATED_AT']) ? new DateTimeImmutable((string) $row['UPDATED_AT']) : null
        );
    }

    public static function formatPrice(string $value): string
    {
        return number_format((float) $value, 2, '.', '');
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'price' => $this->price,
            'brand' => $this->brand,
            'in_stock' => $this->inStock,
            'created_at' => $this->createdAt->format(DateTimeInterface::ATOM),
        ];
    }
}