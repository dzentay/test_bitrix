<?php

declare(strict_types=1);

namespace Vendor\Catalog\Validation;

use Vendor\Catalog\Domain\Product;
use Vendor\Catalog\Exceptions\ValidationException;

final class ProductValidator
{
    private const SORT_FIELDS = [
        'name',
        'price',
        'brand',
        'created_at',
        'in_stock',
    ];

    public function validateList(array $query): array
    {
        $errors = [];

        $q = $this->getString($query, 'q');
        if ($q !== null && mb_strlen($q) > 255) {
            $errors['q'] = 'Search query must be at most 255 characters';
        }

        $brand = $this->getString($query, 'brand');
        if ($brand !== null && mb_strlen($brand) > 255) {
            $errors['brand'] = 'Brand must be at most 255 characters';
        }

        $priceFrom = $this->getNumeric($query, 'price_from', $errors, 'price_from');
        $priceTo = $this->getNumeric($query, 'price_to', $errors, 'price_to');

        if ($priceFrom !== null && $priceFrom < 0) {
            $errors['price_from'] = 'Price from must be greater than or equal to 0';
        }

        if ($priceTo !== null && $priceTo < 0) {
            $errors['price_to'] = 'Price to must be greater than or equal to 0';
        }

        if ($priceFrom !== null && $priceTo !== null && $priceFrom > $priceTo) {
            $errors['price_to'] = 'Price to must be greater than or equal to price from';
        }

        $inStock = $this->getBoolOrNull($query, 'in_stock', $errors);

        $sort = strtolower($this->getString($query, 'sort') ?? 'created_at');
        if (!in_array($sort, self::SORT_FIELDS, true)) {
            $errors['sort'] = 'Allowed sort fields: ' . implode(', ', self::SORT_FIELDS);
        }

        $order = strtolower($this->getString($query, 'order') ?? 'desc');
        if (!in_array($order, ['asc', 'desc'], true)) {
            $errors['order'] = 'Order must be asc or desc';
        }

        $page = $this->getInt($query, 'page', 1, $errors, 'page', 1);
        $perPage = $this->getInt($query, 'per_page', 10, $errors, 'per_page', 1, 100);

        if ($errors !== []) {
            throw ValidationException::withErrors($errors);
        }

        return [
            'q' => $q,
            'brand' => $brand,
            'price_from' => $priceFrom,
            'price_to' => $priceTo,
            'in_stock' => $inStock,
            'sort' => $sort,
            'order' => $order,
            'page' => $page,
            'per_page' => $perPage,
        ];
    }

    public function validateCreate(array $payload): array
    {
        $errors = [];

        $name = $this->getString($payload, 'name');
        if ($name === null) {
            $errors['name'] = 'Name is required';
        } elseif (mb_strlen($name) > 255) {
            $errors['name'] = 'Name must be at most 255 characters';
        }

        $code = $this->getString($payload, 'code');
        if ($code === null) {
            $errors['code'] = 'Code is required';
        } else {
            $code = strtolower($code);

            if (mb_strlen($code) > 255) {
                $errors['code'] = 'Code must be at most 255 characters';
            } elseif (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/u', $code)) {
                $errors['code'] = 'Code must be a slug: lowercase latin letters, digits and hyphens';
            }
        }

        $price = $this->getNumeric($payload, 'price', $errors, 'price');

        if ($price === null) {
            if (!isset($errors['price'])) {
                $errors['price'] = 'Price is required';
            }
        } elseif ($price < 0 || $price > 9999999999.99) {
            $errors['price'] = 'Price must be between 0 and 9999999999.99';
        }

        $brand = $this->getString($payload, 'brand');
        if ($brand !== null && mb_strlen($brand) > 255) {
            $errors['brand'] = 'Brand must be at most 255 characters';
        }

        $inStock = $this->getBoolOrNull($payload, 'in_stock', $errors) ?? false;

        if ($errors !== []) {
            throw ValidationException::withErrors($errors);
        }

        return [
            'name' => $name,
            'code' => $code,
            'price' => Product::formatPrice((string) $price),
            'brand' => $brand,
            'in_stock' => $inStock,
        ];
    }

    private function getString(array $data, string $key): ?string
    {
        $value = $data[$key] ?? null;

        if (is_array($value)) {
            $value = $value[0] ?? null;
        }

        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function getNumeric(array $data, string $key, array &$errors, string $errorKey): ?float
    {
        $raw = $data[$key] ?? null;

        if (is_array($raw)) {
            $raw = $raw[0] ?? null;
        }

        if ($raw === null || $raw === '') {
            return null;
        }

        if (!is_numeric($raw)) {
            $errors[$errorKey] = 'Must be numeric';

            return null;
        }

        $value = (float) $raw;

        if (!is_finite($value)) {
            $errors[$errorKey] = 'Must be a finite number';

            return null;
        }

        return $value;
    }

    private function getInt(
        array $data,
        string $key,
        int $default,
        array &$errors,
        string $errorKey,
        int $min = PHP_INT_MIN,
        int $max = PHP_INT_MAX
    ): int {
        $raw = $data[$key] ?? null;

        if (is_array($raw)) {
            $raw = $raw[0] ?? null;
        }

        if ($raw === null || $raw === '') {
            return $default;
        }

        if (filter_var($raw, FILTER_VALIDATE_INT) === false) {
            $errors[$errorKey] = 'Must be an integer';

            return $default;
        }

        $value = (int) $raw;

        if ($value < $min || $value > $max) {
            $errors[$errorKey] = sprintf('Must be between %d and %d', $min, $max);

            return $default;
        }

        return $value;
    }

    private function getBoolOrNull(array $data, string $key, array &$errors): ?bool
    {
        $raw = $data[$key] ?? null;

        if (is_array($raw)) {
            $raw = $raw[0] ?? null;
        }

        if ($raw === null || $raw === '') {
            return null;
        }

        if (is_bool($raw)) {
            return $raw;
        }

        if (is_int($raw)) {
            return $raw === 1;
        }

        if (is_string($raw)) {
            $value = strtolower(trim($raw));

            if (in_array($value, ['1', 'true', 'yes', 'on'], true)) {
                return true;
            }

            if (in_array($value, ['0', 'false', 'no', 'off'], true)) {
                return false;
            }
        }

        $errors[$key] = 'Must be one of: 1, 0, true, false, yes, no';

        return null;
    }
}