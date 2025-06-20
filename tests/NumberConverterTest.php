<?php

declare(strict_types=1);

namespace Bermuda\Stdlib\Tests;

use Bermuda\Stdlib\NumberConverter;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\Group;
use InvalidArgumentException;

#[Group('number-converter')]
#[TestDox('NumberConverter utility tests')]
final class NumberConverterTest extends TestCase
{
    #[Test]
    #[TestDox('Basic numeric conversion works correctly')]
    public function basic_numeric_conversion_works_correctly(): void
    {
        // Integer conversion
        $this->assertSame(123, NumberConverter::convertValue('123'));
        $this->assertSame(-456, NumberConverter::convertValue('-456'));
        $this->assertSame(0, NumberConverter::convertValue('0'));

        // Float conversion
        $this->assertSame(123.45, NumberConverter::convertValue('123.45'));
        $this->assertSame(-67.89, NumberConverter::convertValue('-67.89'));
        $this->assertSame(0.0, NumberConverter::convertValue('0.0'));

        // Non-numeric strings remain unchanged
        $this->assertSame('hello', NumberConverter::convertValue('hello'));
        $this->assertSame('123abc', NumberConverter::convertValue('123abc'));
        $this->assertSame('abc123', NumberConverter::convertValue('abc123'));
    }

    #[Test]
    #[TestDox('Special base formats conversion')]
    public function special_base_formats_conversion(): void
    {
        // Hexadecimal format with 0x prefix
        $this->assertSame(255, NumberConverter::convertValue('0xFF'));
        $this->assertSame(255, NumberConverter::convertValue('0xff'));

        // Octal format with 0 or 0o prefix
        $this->assertSame(493, NumberConverter::convertValue('0755'));
        $this->assertSame(493, NumberConverter::convertValue('0o755'));

        // Binary format with 0b prefix
        $this->assertSame(10, NumberConverter::convertValue('0b1010'));
        $this->assertSame(10, NumberConverter::convertValue('0B1010'));
    }

    #[Test]
    #[DataProvider('scientificNotationProvider')]
    #[TestDox('Scientific notation conversion')]
    public function scientific_notation_conversion(string $input, float $expected): void
    {
        $result = NumberConverter::convertValue($input);
        $this->assertIsFloat($result);
        $this->assertEqualsWithDelta($expected, $result, 0.0001,
            "Scientific notation conversion failed for: $input");
    }

    public static function scientificNotationProvider(): array
    {
        return [
            ['1e5', 100000.0],
            ['1e-5', 0.00001],
            ['2.5e3', 2500.0],
            ['1.23e-4', 0.000123],
            ['0e0', 0.0],
            ['-1e2', -100.0],
            ['1E5', 100000.0],
            ['3.14159e0', 3.14159],
        ];
    }

    #[Test]
    #[TestDox('Edge cases handled correctly')]
    public function edge_cases_handled_correctly(): void
    {
        // Empty and whitespace strings should remain unchanged
        $this->assertSame('', NumberConverter::convertValue(''));
        $this->assertSame('   ', NumberConverter::convertValue('   '));
        $this->assertSame(' 123 ', NumberConverter::convertValue(' 123 ')); // Whitespace preserved

        // Non-string types
        $this->assertSame(123, NumberConverter::convertValue(123));
        $this->assertSame(45.67, NumberConverter::convertValue(45.67));
        $this->assertSame(0, NumberConverter::convertValue(null));
        $this->assertSame(1, NumberConverter::convertValue(true));
        $this->assertSame(0, NumberConverter::convertValue(false));

        // Special numeric strings (without leading/trailing whitespace)
        $this->assertSame(123, NumberConverter::convertValue('+123'));
        $this->assertSame(45.67, NumberConverter::convertValue('+45.67'));

        // Invalid scientific notation
        $this->assertSame('invalid-e5', NumberConverter::convertValue('invalid-e5'));
        $this->assertSame('1e', NumberConverter::convertValue('1e'));
        $this->assertSame('e5', NumberConverter::convertValue('e5'));
    }

    #[Test]
    #[TestDox('Array conversion preserves structure')]
    public function array_conversion_preserves_structure(): void
    {
        $input = [
            'id' => '123',
            'price' => '45.67',
            'name' => 'product',
            'scientific' => '1e3',
            'mixed' => '123abc',
            'hex' => '0xFF',
            'octal' => '0755',
            'binary' => '0b1010'
        ];

        $expected = [
            'id' => 123,
            'price' => 45.67,
            'name' => 'product',
            'scientific' => 1000.0,
            'mixed' => '123abc',
            'hex' => 255,
            'octal' => 493,
            'binary' => 10
        ];

        $result = NumberConverter::convertArray($input);
        $this->assertEquals($expected, $result);

        // Test with numeric indices
        $numericInput = ['123', '45.67', 'hello', '0xFF'];
        $numericExpected = [123, 45.67, 'hello', 255];
        $numericResult = NumberConverter::convertArray($numericInput);
        $this->assertEquals($numericExpected, $numericResult);
    }

    #[Test]
    #[TestDox('isNumeric method works correctly')]
    public function is_numeric_method_works_correctly(): void
    {
        // Positive cases
        $this->assertTrue(NumberConverter::isNumeric('123'));
        $this->assertTrue(NumberConverter::isNumeric('45.67'));
        $this->assertTrue(NumberConverter::isNumeric('-123'));
        $this->assertTrue(NumberConverter::isNumeric('+45.67'));
        $this->assertTrue(NumberConverter::isNumeric('1e5'));
        $this->assertTrue(NumberConverter::isNumeric('2.5e-3'));
        $this->assertTrue(NumberConverter::isNumeric('0xFF'));
        $this->assertTrue(NumberConverter::isNumeric('0755'));
        $this->assertTrue(NumberConverter::isNumeric('0b1010'));
        $this->assertTrue(NumberConverter::isNumeric(123));
        $this->assertTrue(NumberConverter::isNumeric(45.67));

        // Negative cases
        $this->assertFalse(NumberConverter::isNumeric('hello'));
        $this->assertFalse(NumberConverter::isNumeric('123abc'));
        $this->assertFalse(NumberConverter::isNumeric('abc123'));
        $this->assertFalse(NumberConverter::isNumeric(''));
        $this->assertFalse(NumberConverter::isNumeric('   '));
        $this->assertFalse(NumberConverter::isNumeric(' 123 ')); // Whitespace makes it non-numeric
        $this->assertFalse(NumberConverter::isNumeric('invalid-e5'));
        $this->assertFalse(NumberConverter::isNumeric(null));
        $this->assertFalse(NumberConverter::isNumeric(true));
    }

    #[Test]
    #[TestDox('Base format validation methods work correctly')]
    public function base_format_validation_methods_work_correctly(): void
    {
        // Hexadecimal tests
        $this->assertTrue(NumberConverter::isHex('0xFF'));
        $this->assertTrue(NumberConverter::isHex('0xff'));
        $this->assertTrue(NumberConverter::isHex('0x1A2B'));
        $this->assertTrue(NumberConverter::isHex('0xDEADBEEF'));
        $this->assertFalse(NumberConverter::isHex('FF')); // Without prefix
        $this->assertFalse(NumberConverter::isHex('0x'));
        $this->assertFalse(NumberConverter::isHex('0xGHI'));

        // Octal tests
        $this->assertTrue(NumberConverter::isOctal('0755'));  // traditional single leading zero
        $this->assertTrue(NumberConverter::isOctal('0o755')); // modern notation
        $this->assertTrue(NumberConverter::isOctal('0O755')); // modern notation uppercase
        $this->assertFalse(NumberConverter::isOctal('755')); // Without prefix
        $this->assertFalse(NumberConverter::isOctal('0789')); // Invalid octal digits
        $this->assertFalse(NumberConverter::isOctal('0o'));
        $this->assertFalse(NumberConverter::isOctal('000123')); // Multiple leading zeros = decimal

        // Binary tests
        $this->assertTrue(NumberConverter::isBinary('0b1010'));
        $this->assertTrue(NumberConverter::isBinary('0B1010'));
        $this->assertFalse(NumberConverter::isBinary('1010')); // Without prefix
        $this->assertFalse(NumberConverter::isBinary('0b1012')); // Invalid binary digits
        $this->assertFalse(NumberConverter::isBinary('0b'));
    }

    #[Test]
    #[TestDox('isBase validates correctly for different radixes')]
    public function is_base_validates_correctly_for_different_radixes(): void
    {
        $this->assertTrue(NumberConverter::isBase('1010', 2));
        $this->assertTrue(NumberConverter::isBase('755', 8));
        $this->assertTrue(NumberConverter::isBase('FF', 16));
        $this->assertTrue(NumberConverter::isBase('ff', 16)); // lowercase
        $this->assertTrue(NumberConverter::isBase('ZZ', 36));
        $this->assertTrue(NumberConverter::isBase('zz', 36)); // lowercase
        $this->assertTrue(NumberConverter::isBase('A', 11)); // single character
        $this->assertTrue(NumberConverter::isBase('123', 10));
        $this->assertFalse(NumberConverter::isBase('89', 8)); // 8,9 not valid in octal
        $this->assertFalse(NumberConverter::isBase('GG', 16)); // G not valid in hex
        $this->assertFalse(NumberConverter::isBase('', 10)); // empty string
        $this->assertFalse(NumberConverter::isBase('2', 2)); // 2 not valid in binary
        $this->assertFalse(NumberConverter::isBase(123, 10)); // not a string
    }

    #[Test]
    #[TestDox('isBase throws exception for invalid radix')]
    public function is_base_throws_exception_for_invalid_radix(): void
    {
        $this->expectException(InvalidArgumentException::class);
        NumberConverter::isBase('123', 1);
    }

    #[Test]
    #[TestDox('convertBase works correctly')]
    public function convert_base_works_correctly(): void
    {
        $this->assertSame(255, NumberConverter::convertBase('FF', 16));
        $this->assertSame(10, NumberConverter::convertBase('1010', 2));
        $this->assertSame(493, NumberConverter::convertBase('755', 8));
        $this->assertSame(1295, NumberConverter::convertBase('ZZ', 36));
        $this->assertSame(35, NumberConverter::convertBase('z', 36)); // single lowercase char
    }

    #[Test]
    #[TestDox('convertBase auto-detects format')]
    public function convert_base_auto_detects_format(): void
    {
        // Auto-detection based on prefixes
        $this->assertSame(255, NumberConverter::convertBase('0xFF'));   // hexadecimal with prefix
        $this->assertSame(255, NumberConverter::convertBase('0xff'));   // hexadecimal lowercase
        $this->assertSame(493, NumberConverter::convertBase('0755'));   // octal traditional
        $this->assertSame(493, NumberConverter::convertBase('0o755'));  // octal modern
        $this->assertSame(493, NumberConverter::convertBase('0O755'));  // octal modern uppercase
        $this->assertSame(10, NumberConverter::convertBase('0b1010'));  // binary
        $this->assertSame(10, NumberConverter::convertBase('0B1010'));  // binary uppercase
        $this->assertSame(123, NumberConverter::convertBase('123'));    // decimal without prefix
        $this->assertSame(123.45, NumberConverter::convertBase('123.45')); // decimal float
    }

    #[Test]
    #[TestDox('convertBase throws exceptions for invalid input')]
    public function convert_base_throws_exceptions_for_invalid_input(): void
    {
        // Empty string
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Empty string cannot be converted');
        NumberConverter::convertBase('');
    }

    #[Test]
    #[TestDox('convertBase throws exception for invalid base')]
    public function convert_base_throws_exception_for_invalid_base(): void
    {
        // Invalid base
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Base must be between 2 and 36');
        NumberConverter::convertBase('123', 1);
    }

    #[Test]
    #[TestDox('convertBase throws exception for invalid value for base')]
    public function convert_base_throws_exception_for_invalid_value_for_base(): void
    {
        // Invalid value for base
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value \'89\' is not valid for base 8');
        NumberConverter::convertBase('89', 8);
    }

    #[Test]
    #[TestDox('convertBase throws exception for auto-detect failure')]
    public function convert_base_throws_exception_for_auto_detect_failure(): void
    {
        // Cannot auto-detect invalid format
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot auto-detect base for value: invalid');
        NumberConverter::convertBase('invalid');
    }

    #[Test]
    #[TestDox('parseFloat and parseInt work correctly')]
    public function parse_float_and_parse_int_work_correctly(): void
    {
        $this->assertSame(42.5, NumberConverter::parseFloat('42.5'));
        $this->assertSame(0.0, NumberConverter::parseFloat('invalid'));

        $this->assertSame(42, NumberConverter::parseInt('42'));
        $this->assertSame(10, NumberConverter::parseInt('1010', 2));
        $this->assertSame(255, NumberConverter::parseInt('FF', 16));
        $this->assertSame(493, NumberConverter::parseInt('755', 8));
    }

    #[Test]
    #[TestDox('convertToNumber method works correctly')]
    public function convert_to_number_method_works_correctly(): void
    {
        // Successful conversions
        $this->assertSame(123, NumberConverter::convertToNumber('123'));
        $this->assertSame(45.67, NumberConverter::convertToNumber('45.67'));
        $this->assertSame(255, NumberConverter::convertToNumber('0xFF'));
        $this->assertSame(493, NumberConverter::convertToNumber('0755'));
        $this->assertSame(10, NumberConverter::convertToNumber('0b1010'));
        $this->assertSame(1000.0, NumberConverter::convertToNumber('1e3'));

        // Non-string types
        $this->assertSame(42, NumberConverter::convertToNumber(42));
        $this->assertSame(3.14, NumberConverter::convertToNumber(3.14));
        $this->assertSame(1, NumberConverter::convertToNumber(true));
        $this->assertSame(0, NumberConverter::convertToNumber(false));
        $this->assertSame(0, NumberConverter::convertToNumber(null));
    }

    #[Test]
    #[DataProvider('invalidConvertToNumberProvider')]
    #[TestDox('convertToNumber throws exceptions for invalid inputs')]
    public function convert_to_number_throws_exceptions_for_invalid_inputs(
        mixed $input,
        string $expectedExceptionMessage
    ): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        NumberConverter::convertToNumber($input);
    }

    #[Test]
    #[TestDox('convertToNumber throws exception for resource input')]
    public function convert_to_number_throws_exception_for_resource_input(): void
    {
        $resource = fopen('php://memory', 'r');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot convert resource to number');

        try {
            NumberConverter::convertToNumber($resource);
        } finally {
            // Clean up resource
            if (is_resource($resource)) {
                fclose($resource);
            }
        }
    }

    public static function invalidConvertToNumberProvider(): array
    {
        return [
            'non_numeric_string' => ['hello', 'Cannot convert non-numeric string to number: "hello"'],
            'mixed_alphanumeric_start_num' => ['123abc', 'Cannot convert non-numeric string to number: "123abc"'],
            'mixed_alphanumeric_start_alpha' => ['abc123', 'Cannot convert non-numeric string to number: "abc123"'],
            'empty_string' => ['', 'Cannot convert empty string to number'],
            'string_with_whitespace' => [' 123 ', 'Cannot convert string with whitespace to number: " 123 "'],
            'whitespace_only' => ['   ', 'Cannot convert string with whitespace to number: "   "'],
            'array_input' => [[], 'Cannot convert array to number'],
            'object_input' => [new \stdClass(), 'Cannot convert object of type stdClass to number'],
        ];
    }

    #[Test]
    #[TestDox('convertValue and convertToNumber have consistent behavior for valid inputs')]
    public function convert_value_and_convert_to_number_consistent_for_valid_inputs(): void
    {
        $validTestCases = [
            'simple_integer' => '123',
            'negative_integer' => '-456',
            'simple_float' => '45.67',
            'hex_format' => '0xFF',
            'octal_format' => '0755',
            'binary_format' => '0b1010',
            'scientific_notation' => '1e3',
            'positive_with_sign' => '+123',
        ];

        foreach ($validTestCases as $description => $value) {
            $convertValueResult = NumberConverter::convertValue($value);
            $this->assertTrue(is_numeric($convertValueResult),
                "convertValue should return numeric for valid case: $description");

            $convertToNumberResult = NumberConverter::convertToNumber($value);
            $this->assertSame($convertValueResult, $convertToNumberResult,
                "Both methods should return same result for valid case: $description");
        }
    }

    #[Test]
    #[TestDox('convertValue returns original for invalid strings while convertToNumber throws')]
    public function convert_value_returns_original_convert_to_number_throws_for_invalid(): void
    {
        $invalidTestCases = [
            'non_numeric_string' => 'hello',
            'mixed_alphanumeric' => '123abc',
            'empty_string' => '',
            'string_with_whitespace' => ' 123 ',
        ];

        foreach ($invalidTestCases as $description => $value) {
            // convertValue should return original string
            $convertValueResult = NumberConverter::convertValue($value);
            $this->assertSame($value, $convertValueResult,
                "convertValue should return original for invalid case: $description");

            // convertToNumber should throw exception
            try {
                NumberConverter::convertToNumber($value);
                $this->fail("convertToNumber should throw exception for invalid case: $description");
            } catch (InvalidArgumentException $e) {
                // Expected exception - test passes
                $this->addToAssertionCount(1);
            }
        }
    }

    #[Test]
    #[TestDox('normalize method works correctly')]
    public function normalize_method_works_correctly(): void
    {
        $this->assertSame(123, NumberConverter::normalize('123'));
        $this->assertSame(45.67, NumberConverter::normalize('45.67'));
        $this->assertSame(255, NumberConverter::normalize('0xFF'));
        $this->assertSame(1, NumberConverter::normalize(true));
        $this->assertSame(0, NumberConverter::normalize(false));
        $this->assertSame(0, NumberConverter::normalize(null));
        $this->assertSame(42, NumberConverter::normalize(42));

        $this->expectException(InvalidArgumentException::class);
        NumberConverter::normalize('invalid');
    }

    #[Test]
    #[TestDox('Locale independence test')]
    public function locale_independence_test(): void
    {
        $originalLocale = setlocale(LC_NUMERIC, 0);

        // First, test with current locale to ensure basic functionality
        $this->assertSame(123.45, NumberConverter::convertValue('123.45'),
            "Basic conversion should work with current locale");
        $this->assertSame(1000.0, NumberConverter::convertValue('1e3'),
            "Scientific notation should work with current locale");

        try {
            // Test with different locales that use comma as decimal separator
            $testLocales = ['de_DE.UTF-8', 'fr_FR.UTF-8', 'ru_RU.UTF-8', 'C'];
            $localesTested = 0;

            foreach ($testLocales as $locale) {
                if (setlocale(LC_NUMERIC, $locale) !== false) {
                    $localesTested++;

                    // Even with comma-decimal locale, dot should work
                    $this->assertSame(123.45, NumberConverter::convertValue('123.45'),
                        "Conversion should be locale-independent in locale: $locale");

                    $this->assertSame(1000.0, NumberConverter::convertValue('1e3'),
                        "Scientific notation should work in locale: $locale");
                }
            }

            // Ensure we tested at least one locale (C locale should always be available)
            $this->assertGreaterThan(0, $localesTested,
                "At least one locale should be testable (C locale should be available)");

        } finally {
            setlocale(LC_NUMERIC, $originalLocale);
        }
    }

    #[Test]
    #[TestDox('Large number handling')]
    public function large_number_handling(): void
    {
        // Test PHP_INT_MAX and beyond
        $maxInt = (string) PHP_INT_MAX;
        $this->assertSame(PHP_INT_MAX, NumberConverter::convertValue($maxInt));

        // Numbers beyond PHP_INT_MAX should become floats
        $beyondMax = '9223372036854775808'; // PHP_INT_MAX + 1 on 64-bit systems
        $result = NumberConverter::convertValue($beyondMax);
        $this->assertIsFloat($result);

        // Very large numbers in scientific notation
        $veryLarge = '1e100';
        $result = NumberConverter::convertValue($veryLarge);
        $this->assertIsFloat($result);
        $this->assertEquals(1e100, $result);
    }

    #[Test]
    #[TestDox('Special float values handling')]
    public function special_float_values_handling(): void
    {
        // Test zero values
        $this->assertSame(0, NumberConverter::convertValue('0'));
        $this->assertSame(0.0, NumberConverter::convertValue('0.0'));

        // Test negative zero
        $this->assertSame(-0.0, NumberConverter::convertValue('-0.0'));

        // Test very small numbers
        $this->assertSame(1e-10, NumberConverter::convertValue('1e-10'));
        $this->assertSame(0.0000000001, NumberConverter::convertValue('0.0000000001'));

        // Test precision limits
        $this->assertIsFloat(NumberConverter::convertValue('1.7976931348623157e+308')); // Near PHP_FLOAT_MAX
    }

    #[Test]
    #[TestDox('Edge cases for number formats')]
    public function edge_cases_for_number_formats(): void
    {
        // Hex edge cases
        $this->assertSame(0, NumberConverter::convertValue('0x0'));
        $this->assertSame(15, NumberConverter::convertValue('0xF'));
        $this->assertSame(15, NumberConverter::convertValue('0xf'));

        // Octal edge cases
        $this->assertSame(0, NumberConverter::convertValue('0o0'));
        $this->assertSame(7, NumberConverter::convertValue('0o7'));
        $this->assertSame(7, NumberConverter::convertValue('07'));

        // Binary edge cases
        $this->assertSame(0, NumberConverter::convertValue('0b0'));
        $this->assertSame(1, NumberConverter::convertValue('0b1'));
        $this->assertSame(1, NumberConverter::convertValue('0B1'));

        // Scientific notation edge cases
        $this->assertSame(0.0, NumberConverter::convertValue('0e0'));
        $this->assertSame(1.0, NumberConverter::convertValue('1e0'));
        $this->assertSame(10.0, NumberConverter::convertValue('1e1'));
        $this->assertSame(0.1, NumberConverter::convertValue('1e-1'));
    }

    #[Test]
    #[TestDox('Boundary conditions for string validation')]
    public function boundary_conditions_for_string_validation(): void
    {
        // Single character tests
        $this->assertSame(0, NumberConverter::convertValue('0'));
        $this->assertSame(9, NumberConverter::convertValue('9'));
        $this->assertSame('a', NumberConverter::convertValue('a'));

        // Leading/trailing characters that invalidate numbers
        $this->assertSame('123a', NumberConverter::convertValue('123a'));
        $this->assertSame('a123', NumberConverter::convertValue('a123'));
        $this->assertSame('12.3.4', NumberConverter::convertValue('12.3.4'));

        // Multiple decimal points
        $this->assertSame('1.2.3', NumberConverter::convertValue('1.2.3'));

        // Invalid scientific notation
        $this->assertSame('1ee', NumberConverter::convertValue('1ee'));
        $this->assertSame('e123', NumberConverter::convertValue('e123'));
        $this->assertSame('123e', NumberConverter::convertValue('123e'));

        // Special whitespace cases
        $this->assertSame("\t123\t", NumberConverter::convertValue("\t123\t"));
        $this->assertSame("\n123\n", NumberConverter::convertValue("\n123\n"));
    }

    #[Test]
    #[TestDox('getConversionInfo provides accurate metadata')]
    public function get_conversion_info_provides_accurate_metadata(): void
    {
        // Integer case
        $info = NumberConverter::getConversionInfo('123');
        $this->assertTrue($info['will_convert']);
        $this->assertEquals('123', $info['original_value']);
        $this->assertEquals('integer', $info['target_type']);
        $this->assertFalse($info['is_scientific']);
        $this->assertTrue($info['is_integer']);
        $this->assertFalse($info['is_float']);
        $this->assertFalse($info['is_hex']);
        $this->assertFalse($info['is_octal']);
        $this->assertFalse($info['is_binary']);

        // Float case
        $info = NumberConverter::getConversionInfo('45.67');
        $this->assertTrue($info['will_convert']);
        $this->assertEquals('double', $info['target_type']);
        $this->assertFalse($info['is_scientific']);
        $this->assertFalse($info['is_integer']);
        $this->assertTrue($info['is_float']);

        // Scientific notation case
        $info = NumberConverter::getConversionInfo('1e5');
        $this->assertTrue($info['will_convert']);
        $this->assertEquals('double', $info['target_type']);
        $this->assertTrue($info['is_scientific']);
        $this->assertFalse($info['is_integer']);
        $this->assertTrue($info['is_float']);

        // Hexadecimal case
        $info = NumberConverter::getConversionInfo('0xFF');
        $this->assertTrue($info['will_convert']);
        $this->assertEquals('integer', $info['target_type']);
        $this->assertFalse($info['is_scientific']);
        $this->assertTrue($info['is_integer']);
        $this->assertFalse($info['is_float']);
        $this->assertTrue($info['is_hex']);
        $this->assertFalse($info['is_octal']);
        $this->assertFalse($info['is_binary']);

        // Octal case
        $info = NumberConverter::getConversionInfo('0755');
        $this->assertTrue($info['will_convert']);
        $this->assertEquals('integer', $info['target_type']);
        $this->assertTrue($info['is_octal']);

        // Binary case
        $info = NumberConverter::getConversionInfo('0b1010');
        $this->assertTrue($info['will_convert']);
        $this->assertEquals('integer', $info['target_type']);
        $this->assertTrue($info['is_binary']);

        // Non-numeric case
        $info = NumberConverter::getConversionInfo('hello');
        $this->assertFalse($info['will_convert']);
        $this->assertEquals('string', $info['target_type']);
        $this->assertFalse($info['is_scientific']);
        $this->assertFalse($info['is_integer']);
        $this->assertFalse($info['is_float']);

        // String with whitespace case (should not convert)
        $info = NumberConverter::getConversionInfo(' 123 ');
        $this->assertFalse($info['will_convert']);
        $this->assertEquals('string', $info['target_type']);
        $this->assertFalse($info['is_scientific']);
        $this->assertFalse($info['is_integer']);
        $this->assertFalse($info['is_float']);
    }

    #[Test]
    #[TestDox('Performance test with reasonable limits')]
    public function performance_test_with_reasonable_limits(): void
    {
        $startTime = microtime(true);
        $iterations = 10000; // Reduced for more predictable test runs

        // Test conversion performance with mixed data types
        $testData = ['123', '45.67', 'hello', '0xFF', '0755', '0b1010', '', ' 123 '];

        for ($i = 0; $i < $iterations; $i++) {
            $testValue = $testData[$i % count($testData)];
            NumberConverter::convertValue($testValue);
        }

        $endTime = microtime(true);
        $elapsed = ($endTime - $startTime) * 1000; // Convert to milliseconds

        // Performance should be reasonable (under 1 second for 10k operations)
        $this->assertLessThan(1000, $elapsed,
            "Performance test took {$elapsed}ms for {$iterations} operations - may indicate performance regression");

        // Verify functionality still works after stress test
        $this->assertSame(123, NumberConverter::convertValue('123'));
        $this->assertSame(255, NumberConverter::convertValue('0xFF'));
    }

    #[Test]
    #[TestDox('Memory usage stays within bounds')]
    public function memory_usage_stays_within_bounds(): void
    {
        $memoryBefore = memory_get_usage(true);
        $iterations = 5000; // Reasonable number for memory test
        $results = [];

        // Perform many conversions and store results
        for ($i = 0; $i < $iterations; $i++) {
            $testValue = "number_$i";
            $results[] = NumberConverter::convertValue($testValue);
        }

        $memoryAfter = memory_get_usage(true);
        $memoryUsed = $memoryAfter - $memoryBefore;

        // Memory usage should be reasonable (less than 5MB for 5k operations)
        $this->assertLessThan(5 * 1024 * 1024, $memoryUsed,
            "Memory usage of {$memoryUsed} bytes for {$iterations} operations exceeds reasonable limits");

        // Verify results are correct (should all be strings since they're not numeric)
        $this->assertCount($iterations, $results);
        $this->assertSame('number_0', $results[0]);
        $this->assertSame('number_' . ($iterations - 1), $results[$iterations - 1]);

        // Clean up
        unset($results);
    }

    #[Test]
    #[TestDox('Thread safety simulation')]
    public function thread_safety_simulation(): void
    {
        // Simulate concurrent locale changes during conversion
        $originalLocale = setlocale(LC_NUMERIC, 0);

        try {
            $results = [];

            // Simulate multiple "threads" doing conversions
            for ($i = 0; $i < 100; $i++) {
                // Change locale randomly during conversions
                if ($i % 10 === 0) {
                    setlocale(LC_NUMERIC, 'C');
                }

                $result = NumberConverter::convertValue('123.45');
                $results[] = $result;

                if ($i % 15 === 0) {
                    setlocale(LC_NUMERIC, $originalLocale);
                }
            }

            // All results should be identical despite locale changes
            foreach ($results as $result) {
                $this->assertSame(123.45, $result,
                    'Conversion should be consistent despite external locale changes');
            }

        } finally {
            setlocale(LC_NUMERIC, $originalLocale);
        }
    }

    #[Test]
    #[DataProvider('typeConsistencyProvider')]
    #[TestDox('Type consistency with various inputs')]
    public function type_consistency_with_various_inputs(
        string $input,
        mixed $expectedValue,
        string $expectedType,
        string $description
    ): void {
        $result = NumberConverter::convertValue($input);

        $this->assertSame($expectedValue, $result, $description);
        $this->assertEquals($expectedType, gettype($result),
            "Type should be $expectedType for: $description");
    }

    public static function typeConsistencyProvider(): array
    {
        return [
            // [input, expected_value, expected_type, description]
            ['123', 123, 'integer', 'Simple positive integer'],
            ['-123', -123, 'integer', 'Negative integer'],
            ['0', 0, 'integer', 'Zero integer'],
            ['99.99', 99.99, 'double', 'Positive float'],
            ['-45.67', -45.67, 'double', 'Negative float'],
            ['0.0', 0.0, 'double', 'Zero float'],
            ['1.0', 1.0, 'double', 'Float that looks like integer'],
            ['000123', 123, 'integer', 'Integer with leading zeros'],
            ['123.000', 123.0, 'double', 'Float with trailing zeros'],
            ['1e5', 100000.0, 'double', 'Scientific notation - large'],
            ['1.23e-4', 0.000123, 'double', 'Scientific notation - small'],
            ['2.5e2', 250.0, 'double', 'Scientific notation - medium'],
            ['0xFF', 255, 'integer', 'Hexadecimal'],
            ['0xff', 255, 'integer', 'Hexadecimal lowercase'],
            ['0755', 493, 'integer', 'Octal traditional'],
            ['0o755', 493, 'integer', 'Octal modern'],
            ['0b1010', 10, 'integer', 'Binary'],
            ['hello', 'hello', 'string', 'Simple string'],
            ['123abc', '123abc', 'string', 'Mixed alphanumeric starting with numbers'],
            ['abc123', 'abc123', 'string', 'Mixed alphanumeric starting with letters'],
            ['', '', 'string', 'Empty string'],
            ['+123', 123, 'integer', 'Positive integer with plus sign'],
            ['+45.67', 45.67, 'double', 'Positive float with plus sign'],
            [' 123 ', ' 123 ', 'string', 'String with whitespace preserved'],
            ['   ', '   ', 'string', 'Whitespace-only string'],
        ];
    }
}