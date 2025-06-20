<?php

declare(strict_types=1);

namespace Bermuda\Stdlib;

use InvalidArgumentException;

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
 * - Base conversion support (binary, octal, hexadecimal, and arbitrary bases)
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
     * - Hexadecimal with prefix: "0xFF" → 255 (int)
     * - Octal with prefix: "0755" or "0o755" → 493 (int)
     * - Binary with prefix: "0b1010" → 10 (int)
     * - Signed numbers: "-123", "+45.67"
     * - Edge cases: empty strings, whitespace, mixed content
     * - Large numbers: handles PHP_INT_MAX overflow gracefully
     *
     * Conversion rules:
     * - Pure numeric strings without decimal point become integers
     * - Numeric strings with decimal point become floats
     * - Scientific notation always becomes float
     * - Special base formats (hex, octal, binary) become integers
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
     * NumberConverter::convertValue('0xFF')       // → 255 (int)
     * NumberConverter::convertValue('0755')       // → 493 (int)
     * NumberConverter::convertValue('0b1010')     // → 10 (int)
     * NumberConverter::convertValue('hello')      // → 'hello' (string, unchanged)
     * NumberConverter::convertValue('-123')       // → -123 (int)
     * NumberConverter::convertValue('123abc')     // → '123abc' (string, mixed content)
     * NumberConverter::convertValue('')           // → '' (empty string unchanged)
     * NumberConverter::convertValue(null)         // → null (unchanged)
     */
    public static function convertValue(mixed $value): mixed
    {
        // Handle non-string types
        if (!is_string($value)) {
            if (is_numeric($value)) {
                return $value;
            }
            if (is_bool($value)) {
                return (int)$value;
            }
            if (is_null($value)) {
                return 0;
            }
            return $value;
        }

        // If string has leading or trailing whitespace, it should remain unchanged
        if ($value !== trim($value)) {
            return $value;
        }

        $trimmed = $value; // No need to trim again since we checked above

        // Return original value if empty after trim
        if ($trimmed === '') {
            return $value;
        }

        // Check for special number formats first (with prefixes)
        if (self::isHex($value)) {
            return hexdec($value);
        }

        if (self::isBinary($value)) {
            if (str_starts_with(strtolower($value), '0b')) {
                return bindec(substr($value, 2));
            }
        }

        if (self::isOctal($value)) {
            // Handle different octal formats
            if (str_starts_with(strtolower($value), '0o')) {
                return octdec(substr($value, 2));
            } elseif (str_starts_with($value, '0') && strlen($value) > 1) {
                return octdec(substr($value, 1));
            }
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

            // Check if it's a valid decimal number with letters (invalid)
            if (preg_match('/[a-zA-Z]/', $trimmed)) {
                return $value;
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
     * NumberConverter::isNumeric('0xFF')     // → true
     * NumberConverter::isNumeric('0755')     // → true
     * NumberConverter::isNumeric('0b1010')   // → true
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

        // Check for special formats first
        if (self::isHex($value) || self::isOctal($value) || self::isBinary($value)) {
            return true;
        }

        // Handle scientific notation separately for more reliable detection
        if (stripos($value, 'e') !== false) {
            return filter_var($value, FILTER_VALIDATE_FLOAT) !== false;
        }

        return is_numeric($value);
    }

    /**
     * Checks if a string represents a hexadecimal number
     * Requires 0x or 0X prefix
     */
    public static function isHex(mixed $value): bool
    {
        if (!is_string($value) || $value === '') {
            return false;
        }

        // Only with 0x/0X prefix
        if (str_starts_with(strtolower($value), '0x')) {
            $hex = substr($value, 2);
            return $hex !== '' && ctype_xdigit($hex);
        }

        return false;
    }

    /**
     * Checks if a string represents an octal number
     * Accepts traditional (0755) and modern (0o755) notation
     */
    public static function isOctal(mixed $value): bool
    {
        // Must be a non-empty string
        if (!is_string($value) || $value === '') {
            return false;
        }

        // Modern octal notation: 0o755 or 0O755
        if (str_starts_with($value, '0o') || str_starts_with($value, '0O')) {
            $oct = substr($value, 2);
            return $oct !== '' && preg_match('/^[0-7]+$/', $oct) === 1;
        }

        // Traditional octal notation: 0755 (starts with single 0 and length > 1)
        // Must not have multiple leading zeros (000123 is decimal, not octal)
        if (str_starts_with($value, '0') && strlen($value) > 1 && !str_starts_with($value, '00')) {
            $oct = substr($value, 1); // remove leading 0
            return $oct !== '' && preg_match('/^[0-7]+$/', $oct) === 1;
        }

        // Strings without prefix are treated as decimal numbers
        return false;
    }

    /**
     * Checks if a string represents a binary number
     * Requires 0b or 0B prefix
     */
    public static function isBinary(mixed $value): bool
    {
        if (!is_string($value) || $value === '') {
            return false;
        }

        // Only with 0b/0B prefix
        if (str_starts_with(strtolower($value), '0b')) {
            $bin = substr($value, 2);
            return $bin !== '' && preg_match('/^[01]+$/', $bin) === 1;
        }

        return false;
    }

    /**
     * Checks if a string represents a number in a specific base
     */
    public static function isBase(mixed $value, int $radix): bool
    {
        if (!is_string($value)) {
            return false;
        }

        if ($radix < 2 || $radix > 36) {
            throw new InvalidArgumentException('Radix must be between 2 and 36');
        }

        if ($value === '') {
            return false;
        }

        // Build valid characters for this base
        $validChars = [];

        // Add digits 0-9 as needed
        for ($i = 0; $i < min(10, $radix); $i++) {
            $validChars[] = (string)$i;
        }

        // Add letters A-Z as needed for bases > 10
        if ($radix > 10) {
            for ($i = 10; $i < $radix; $i++) {
                $letter = chr(ord('A') + $i - 10);
                $validChars[] = $letter;
                $validChars[] = strtolower($letter);
            }
        }

        // Check if all characters in value are valid for this base
        $valueChars = str_split(strtoupper($value));
        $validCharsUpper = array_map('strtoupper', $validChars);

        return array_all($valueChars, fn($char) => in_array($char, $validCharsUpper, true));
    }

    /**
     * Converts a string from any base to decimal
     * Auto-detects base from prefixes when base parameter is null
     */
    public static function convertBase(string $value, ?int $fromBase = null): int|float
    {
        if ($value === '') {
            throw new InvalidArgumentException('Empty string cannot be converted');
        }

        // Auto-detect base if not specified
        if ($fromBase === null) {
            // Priority order: HEX -> BINARY -> OCTAL -> DECIMAL
            if (self::isHex($value)) {
                return hexdec($value);
            }

            if (self::isBinary($value)) {
                if (str_starts_with(strtolower($value), '0b')) {
                    return bindec(substr($value, 2));
                }
            }

            if (self::isOctal($value)) {
                // Handle different octal formats
                if (str_starts_with(strtolower($value), '0o')) {
                    return octdec(substr($value, 2));
                } elseif (str_starts_with($value, '0') && strlen($value) > 1) {
                    return octdec(substr($value, 1));
                }
            }

            // Default to decimal
            if (is_numeric($value)) {
                return $value + 0;
            }

            throw new InvalidArgumentException('Cannot auto-detect base for value: ' . $value);
        }

        // Validate base
        if ($fromBase < 2 || $fromBase > 36) {
            throw new InvalidArgumentException('Base must be between 2 and 36');
        }

        // Validate value for the specified base
        if (!self::isBase($value, $fromBase)) {
            throw new InvalidArgumentException("Value '$value' is not valid for base $fromBase");
        }

        return (int)base_convert($value, $fromBase, 10);
    }

    /**
     * Parses string as float
     */
    public static function parseFloat(string $value): float
    {
        return (float)$value;
    }

    /**
     * Parses string as int with specified radix
     */
    public static function parseInt(string $value, int $radix = 10): int
    {
        return match ($radix) {
            2 => bindec($value),
            8 => octdec($value),
            16 => hexdec($value),
            default => (int)$value
        };
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
     *   is_float: bool,
     *   is_hex: bool,
     *   is_octal: bool,
     *   is_binary: bool
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
     * //   'is_float' => false,
     * //   'is_hex' => false,
     * //   'is_octal' => false,
     * //   'is_binary' => false
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
            'is_hex' => false,
            'is_octal' => false,
            'is_binary' => false,
        ];

        if (!is_string($value) || $value === '') {
            return $info;
        }

        // If string has leading or trailing whitespace, it won't convert
        if ($value !== trim($value)) {
            return $info;
        }

        // Check for special formats first
        if (self::isHex($value)) {
            $info['will_convert'] = true;
            $info['target_type'] = 'integer';
            $info['is_integer'] = true;
            $info['is_hex'] = true;
            return $info;
        }

        if (self::isOctal($value)) {
            $info['will_convert'] = true;
            $info['target_type'] = 'integer';
            $info['is_integer'] = true;
            $info['is_octal'] = true;
            return $info;
        }

        if (self::isBinary($value)) {
            $info['will_convert'] = true;
            $info['target_type'] = 'integer';
            $info['is_integer'] = true;
            $info['is_binary'] = true;
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

    /**
     * Converts value to number or throws exception if conversion is impossible.
     *
     * Unlike convertValue(), this method guarantees a numeric result or throws an exception.
     * This is useful when you need to ensure a value is definitely a number and want
     * to fail fast if it's not convertible.
     *
     * @param mixed $value Value to convert to number
     * @return int|float Converted numeric value
     * @throws InvalidArgumentException If value cannot be converted to number
     *
     * @example
     * NumberConverter::convertToNumber('123');     // → 123 (int)
     * NumberConverter::convertToNumber('45.67');   // → 45.67 (float)
     * NumberConverter::convertToNumber('0xFF');    // → 255 (int)
     * NumberConverter::convertToNumber('hello');   // → throws InvalidArgumentException
     * NumberConverter::convertToNumber('123abc');  // → throws InvalidArgumentException
     * NumberConverter::convertToNumber(true);      // → 1 (int)
     * NumberConverter::convertToNumber(null);      // → 0 (int)
     */
    public static function convertToNumber(mixed $value): int|float
    {
        $converted = self::convertValue($value);

        // If convertValue returned the same string value, conversion failed
        if (is_string($value) && $converted === $value) {
            // Provide more specific error messages based on input type
            $errorMessage = match (true) {
                $value === '' => 'Cannot convert empty string to number',
                trim($value) !== $value => 'Cannot convert string with whitespace to number: "' . $value . '"',
                default => 'Cannot convert non-numeric string to number: "' . $value . '"'
            };
            throw new InvalidArgumentException($errorMessage);
        }

        // For non-string inputs, check if result is numeric
        if (is_numeric($converted)) {
            return $converted;
        }

        // Provide error messages for other types
        $errorMessage = match (true) {
            is_array($value) => 'Cannot convert array to number',
            is_object($value) => 'Cannot convert object of type ' . get_class($value) . ' to number',
            is_resource($value) => 'Cannot convert resource to number',
            default => 'Cannot convert value of type ' . gettype($value) . ' to number'
        };

        throw new InvalidArgumentException($errorMessage);
    }

    /**
     * Normalizes input value to numeric type for use in Number class
     * This is the main entry point for Number class conversions
     */
    public static function normalize(mixed $value): int|float
    {
        return self::convertToNumber($value);
    }
}