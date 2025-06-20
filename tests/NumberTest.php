<?php

declare(strict_types=1);

namespace Bermuda\Stdlib\Tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use Bermuda\Stdlib\Number;
use InvalidArgumentException;
use ArithmeticError;

final class NumberTest extends TestCase
{
    #[Test]
    public function constructor_creates_number_with_default_value(): void
    {
        $number = new Number();
        $this->assertSame(0, $number->value);
    }

    #[Test]
    public function constructor_creates_number_with_given_value(): void
    {
        $number = new Number(42);
        $this->assertSame(42, $number->value);

        $number = new Number(3.14);
        $this->assertSame(3.14, $number->value);
    }

    #[Test]
    public function from_creates_number_from_various_types(): void
    {
        $this->assertSame(42, Number::from(42)->value);
        $this->assertSame(3.14, Number::from(3.14)->value);

        // Strings without prefix are treated as decimal numbers
        $this->assertSame(123, Number::from('123')->value);
        $this->assertSame(755, Number::from('755')->value);
        $this->assertSame(123.45, Number::from('123.45')->value);

        $this->assertSame(1, Number::from(true)->value);
        $this->assertSame(0, Number::from(false)->value);
        $this->assertSame(0, Number::from(null)->value);
    }

    #[Test]
    public function from_handles_hex_and_octal_strings(): void
    {
        // Hexadecimal format with 0x prefix
        $this->assertSame(255, Number::from('0xFF')->value);
        $this->assertSame(255, Number::from('0xff')->value);

        // Octal format with 0 or 0o prefix
        $this->assertSame(493, Number::from('0755')->value);
        $this->assertSame(493, Number::from('0o755')->value);
    }

    #[Test]
    public function from_throws_exception_for_invalid_input(): void
    {
        // Strings with letters but no proper prefix are invalid
        $this->expectException(InvalidArgumentException::class);
        Number::from('FF');
    }

    #[Test]
    public function from_throws_exception_for_invalid_strings(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Number::from('invalid');
    }

    #[Test]
    public function mathematical_constants_are_correct(): void
    {
        $this->assertSame(M_PI, Number::PI);
        $this->assertSame(M_E, Number::E);
        $this->assertSame(1.618033988749, Number::GOLDEN_RATIO);
        $this->assertSame(0.5772156649015329, Number::EULER_GAMMA);
    }

    #[Test]
    public function check_integer_validates_correctly(): void
    {
        $this->assertTrue(Number::checkInteger(42));
        $this->assertTrue(Number::checkInteger(42.0));
        $this->assertFalse(Number::checkInteger(42.5));
        $this->assertFalse(Number::checkInteger('42'));
    }

    #[Test]
    public function check_finite_validates_correctly(): void
    {
        $this->assertTrue(Number::checkFinite(42));
        $this->assertTrue(Number::checkFinite(42.5));
        $this->assertTrue(Number::checkFinite('42'));
        $this->assertFalse(Number::checkFinite(INF));
        $this->assertFalse(Number::checkFinite(-INF));
        $this->assertFalse(Number::checkFinite(NAN));
    }

    #[Test]
    public function check_nan_validates_correctly(): void
    {
        $this->assertTrue(Number::checkNaN(NAN));
        $this->assertFalse(Number::checkNaN(42));
        $this->assertFalse(Number::checkNaN(42.5));
        $this->assertFalse(Number::checkNaN(INF));
    }

    #[Test]
    public function parse_float_converts_string_to_float(): void
    {
        $this->assertSame(42.5, Number::parseFloat('42.5'));
        $this->assertSame(0.0, Number::parseFloat('invalid'));
    }

    #[Test]
    public function parse_int_converts_string_with_radix(): void
    {
        $this->assertSame(42, Number::parseInt('42'));
        $this->assertSame(10, Number::parseInt('1010', 2));
        $this->assertSame(255, Number::parseInt('FF', 16));
        $this->assertSame(493, Number::parseInt('755', 8));
    }

    #[Test]
    public function safe_integer_limits_are_correct(): void
    {
        $this->assertSame(PHP_INT_MAX, Number::maxSafeInteger());
        $this->assertSame(PHP_INT_MIN, Number::minSafeInteger());
    }

    #[Test]
    public function is_prime_checks_correctly(): void
    {
        $this->assertFalse(Number::isPrime(1));
        $this->assertTrue(Number::isPrime(2));
        $this->assertTrue(Number::isPrime(3));
        $this->assertFalse(Number::isPrime(4));
        $this->assertTrue(Number::isPrime(5));
        $this->assertTrue(Number::isPrime(17));
        $this->assertFalse(Number::isPrime(18));
        $this->assertTrue(Number::isPrime(Number::from(23)));
    }

    #[Test]
    public function factorial_calculates_correctly(): void
    {
        $this->assertSame(1, Number::factorial(0)->value);
        $this->assertSame(1, Number::factorial(1)->value);
        $this->assertSame(2, Number::factorial(2)->value);
        $this->assertSame(6, Number::factorial(3)->value);
        $this->assertSame(24, Number::factorial(4)->value);
        $this->assertSame(120, Number::factorial(5)->value);
        $this->assertSame(120, Number::factorial(Number::from(5))->value);
    }

    #[Test]
    public function factorial_throws_exception_for_negative(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Number::factorial(-1);
    }

    #[Test]
    public function fibonacci_calculates_correctly(): void
    {
        $this->assertSame(0, Number::fibonacci(0)->value);
        $this->assertSame(1, Number::fibonacci(1)->value);
        $this->assertSame(1, Number::fibonacci(2)->value);
        $this->assertSame(2, Number::fibonacci(3)->value);
        $this->assertSame(3, Number::fibonacci(4)->value);
        $this->assertSame(5, Number::fibonacci(5)->value);
        $this->assertSame(55, Number::fibonacci(10)->value);
    }

    #[Test]
    public function fibonacci_throws_exception_for_negative(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Number::fibonacci(-1);
    }

    #[Test]
    public function gcd_calculates_correctly(): void
    {
        $this->assertSame(6, Number::gcd(48, 18)->value);
        $this->assertSame(1, Number::gcd(17, 13)->value);
        $this->assertSame(10, Number::gcd(10, 0)->value);
        $this->assertSame(5, Number::gcd(Number::from(15), Number::from(10))->value);
    }

    #[Test]
    public function lcm_calculates_correctly(): void
    {
        $this->assertSame(144, Number::lcm(48, 18)->value);
        $this->assertSame(0, Number::lcm(0, 5)->value);
        $this->assertSame(30, Number::lcm(Number::from(15), Number::from(10))->value);
    }

    #[Test]
    public function is_perfect_checks_correctly(): void
    {
        $this->assertFalse(Number::isPerfect(1));
        $this->assertTrue(Number::isPerfect(6));
        $this->assertTrue(Number::isPerfect(28));
        $this->assertFalse(Number::isPerfect(12));
        $this->assertTrue(Number::isPerfect(Number::from(6)));
    }

    #[Test]
    public function degrees_radians_conversion_works(): void
    {
        $radians = Number::degreesToRadians(180);
        $this->assertEqualsWithDelta(M_PI, $radians->value, 0.0001);

        $degrees = Number::radiansToDegrees(M_PI);
        $this->assertEqualsWithDelta(180, $degrees->value, 0.0001);
    }

    #[Test]
    public function distance_2d_calculates_correctly(): void
    {
        $distance = Number::distance2D(0, 0, 3, 4);
        $this->assertSame(5.0, $distance->value);

        $distance = Number::distance2D(Number::from(1), Number::from(1), Number::from(4), Number::from(5));
        $this->assertSame(5.0, $distance->value);
    }

    #[Test]
    public function distance_3d_calculates_correctly(): void
    {
        $distance = Number::distance3D(0, 0, 0, 1, 1, 1);
        $this->assertEqualsWithDelta(sqrt(3), $distance->value, 0.0001);
    }

    #[Test]
    public function lerp_interpolates_correctly(): void
    {
        $result = Number::lerp(0, 10, 0.5);
        $this->assertSame(5.0, $result->value);

        $result = Number::lerp(Number::from(10), Number::from(20), Number::from(0.25));
        $this->assertSame(12.5, $result->value);
    }

    #[Test]
    public function map_projects_values_correctly(): void
    {
        $result = Number::map(5, 0, 10, 0, 100);
        $this->assertSame(50.0, $result->value);

        $result = Number::map(Number::from(2), Number::from(0), Number::from(4), Number::from(10), Number::from(20));
        $this->assertSame(15.0, $result->value);
    }

    #[Test]
    public function range_generates_correct_sequences(): void
    {
        $range = Number::range(1, 5);
        $values = array_map(fn($n) => $n->value, $range);
        $this->assertSame([1, 2, 3, 4, 5], $values);

        $range = Number::range(0, 10, 2);
        $values = array_map(fn($n) => $n->value, $range);
        $this->assertSame([0, 2, 4, 6, 8, 10], $values);

        $range = Number::range(5, 1, -1);
        $values = array_map(fn($n) => $n->value, $range);
        $this->assertSame([5, 4, 3, 2, 1], $values);
    }

    #[Test]
    public function range_throws_exception_for_zero_step(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Number::range(1, 5, 0);
    }

    #[Test]
    public function statistical_functions_work_correctly(): void
    {
        $numbers = [1, 2, 3, 4, 5];

        $mean = Number::mean($numbers);
        $this->assertSame(3, $mean->value);
        $this->assertIsInt($mean->value);

        $median = Number::median($numbers);
        $this->assertSame(3, $median->value);
        $this->assertIsInt($median->value); // Odd count preserves middle element type

        $mode = Number::mode([1, 2, 2, 3]);
        $this->assertSame(2.0, $mode->value);

        $stdDev = Number::standardDeviation($numbers);
        $this->assertEqualsWithDelta(1.58, $stdDev->value, 0.01);
    }

    #[Test]
    public function mean_returns_appropriate_types(): void
    {
        // Integer inputs that result in float due to division
        $meanFloat = Number::mean([1, 2, 4]);
        $this->assertSame(7/3, $meanFloat->value);
        $this->assertIsFloat($meanFloat->value);

        // Integer inputs that result in integer due to exact division
        $meanInt = Number::mean([2, 4, 6]);
        $this->assertSame(4, $meanInt->value);
        $this->assertIsInt($meanInt->value);

        // Float inputs always result in float
        $meanFloatInput = Number::mean([1.5, 2.5, 3.0]);
        $this->assertSame(7.0/3, $meanFloatInput->value);
        $this->assertIsFloat($meanFloatInput->value);
    }

    #[Test]
    public function median_returns_appropriate_types(): void
    {
        // Odd count: preserves type of middle element
        $medianOddInt = Number::median([1, 2, 3, 4, 5]);
        $this->assertSame(3, $medianOddInt->value);
        $this->assertIsInt($medianOddInt->value);

        $medianOddFloat = Number::median([1.0, 2.0, 3.0, 4.0, 5.0]);
        $this->assertSame(3.0, $medianOddFloat->value);
        $this->assertIsFloat($medianOddFloat->value);

        // Even count: always returns float (average of two middle values)
        $medianEvenInt = Number::median([1, 2, 3, 4]);
        $this->assertSame(2.5, $medianEvenInt->value);
        $this->assertIsFloat($medianEvenInt->value);

        $medianEvenFloat = Number::median([1.0, 2.0, 3.0, 4.0]);
        $this->assertSame(2.5, $medianEvenFloat->value);
        $this->assertIsFloat($medianEvenFloat->value);
    }

    #[Test]
    public function statistical_functions_throw_exceptions_for_invalid_input(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Number::mean([]);
    }

    #[Test]
    public function random_generates_values_in_range(): void
    {
        $random = Number::random(10, 20);
        $this->assertGreaterThanOrEqual(10, $random->value);
        $this->assertLessThanOrEqual(20, $random->value);

        $randomInt = Number::randomInt(5, 15);
        $this->assertGreaterThanOrEqual(5, $randomInt->value);
        $this->assertLessThanOrEqual(15, $randomInt->value);
        $this->assertTrue($randomInt->isInteger());
    }

    #[Test]
    public function arithmetic_operations_work_correctly(): void
    {
        $num = Number::from(10);

        $this->assertSame(15, $num->add(5)->value);
        $this->assertSame(5, $num->subtract(5)->value);
        $this->assertSame(50, $num->multiply(5)->value);
        $this->assertSame(2, $num->divide(5)->value);
        $this->assertSame(1, $num->mod(3)->value);
        $this->assertSame(100, $num->power(2)->value);
        $this->assertSame(10, $num->abs()->value);

        $this->assertSame(10, Number::from(-10)->abs()->value);
    }

    #[Test]
    public function division_by_zero_throws_exception(): void
    {
        $num = Number::from(10);

        $this->expectException(ArithmeticError::class);
        $num->divide(0);
    }

    #[Test]
    public function mathematical_functions_work_correctly(): void
    {
        $num = Number::from(16);

        $this->assertSame(4.0, $num->sqrt()->value);
        $this->assertEqualsWithDelta(2.52, $num->cbrt()->value, 0.01);
        $this->assertEqualsWithDelta(log(16), $num->log()->value, 0.0001);
        $this->assertEqualsWithDelta(log10(16), $num->log10()->value, 0.0001);
        $this->assertSame(4.0, $num->log2()->value);

        $angle = Number::from(M_PI / 2);
        $this->assertEqualsWithDelta(1, $angle->sin()->value, 0.0001);
        $this->assertEqualsWithDelta(0, $angle->cos()->value, 0.0001);
    }

    #[Test]
    public function rounding_functions_work_correctly(): void
    {
        $num = Number::from(3.7);

        $this->assertSame(4.0, $num->ceil()->value);
        $this->assertSame(3.0, $num->floor()->value);
        $this->assertSame(4.0, $num->round()->value);
        $this->assertSame(3, $num->trunc()->value);
        $this->assertSame(1, $num->sign());

        $this->assertSame(-1, Number::from(-5)->sign());
        $this->assertSame(0, Number::from(0)->sign());
    }

    #[Test]
    public function comparison_methods_work_correctly(): void
    {
        $a = Number::from(10);
        $b = Number::from(20);

        $this->assertTrue($a->equals(10));
        $this->assertFalse($a->equals(10.0, true)); // strict comparison
        $this->assertTrue($a->strictEquals(Number::from(10)));
        $this->assertSame(-1, $a->compare($b));
        $this->assertTrue($a->lessThan($b));
        $this->assertFalse($a->greaterThan($b));
        $this->assertTrue($a->lessThanOrEqual(10));
        $this->assertTrue($a->greaterThanOrEqual(10));
    }

    #[Test]
    public function utility_methods_work_correctly(): void
    {
        $a = Number::from(10);
        $b = Number::from(20);

        $this->assertSame(20, $a->max($b)->value);
        $this->assertSame(10, $a->min($b)->value);
        $this->assertSame(15, $a->clamp(15, 25)->value);
        $this->assertSame(1.0, $a->percent(10)->value); // 10% of 10 = 1.0
        $this->assertSame(50.0, $a->percentOf(20)->value); // 10 is 50% of 20
    }

    #[Test]
    public function type_checking_methods_work_correctly(): void
    {
        $int = Number::from(42);
        $float = Number::from(42.5);

        $this->assertTrue($int->isInteger());
        $this->assertFalse($int->isFloat());
        $this->assertFalse($float->isInteger());
        $this->assertTrue($float->isFloat());

        $this->assertTrue($int->isFinite());
        $this->assertFalse($int->isNaN());

        $this->assertTrue($int->isPositive());
        $this->assertFalse($int->isNegative());
        $this->assertFalse($int->isZero());
        $this->assertTrue($int->isEven());
        $this->assertFalse($int->isOdd());

        $this->assertTrue(Number::from(43)->isOdd());
        $this->assertTrue(Number::from(0)->isZero());
        $this->assertTrue(Number::from(-5)->isNegative());
    }

    #[Test]
    public function formatting_methods_work_correctly(): void
    {
        $num = Number::from(1234.567);

        $this->assertSame('1234.57', $num->toFixed(2));
        $this->assertSame('1.23e+3', $num->toExponential(2));
        $this->assertSame('1234.567', $num->toString());
        $this->assertSame('1,234.57', $num->format(2));
    }

    #[Test]
    public function conversion_methods_work_correctly(): void
    {
        $num = Number::from(255);

        $this->assertSame(255, $num->toInt());
        $this->assertSame(255.0, $num->toFloat());
        $this->assertSame(255, $num->toNumber());
        $this->assertSame('ff', $num->toHex());
        $this->assertSame('377', $num->toOctal());
        $this->assertSame('11111111', $num->toBinary());
        $this->assertSame('zz', Number::from(1295)->toBase(36));
    }

    #[Test]
    public function base_conversion_throws_exception_for_invalid_radix(): void
    {
        $num = Number::from(255);

        $this->expectException(InvalidArgumentException::class);
        $num->toBase(1);
    }

    #[Test]
    public function string_conversion_works(): void
    {
        $num = Number::from(42);
        $this->assertSame('42', (string)$num);
        $this->assertSame('42', $num->__toString());
    }

    #[Test]
    public function json_serialization_works(): void
    {
        $num = Number::from(42.5);
        $this->assertSame(42.5, $num->jsonSerialize());
        $this->assertSame('42.5', json_encode($num));
    }

    #[Test]
    public function is_hex_validates_correctly(): void
    {
        // Hexadecimal numbers require 0x prefix
        $this->assertTrue(Number::isHex('0xFF'));
        $this->assertTrue(Number::isHex('0xff'));
        $this->assertTrue(Number::isHex('0x1A2B'));
        $this->assertTrue(Number::isHex('0xDEADBEEF'));

        // Without prefix, not considered hexadecimal
        $this->assertFalse(Number::isHex('FF'));
        $this->assertFalse(Number::isHex('ABC'));
        $this->assertFalse(Number::isHex('123'));
        $this->assertFalse(Number::isHex('755'));

        // Invalid cases
        $this->assertFalse(Number::isHex('0x'));
        $this->assertFalse(Number::isHex('0xGHI'));
        $this->assertFalse(Number::isHex(''));
        $this->assertFalse(Number::isHex(255));
    }

    #[Test]
    public function is_octal_validates_correctly(): void
    {
        // Octal numbers require explicit prefix
        $this->assertTrue(Number::isOctal('0755'));   // traditional notation
        $this->assertTrue(Number::isOctal('0123'));   // traditional notation
        $this->assertTrue(Number::isOctal('0007'));   // traditional notation with leading zeros
        $this->assertTrue(Number::isOctal('0o755'));  // modern notation
        $this->assertTrue(Number::isOctal('0O755'));  // modern notation

        // Without prefix, treated as decimal numbers
        $this->assertFalse(Number::isOctal('755'));
        $this->assertFalse(Number::isOctal('123'));
        $this->assertFalse(Number::isOctal('7'));

        // Invalid cases
        $this->assertFalse(Number::isOctal('0789')); // 8,9 are not octal digits
        $this->assertFalse(Number::isOctal('0o'));   // empty string after prefix
        $this->assertFalse(Number::isOctal(''));     // empty string
        $this->assertFalse(Number::isOctal(493));    // not a string
    }

    #[Test]
    public function is_binary_validates_correctly(): void
    {
        // Binary numbers require 0b prefix
        $this->assertTrue(Number::isBinary('0b1010'));
        $this->assertTrue(Number::isBinary('0B1010'));
        $this->assertTrue(Number::isBinary('0b0011'));

        // Without prefix, not considered binary
        $this->assertFalse(Number::isBinary('1010'));
        $this->assertFalse(Number::isBinary('0011'));
        $this->assertFalse(Number::isBinary('101'));

        // Invalid cases
        $this->assertFalse(Number::isBinary('0b1012')); // 2 is not a binary digit
        $this->assertFalse(Number::isBinary('0b'));     // empty string after prefix
        $this->assertFalse(Number::isBinary(''));       // empty string
        $this->assertFalse(Number::isBinary(10));       // not a string
    }

    #[Test]
    public function is_base_validates_correctly(): void
    {
        $this->assertTrue(Number::isBase('1010', 2));
        $this->assertTrue(Number::isBase('755', 8));
        $this->assertTrue(Number::isBase('FF', 16));
        $this->assertTrue(Number::isBase('ff', 16)); // lowercase
        $this->assertTrue(Number::isBase('ZZ', 36));
        $this->assertTrue(Number::isBase('zz', 36)); // lowercase
        $this->assertTrue(Number::isBase('A', 11)); // single character
        $this->assertTrue(Number::isBase('123', 10));
        $this->assertFalse(Number::isBase('89', 8)); // 8,9 not valid in octal
        $this->assertFalse(Number::isBase('GG', 16)); // G not valid in hex
        $this->assertFalse(Number::isBase('', 10)); // empty string
        $this->assertFalse(Number::isBase('2', 2)); // 2 not valid in binary
        $this->assertFalse(Number::isBase(123, 10)); // not a string
    }

    #[Test]
    public function is_base_throws_exception_for_invalid_radix(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Number::isBase('123', 1);
    }

    #[Test]
    public function convert_base_works_correctly(): void
    {
        $this->assertSame(255, Number::convertBase('FF', 16)->value);
        $this->assertSame(10, Number::convertBase('1010', 2)->value);
        $this->assertSame(493, Number::convertBase('755', 8)->value);
        $this->assertSame(1295, Number::convertBase('ZZ', 36)->value);
    }

    #[Test]
    public function convert_base_auto_detects_format(): void
    {
        // Auto-detection based on prefixes
        $this->assertSame(255, Number::convertBase('0xFF')->value);   // hexadecimal with prefix
        $this->assertSame(493, Number::convertBase('0755')->value);   // octal with prefix
        $this->assertSame(493, Number::convertBase('0o755')->value);  // modern octal
        $this->assertSame(10, Number::convertBase('0b1010')->value);  // binary with prefix
        $this->assertSame(123, Number::convertBase('123')->value);    // decimal without prefix
    }

    #[Test]
    public function convert_base_throws_exceptions_for_invalid_input(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Number::convertBase('');
    }

    #[Test]
    public function format_number_works_correctly(): void
    {
        $result = Number::formatNumber(1234.56, 2, '.', ',', '$', ' USD');
        $this->assertSame('$1,234.56 USD', $result);

        $result = Number::formatNumber(Number::from(1000), 0, '.', ' ');
        $this->assertSame('1 000', $result);
    }

    #[Test]
    public function immutability_is_maintained(): void
    {
        $original = Number::from(10);
        $result = $original->add(5);

        $this->assertSame(10, $original->value);
        $this->assertSame(15, $result->value);
        $this->assertNotSame($original, $result);
    }

    #[Test]
    public function method_chaining_works_correctly(): void
    {
        $result = Number::from(10)
            ->add(5)
            ->multiply(2)
            ->subtract(5)
            ->divide(5);

        $this->assertSame(5, $result->value);
    }

    public static function invalidNormalizeValueProvider(): array
    {
        return [
            [[]],
            [new \stdClass()],
            [fopen('php://memory', 'r')],
        ];
    }

    #[Test]
    #[DataProvider('invalidNormalizeValueProvider')]
    public function normalize_throws_exception_for_invalid_types(mixed $value): void
    {
        $this->expectException(InvalidArgumentException::class);
        Number::from($value);
    }

    #[Test]
    public function midrange_calculates_correctly(): void
    {
        // Basic midrange calculation
        $midrange = Number::midrange([1, 5]);
        $this->assertSame(3, $midrange->value); // (1+5)/2 = 3 (exact division = int)

        // Multiple values
        $midrange = Number::midrange([1, 2, 3, 4, 5]);
        $this->assertSame(3, $midrange->value); // (1+5)/2 = 3 (exact division = int)

        // With duplicates
        $midrange = Number::midrange([2, 2, 2, 2]);
        $this->assertSame(2, $midrange->value); // (2+2)/2 = 2 (exact division = int)

        // Negative numbers
        $midrange = Number::midrange([-10, -5, 0, 5, 10]);
        $this->assertSame(0, $midrange->value); // (-10+10)/2 = 0 (exact division = int)
    }

    #[Test]
    public function midrange_works_with_single_element(): void
    {
        $midrange = Number::midrange([42]);
        $this->assertSame(42, $midrange->value); // (42+42)/2 = 42 (exact division = int)
    }

    #[Test]
    public function midrange_works_with_number_objects(): void
    {
        $numbers = [
            Number::from(10),
            Number::from(20),
            Number::from(30)
        ];

        $midrange = Number::midrange($numbers);
        $this->assertSame(20, $midrange->value); // (10+30)/2 = 20 (exact division = int)
    }

    #[Test]
    public function midrange_works_with_mixed_types(): void
    {
        $numbers = [1, 2.5, Number::from(8), 4];

        $midrange = Number::midrange($numbers);
        $this->assertSame(4.5, $midrange->value); // (1+8)/2 = 4.5
    }

    #[Test]
    public function midrange_returns_appropriate_types(): void
    {
        // Integer range with integer result (exact division)
        $intMidrange = Number::midrange([2, 6]);
        $this->assertSame(4, $intMidrange->value);
        $this->assertIsInt($intMidrange->value);

        // Integer range with float result (inexact division)
        $floatMidrange = Number::midrange([1, 4]);
        $this->assertSame(2.5, $floatMidrange->value);
        $this->assertIsFloat($floatMidrange->value);

        // Float input produces result based on division
        $floatInput = Number::midrange([1.0, 3.0]);
        $this->assertSame(2.0, $floatInput->value);
        $this->assertIsFloat($floatInput->value); // Float input typically produces float
    }

    #[Test]
    public function midrange_handles_large_ranges(): void
    {
        // Large positive range
        $midrange = Number::midrange([1000000, 9000000]);
        $this->assertSame(5000000, $midrange->value); // (1000000+9000000)/2 = 5000000 (exact)

        // Large negative range
        $midrange = Number::midrange([-1000000, -100000]);
        $this->assertSame(-550000, $midrange->value); // (-1000000+-100000)/2 = -550000 (exact)

        // Mixed large range
        $midrange = Number::midrange([-1000000, 1000000]);
        $this->assertSame(0, $midrange->value); // (-1000000+1000000)/2 = 0 (exact)
    }

    #[Test]
    public function midrange_throws_exception_for_empty_array(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot calculate midrange of empty array');
        Number::midrange([]);
    }

    #[Test]
    public function midrange_comparison_with_other_measures(): void
    {
        $data = [1, 2, 2, 3, 100]; // Dataset with outlier

        $mean = Number::mean($data);         // (1+2+2+3+100)/5 = 21.6
        $median = Number::median($data);     // 2 (middle value)
        $midrange = Number::midrange($data); // (1+100)/2 = 50.5

        $this->assertSame(21.6, $mean->value);
        $this->assertSame(2, $median->value);
        $this->assertSame(50.5, $midrange->value); // This will be float due to inexact division

        // Midrange is most affected by outliers
        $this->assertTrue($midrange->value > $mean->value);
        $this->assertTrue($mean->value > $median->value);
    }

    #[Test]
    public function midrange_with_decimal_precision(): void
    {
        // Test precision with decimal values
        $midrange = Number::midrange([1.1, 2.9]);
        $this->assertSame(2.0, $midrange->value);
        $this->assertIsFloat($midrange->value); // Float input produces float

        $midrange = Number::midrange([0.1, 0.9]);
        $this->assertSame(0.5, $midrange->value);
        $this->assertIsFloat($midrange->value); // Float input produces float

        // Test with repeating decimals
        $midrange = Number::midrange([1, 2]);
        $this->assertSame(1.5, $midrange->value);
        $this->assertIsFloat($midrange->value); // Inexact division produces float
    }
}
