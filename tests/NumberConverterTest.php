<?php

declare(strict_types=1);

namespace Bermuda\Stdlib\Tests;

use Bermuda\Stdlib\NumberConverter;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\Group;

#[Group('number-converter')]
#[TestDox('NumberConverter utility tests')]
final class NumberConverterTest extends TestCase
{
    private ?string $performanceOutput = null;
    private ?string $memoryOutput = null;

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
        $this->assertSame(null, NumberConverter::convertValue(null));
        $this->assertSame(true, NumberConverter::convertValue(true));

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
            'mixed' => '123abc'
        ];

        $expected = [
            'id' => 123,
            'price' => 45.67,
            'name' => 'product',
            'scientific' => 1000.0,
            'mixed' => '123abc'
        ];

        $result = NumberConverter::convertArray($input);
        $this->assertEquals($expected, $result);

        // Test with numeric indices
        $numericInput = ['123', '45.67', 'hello'];
        $numericExpected = [123, 45.67, 'hello'];
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
    public function performance_test_with_many_conversions(): void
    {
        $startTime = microtime(true);
        $iterations = 100000;

        // Test conversion performance
        for ($i = 0; $i < $iterations; $i++) {
            $testString = (string)($i % 1000);
            NumberConverter::convertValue($testString);
        }

        $endTime = microtime(true);
        $elapsed = ($endTime - $startTime) * 1000; // Convert to milliseconds

        // Capture output instead of printing directly
        $output = "Performance: $iterations conversions in " . number_format($elapsed, 2) . "ms";

        // Ensure performance is reasonable (should complete in under 5 seconds)
        $this->assertLessThan(5000, $elapsed, 'Performance test took too long: ' . $output);

        // Verify we can still do conversions after stress test
        $this->assertSame(123, NumberConverter::convertValue('123'));

        // Store output for potential debugging (without printing)
        $this->performanceOutput = $output;
    }

    #[Test]
    public function memory_usage_test(): void
    {
        // Measure memory before
        $memoryBefore = memory_get_usage();

        $iterations = 30000;
        $results = [];

        // Perform many conversions and store results
        for ($i = 0; $i < $iterations; $i++) {
            $testValue = "123.$i";
            $results[] = NumberConverter::convertValue($testValue);
        }

        // Measure memory after
        $memoryAfter = memory_get_usage();
        $memoryUsed = $memoryAfter - $memoryBefore;
        $memoryUsedKB = round($memoryUsed / 1024, 2);

        // Capture output instead of printing directly
        $output = "Memory usage: $memoryUsedKB KB for $iterations conversions";

        // Memory usage should be reasonable (less than 10MB for 30k conversions)
        $this->assertLessThan(10 * 1024 * 1024, $memoryUsed, 'Memory usage too high: ' . $output);

        // Verify results are correct
        $this->assertCount($iterations, $results);
        $this->assertEquals(123.0, $results[0]); // First result should be 123.0

        // Store output for potential debugging (without printing)
        $this->memoryOutput = $output;

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
