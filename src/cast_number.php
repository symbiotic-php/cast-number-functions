<?php

declare(strict_types=1);

namespace _S;

/**
 * Разрешает преобразование в числа с запятой в качестве дробного разделителя
 *
 * 1234,00 -> 1234.00 (float)
 * 1,123   -> 1.123   (float)
 *
 * @notice Если будет передан денежный формат: 1,234,345.43 | 1 1234,56
 * он не будет обработан, для этого отдельное разрешение
 */
const CAST_NUMBER_ALLOW_COMMA_SEPARATOR = 64;

/**
 * При использовании денежные форматы будут преобразованы в целые или дробные числа
 *
 * 1 000 000,00        -> 1000000.0,
 * 1 000 000.00        -> 1000000.0,
 * 1,000,000.00        -> 1000000.0,
 * 1 000 000 000,00007 -> 1000000000.00007,
 * 1 000 000 000.00007 -> 1000000000.00007,
 * 1,000,000,000.00007 -> 1000000000.00007,
 * 1 000 000           -> 1000000,
 * 1,000,000           -> 1000000,
 * 1 000 000 000       -> 1000000000,
 * 1,000,000,000       -> 1000000000,
 * 1.234.567,89        -> 1234567.89
 * 1234,321            -> 1234.321,
 * 1234,54             -> 1234.54,
 * 1,234               -> 1.234  @notice Будьте внимательны: английский формат и одной группой нолей и без дробной
 * части будет воспринят как дробное число!
 */
const CAST_NUMBER_HANDLE_MONEY_FORMAT = 128;

/**
 * Разрешает преобразования для шестнадцатеричного и битового представления целых
 *
 *  0x1a         ->   26 (int)
 * '0X1A'        ->   26 (int)
 * '-0x1a'       ->   26 (int)
 * '-0X1A'       ->   26 (int)
 * '0b11111111'  ->  255 (int)
 * '0B11111111'  ->  255 (int)
 * '-0b11111111' -> -255 (int)
 * '-0B11111111' => -255 (int)
 */
const CAST_NUMBER_HANDLE_SPECIAL_FORMATS = 256;

/**
 * При включении булев тип будет приведен в integer
 *
 *  true  -> 1
 *  false -> 0
 */
const CAST_NUMBER_BOOLEAN_AS_INT = 512;

/**
 * При включении NULL будет приведен к нулю
 * null -> 0
 */
const CAST_NUMBER_NULL_AS_ZERO = 1024;

/**
 * При включении не числовые типы будут приведены к NAN (float)
 *
 * iterable -> NAN // Для обработки итераторов используйте iterator_cast_numbers()
 * string   -> NAN
 * object   -> NAN
 * resource -> NAN
 *
 * true     -> NAN // Если не добавлен флаг CAST_NUMBER_BOOLEAN_AS_INT
 * false    -> NAN // Если не добавлен флаг CAST_NUMBER_BOOLEAN_AS_INT
 * null     -> NAN // Если не добавлен флаг CAST_NUMBER_NULL_AS_ZERO
 */
const CAST_NUMBER_STRICT_MODE = 2048;

/**
 * Рекурсивно обрабатывать вложенные элементы являющиеся итераторами
 */
const CAST_NUMBER_DISALLOW_RECURSIVE = 4096;

if (!function_exists('_S\\money_parse')) {
    /**
     * Денежные форматы будут преобразованы в целые или дробные числа
     *
     * @param string|\Stringable $number
     * @param bool               $returnAsString
     * @info https://www.php.net/manual/en/numberformatter.parsecurrency.php
     *
     * 1 000 000,00        -> 1000000.0,
     * 1 000 000.00        -> 1000000.0,
     * 1,000,000.00        -> 1000000.0,
     *
     * 1 000 000 000,0007 -> 1000000000.0007,
     * 1 000 000 000.0007 -> 1000000000.0007,
     * 1,000,000,000.0007 -> 1000000000.0007,
     *
     * 1 000 000           -> 1000000,
     * 1,000,000           -> 1000000,
     * 1 000 000 000       -> 1000000000,
     * 1,000,000,000       -> 1000000000,
     *
     * 1.234.567,89        -> 1234567.89
     * 1234,321            -> (string) "1234,321",
     * 1234,54             -> (string)"1234,54",
     * 1,234               -> (string) "1.234"
     *
     * @notice Будьте внимательны: 1,234 английский формат с одной группой будет проигнорирован,
     * для такой конвертации используйте cast_number(), там он будет приведен в дробному!
     *
     * @return string|int|float
     */
    function money_parse(string|\Stringable $number, bool $returnAsString = false): string|int|float
    {
        $number = (string)$number;
        if (
            strpbrk($number, ' ,') !== false
            && preg_match(
                '/^((?<space_integer>\d{1,3}(\s\d{3})+)(?<space_fractional>[\.\,](\d+))?)$|^((?<comma_integer>\d{1,3}([\,]\d{3})+)(?<comma_fractional>\.\d+)?)$/i',
                $number,
                $matched
            ) === 1) {
            if (!empty($matched['space_integer'])) {
                $number = str_replace([' '], [''], $matched['space_integer'])
                    . (!empty($matched['space_fractional']) ?
                        str_replace(',', '.', $matched['space_fractional']) : '');
            } else {
                $number = str_replace(
                    [' ', ','],
                    ['', ''],
                    $matched['comma_integer']
                ) . ($matched['comma_fractional'] ?? '');
            }

            if ($returnAsString) {
                return $number;
            } elseif ((int)$number == (float)$number && !str_contains($number, '.')) {
                return intval($number);
            } else {
                return (float)$number;
            }
        }
        return $returnAsString ? $number : (float)$number;
    }
}
if (!function_exists('_S\\cast_number')) {
    /**
     * @param mixed       $value
     * @param int|null    $floatPrecision
     * @param string|null $decimal_separator
     * @param string|null $thousands_separator
     * @param int         $mode
     *
     * @return mixed
     */
    function cast_number(
        mixed $value,
        int $floatPrecision = null,
        ?string $decimal_separator = null,
        ?string $thousands_separator = null,
        int $mode = 0
    ): mixed {
        if (is_string($value) || $value instanceof \Stringable) {
            $value = (string)$value;
            if ($mode & CAST_NUMBER_HANDLE_MONEY_FORMAT) {
                $value = money_parse($value, true);
            }
            /** @psalm-suppress PossiblyInvalidArgument */
            if ($mode & CAST_NUMBER_ALLOW_COMMA_SEPARATOR && preg_match('/^\d+\,\d+$/i', $value) === 1) {
                $value = str_replace(',', '.', $value);
            }
        }
        if (is_int($value) || is_float($value)) {
            // not prepare
        } elseif (is_numeric($value)) {
            if ((int)$value == (float)$value && strpbrk((string)$value, '.eE') === false) {
                $value = intval($value, 0);
            } else {
                $value = (float)$value;
            }
        } elseif (
            is_string($value)
            && $value !== ''
            && preg_match('/^-?(0x[[:xdigit:]]+|0b[01]+)$/i', $value) === 1) {
            $value = intval($value, 0);
        } elseif (
            (is_bool($value) && $mode & CAST_NUMBER_BOOLEAN_AS_INT)
            || (is_null($value) && $mode & CAST_NUMBER_NULL_AS_ZERO)
        ) {
            $value = (int)$value;
        } else {
            return $mode & CAST_NUMBER_STRICT_MODE ? NAN : $value;
        }

        if ($decimal_separator) {
            $value = number_format(
                $value,
                is_null($floatPrecision) ? 0 : $floatPrecision,
                $decimal_separator,
                $thousands_separator
            );
        } elseif (is_int($floatPrecision)) {
            $value = round($value, $floatPrecision, $mode > 0 ? $mode : PHP_ROUND_HALF_UP);
        }
        return $value;
    }

    /**
     * @param iterable    $iterator
     * @param int|null    $floatPrecision
     * @param string|null $decimal_separator
     * @param string|null $thousands_separator
     * @param int         $mode
     *
     * @return \Generator
     */
    function iterator_cast_numbers(
        iterable $iterator,
        int $floatPrecision = null,
        ?string $decimal_separator = null,
        ?string $thousands_separator = null,
        int $mode = 0
    ): \Generator {
        /** @psalm-suppress  MixedAssignment */
        foreach ($iterator as $k => $v) {
            if (is_iterable($v) && $mode ^ CAST_NUMBER_DISALLOW_RECURSIVE) {
                $handled = iterator_cast_numbers(
                    $v,
                    $floatPrecision,
                    $decimal_separator,
                    $thousands_separator,
                    $mode
                );
                $value = is_array($v) ? iterator_to_array($handled) : $handled;
            } else {
                $value = cast_number(
                    $v,
                    $floatPrecision,
                    $decimal_separator,
                    $thousands_separator,
                    $mode
                );
            }
            yield $k => $value;
        }
    }
}
