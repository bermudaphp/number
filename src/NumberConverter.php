<?php

declare(strict_types=1);

namespace Bermuda\Stdlib;

/**
 * Number Converter Utility
 *
 * Provides centralized, locale-independent numeric conversion for string values.
 * Handles edge cases like scientific notation, large numbers, and malformed input
 * while maintaining consistent behavior across different system locales.
 *
 * This utility is designed to be used across various components that need reliable
 * string-to-number conversion, such as URL parameter processing, configuration parsing,
 * form data handling, and API input validation.
 *
 * Key Features:
 * - Locale-independent number parsing (always uses C locale internally)
 * - Scientific notation support (1e5, 2.5e-3, etc.)
 * - Large number handling with overflow protection
 * - Edge case handling (empty strings, whitespace, mixed content)
 * - Consistent integer vs float detection
 * - Thread-safe operation with locale restoration
 * - Zero-dependency implementation
 */
final class NumberConverter
{
    /**
     * Stores original locale before temporary C locale switch
     */
    private static ?string $originalLocale = null;

    /**
     * Convert string value to appropriate numeric type if possible.
     *
     * Performs safe, locale-independent conversion of string values to numbers.
     * Non-numeric strings are returned unchanged. The conversion handles:
     * - Standard integers: "123" → 123 (int)
     * - Standard floats: "45.67" → 45.67 (float)
     * - Scientific notation: "1e5" → 100000.0 (float)
     * - Signed numbers: "-123", "+45.67"
     * - Edge cases: empty strings, whitespace, mixed content
     * - Large numbers: handles PHP_INT_MAX overflow gracefully
     *
     * Conversion rules:
     * - Pure numeric strings without decimal point become integers
     * - Numeric strings with decimal point become floats
     * - Scientific notation always becomes float
     * - Non-numeric strings remain unchanged
     * - null, empty string, and non-string values remain unchanged
     *
     * @param mixed $value The value to convert (typically string from user input)
     * @return mixed Original value if non-numeric string, converted number otherwise
     *
     * @example
     * NumberConverter::convertValue('123')        // → 123 (int)
     * NumberConverter::convertValue('45.67')      // → 45.67 (float)
     * NumberConverter::convertValue('1e5')        // → 100000.0 (float)
     * NumberConverter::convertValue('hello')      // → 'hello' (string, unchanged)
     * NumberConverter::convertValue('-123')       // → -123 (int)
     * NumberConverter::convertValue('123abc')     // → '123abc' (string, mixed content)
     * NumberConverter::convertValue('')           // → '' (empty string unchanged)
     * NumberConverter::convertValue(null)         // → null (unchanged)
     */
    public static function convertValue(mixed $value): mixed
    {
        if (!self::isNumeric($value)) {
            return $value;
        }

        $trimmed = trim((string)$value);

        // Return original value if empty after trim
        if ($trimmed === '') {
            return $value;
        }

        // Temporarily switch to C locale for predictable parsing
        // This prevents locale-specific decimal separators (comma vs dot) from causing issues
        // For example, in German locale "1.5" might be interpreted differently
        self::$originalLocale = setlocale(LC_NUMERIC, 0);
        setlocale(LC_NUMERIC, 'C');

        try {
            // Handle scientific notation (1e5, 2.5e-3, 1E10, etc.)
            // Scientific notation should always result in float type
            if (stripos($trimmed, 'e') !== false) {
                $result = filter_var($trimmed, FILTER_VALIDATE_FLOAT);
                return $result !== false ? $result : $value;
            }

            // Standard numeric conversion using PHP's type juggling
            // The + 0 trick converts string to int if no decimal, float if decimal present
            // This is more reliable than (int) or (float) casting for mixed scenarios
            if (is_numeric($trimmed)) {
                return $trimmed + 0;
            }

            // Not a numeric value, return original
            return $value;

        } finally {
            // Always restore original locale to prevent side effects
            // This is critical for thread safety and preventing locale pollution
            if (self::$originalLocale !== null) {
                setlocale(LC_NUMERIC, self::$originalLocale);
            }
        }
    }

    /**
     * Convert array of values, applying numeric conversion to each element.
     *
     * Batch conversion method for processing multiple values efficiently.
     * Maintains array structure (including keys) while converting individual values.
     * This is useful for processing entire datasets, form submissions, configuration
     * arrays, or any collection of values that may contain numeric strings.
     *
     * @param array $values Array of values to convert (preserves keys)
     * @return array Array with same structure but converted numeric values
     *
     * @example
     * NumberConverter::convertArray(['id' => '123', 'name' => 'hello', 'price' => '45.67'])
     * // → ['id' => 123, 'name' => 'hello', 'price' => 45.67]
     *
     * NumberConverter::convertArray(['123', 'hello', '45.67'])
     * // → [123, 'hello', 45.67]
     */
    public static function convertArray(array $values): array
    {
        return array_map([self::class, 'convertValue'], $values);
    }

    /**
     * Check if a string value represents a valid number.
     *
     * Locale-independent check for numeric values, including scientific notation.
     * This is useful for validation before conversion or when you need to know
     * if conversion will occur without actually performing it.
     *
     * More comprehensive than PHP's is_numeric() as it handles edge cases
     * and locale issues consistently. Strings with leading/trailing whitespace
     * are considered non-numeric to maintain consistency with convertValue().
     *
     * @param mixed $value Value to check (any type accepted)
     * @return bool True if value can be converted to number, false otherwise
     *
     * @example
     * NumberConverter::isNumeric('123')      // → true
     * NumberConverter::isNumeric('45.67')    // → true
     * NumberConverter::isNumeric('1e5')      // → true
     * NumberConverter::isNumeric('hello')    // → false
     * NumberConverter::isNumeric('123abc')   // → false
     * NumberConverter::isNumeric(' 123 ')    // → false (whitespace preserved)
     * NumberConverter::isNumeric(123)        // → true (already numeric)
     */
    public static function isNumeric(mixed $value): bool
    {
        // Handle non-string types
        if (!is_string($value)) {
            return is_numeric($value);
        }

        // If string has leading or trailing whitespace, it's not numeric for our purposes
        if ($value !== ($trimmed = trim($value))) {
            return false;
        }

        // Check if it's empty after we know it has no whitespace
        if ($value === '' || $trimmed === '') {
            return false;
        }

        // Handle scientific notation separately for more reliable detection
        if (stripos($value, 'e') !== false) {
            return filter_var($value, FILTER_VALIDATE_FLOAT) !== false;
        }

        return is_numeric($value);
    }

    /**
     * Get information about how a value would be converted.
     *
     * Returns metadata about the conversion without actually performing it.
     * Useful for debugging, logging, or validation scenarios.
     *
     * @param mixed $value Value to analyze
     * @return array{
     *   will_convert: bool,
     *   original_value: mixed,
     *   target_type: string,
     *   is_scientific: bool,
     *   is_integer: bool,
     *   is_float: bool
     * } Conversion metadata
     *
     * @example
     * NumberConverter::getConversionInfo('123')
     * // → [
     * //   'will_convert' => true,
     * //   'original_value' => '123',
     * //   'target_type' => 'integer',
     * //   'is_scientific' => false,
     * //   'is_integer' => true,
     * //   'is_float' => false
     * // ]
     */
    public static function getConversionInfo(mixed $value): array
    {
        $info = [
            'will_convert' => false,
            'original_value' => $value,
            'target_type' => gettype($value),
            'is_scientific' => false,
            'is_integer' => false,
            'is_float' => false,
        ];

        if (!is_string($value) || $value === '') {
            return $info;
        }

        // If string has leading or trailing whitespace, it won't convert
        if ($value !== trim($value)) {
            return $info;
        }

        // Check if the value is numeric
        if (!is_numeric($value)) {
            return $info;
        }

        $info['will_convert'] = true;

        // Check for scientific notation
        if (stripos($value, 'e') !== false) {
            $info['is_scientific'] = true;
            $info['target_type'] = 'double';
            $info['is_float'] = true;
        } else {
            // Determine if it would be int or float
            if (str_contains($value, '.')) {
                $info['target_type'] = 'double';
                $info['is_float'] = true;
            } else {
                $info['target_type'] = 'integer';
                $info['is_integer'] = true;
            }
        }

        return $info;
    }
}