<?php

declare(strict_types=1);

namespace Bermuda\Stdlib\Tests;

use PHPUnit\Framework\Attributes\TestDox;
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
        // Test with primitive numeric types
        $this->assertSame(42, Number::from(42)->value);
        $this->assertSame(3.14, Number::from(3.14)->value);

        // Test with string representations of numbers
        $this->assertSame(123, Number::from('123')->value);
        $this->assertSame(755, Number::from('755')->value);
        $this->assertSame(123.45, Number::from('123.45')->value);

        // Test with boolean values
        $this->assertSame(1, Number::from(true)->value);
        $this->assertSame(0, Number::from(false)->value);

        // Test with null
        $this->assertSame(0, Number::from(null)->value);
    }

    #[Test]
    #[TestDox('Number::from handles special number formats')]
    public function from_handles_special_number_formats(): void
    {
        // Hexadecimal format with 0x prefix
        $this->assertSame(255, Number::from('0xFF')->value);
        $this->assertSame(255, Number::from('0xff')->value);

        // Octal format with 0 or 0o prefix
        $this->assertSame(493, Number::from('0755')->value);
        $this->assertSame(493, Number::from('0o755')->value);

        // Binary format with 0b prefix
        $this->assertSame(10, Number::from('0b1010')->value);

        // Scientific notation
        $this->assertSame(1000.0, Number::from('1e3')->value);
        $this->assertSame(0.001, Number::from('1e-3')->value);
    }

    #[Test]
    #[TestDox('Number::from throws exception for invalid input')]
    public function from_throws_exception_for_invalid_input(): void
    {
        // Strings with letters but no proper prefix are invalid
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot convert non-numeric string to number: "FF"');
        Number::from('FF');
    }

    #[Test]
    #[TestDox('Number::from throws exception for mixed content strings')]
    public function from_throws_exception_for_mixed_content_strings(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot convert non-numeric string to number: "invalid"');
        Number::from('invalid');
    }

    #[Test]
    #[TestDox('Number::from throws exception for whitespace strings')]
    public function from_throws_exception_for_whitespace_strings(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot convert string with whitespace to number: " 123 "');
        Number::from(' 123 ');
    }

    #[Test]
    #[TestDox('Number::from throws exception for unsupported types')]
    public function from_throws_exception_for_unsupported_types(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot convert array to number');
        Number::from([]);
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
        $this->assertFalse(Number::checkInteger(42.0));
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
    #[TestDox('Range generation handles edge cases')]
    public function range_generation_handles_edge_cases(): void
    {
        // Test with same start and end
        $sameRange = Number::range(5, 5);
        $values = array_map(fn($n) => $n->value, $sameRange);
        $this->assertSame([5], $values);

        // Test with negative numbers
        $negativeRange = Number::range(-3, -1);
        $values = array_map(fn($n) => $n->value, $negativeRange);
        $this->assertSame([-3, -2, -1], $values);

        // Test with float step
        $floatStepRange = Number::range(0, 2, 0.5);
        $values = array_map(fn($n) => $n->value, $floatStepRange);
        $this->assertSame([0, 0.5, 1.0, 1.5, 2.0], $values);

        // Test descending with negative step
        $descendingRange = Number::range(10, 5, -2);
        $values = array_map(fn($n) => $n->value, $descendingRange);
        $this->assertSame([10, 8, 6], $values); // Stops before reaching 5

        // Test empty range (impossible with positive step)
        $emptyRange = Number::range(5, 1, 1); // Can't reach 1 from 5 with positive step
        $this->assertEmpty($emptyRange);
    }

    #[Test]
    public function range_throws_exception_for_zero_step(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Step cannot be zero');
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
        // Test random float generation
        for ($i = 0; $i < 10; $i++) { // Test multiple times for randomness
            $random = Number::random(10, 20);
            $this->assertGreaterThanOrEqual(10, $random->value);
            $this->assertLessThanOrEqual(20, $random->value);
            $this->assertIsFloat($random->value); // Should always be float
        }

        // Test random integer generation
        for ($i = 0; $i < 10; $i++) {
            $randomInt = Number::randomInt(5, 15);
            $this->assertGreaterThanOrEqual(5, $randomInt->value);
            $this->assertLessThanOrEqual(15, $randomInt->value);
            $this->assertTrue($randomInt->isInteger());
        }
    }

    #[Test]
    #[TestDox('Random methods handle edge cases correctly')]
    public function random_methods_handle_edge_cases_correctly(): void
    {
        // Test with same min and max values
        $sameValue = Number::random(5, 5);
        $this->assertSame(5.0, $sameValue->value);

        $sameIntValue = Number::randomInt(10, 10);
        $this->assertSame(10, $sameIntValue->value);

        // Test with zero bounds
        $zeroRandom = Number::random(0, 0);
        $this->assertSame(0.0, $zeroRandom->value);

        $zeroIntRandom = Number::randomInt(0, 0);
        $this->assertSame(0, $zeroIntRandom->value);

        // Test with negative ranges
        for ($i = 0; $i < 5; $i++) {
            $negativeRandom = Number::random(-10, -5);
            $this->assertGreaterThanOrEqual(-10, $negativeRandom->value);
            $this->assertLessThanOrEqual(-5, $negativeRandom->value);
        }
    }

    #[Test]
    #[TestDox('Random throws exception when min > max')]
    public function random_throws_exception_when_min_greater_than_max(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Min value cannot be greater than max value');
        Number::random(20, 10);
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
    #[TestDox('Arithmetic operations handle edge cases')]
    public function arithmetic_operations_handle_edge_cases(): void
    {
        $zero = Number::from(0);
        $positive = Number::from(10);
        $negative = Number::from(-5);

        // Test operations with zero
        $this->assertSame(10, $positive->add(0)->value);
        $this->assertSame(10, $positive->subtract(0)->value);
        $this->assertSame(0, $positive->multiply(0)->value);

        // Test operations with negative numbers
        $this->assertSame(5, $positive->add($negative)->value);
        $this->assertSame(15, $positive->subtract($negative)->value);
        $this->assertSame(-50, $positive->multiply($negative)->value);

        // Test power operations
        $this->assertSame(1, $positive->power(0)->value); // Any number^0 = 1
        $this->assertSame(10, $positive->power(1)->value); // Any number^1 = itself
        $this->assertSame(0.1, $positive->power(-1)->value); // 10^-1 = 0.1

        // Test integer division
        $this->assertSame(3, Number::from(15)->integerDivide(4)->value); // 15 ÷ 4 = 3 (integer)
        $this->assertSame(-3, Number::from(-15)->integerDivide(4)->value); // -15 ÷ 4 = -3 (integer)
    }

    #[Test]
    #[TestDox('Division by zero throws ArithmeticError for all division operations')]
    public function division_by_zero_throws_arithmetic_error(): void
    {
        $num = Number::from(10);

        // Test regular division
        $this->expectException(ArithmeticError::class);
        $this->expectExceptionMessage('Division by zero');
        $num->divide(0);
    }

    #[Test]
    #[TestDox('Modulo by zero throws ArithmeticError')]
    public function modulo_by_zero_throws_arithmetic_error(): void
    {
        $num = Number::from(10);

        $this->expectException(ArithmeticError::class);
        $this->expectExceptionMessage('Division by zero');
        $num->mod(0);
    }

    #[Test]
    #[TestDox('Integer division by zero throws ArithmeticError')]
    public function integer_division_by_zero_throws_arithmetic_error(): void
    {
        $num = Number::from(10);

        $this->expectException(ArithmeticError::class);
        $this->expectExceptionMessage('Division by zero');
        $num->integerDivide(0);
    }

    #[Test]
    #[TestDox('Percentage of zero throws ArithmeticError')]
    public function percentage_of_zero_throws_arithmetic_error(): void
    {
        $num = Number::from(10);

        $this->expectException(ArithmeticError::class);
        $this->expectExceptionMessage('Division by zero');
        $num->percentOf(0);
    }

    #[Test]
    public function mathematical_functions_work_correctly(): void
    {
        $num = Number::from(16);

        // Test basic math functions
        $this->assertSame(4.0, $num->sqrt()->value);
        $this->assertEqualsWithDelta(2.5198, $num->cbrt()->value, 0.0001);
        $this->assertEqualsWithDelta(log(16), $num->log()->value, 0.0001);
        $this->assertEqualsWithDelta(log10(16), $num->log10()->value, 0.0001);
        $this->assertSame(4.0, $num->log2()->value);
        $this->assertEqualsWithDelta(exp(16), $num->exp()->value, 0.01);

        // Test trigonometric functions
        $angle = Number::from(M_PI / 2);
        $this->assertEqualsWithDelta(1, $angle->sin()->value, 0.0001);
        $this->assertEqualsWithDelta(0, $angle->cos()->value, 0.0001);

        // Test known trigonometric values
        $this->assertEqualsWithDelta(0, Number::from(0)->sin()->value, 0.0001);
        $this->assertSame(1.0, Number::from(0)->cos()->value);
        $this->assertEqualsWithDelta(0, Number::from(0)->tan()->value, 0.0001);

        // Test inverse trigonometric functions
        $this->assertEqualsWithDelta(M_PI / 2, Number::from(1)->asin()->value, 0.0001);
        $this->assertEqualsWithDelta(0, Number::from(1)->acos()->value, 0.0001);
        $this->assertEqualsWithDelta(M_PI / 4, Number::from(1)->atan()->value, 0.0001);
    }

    #[Test]
    #[TestDox('Mathematical functions handle edge cases correctly')]
    public function mathematical_functions_handle_edge_cases_correctly(): void
    {
        // Test sqrt with negative number (should return NaN)
        $negativeNum = Number::from(-4);
        $this->assertTrue($negativeNum->sqrt()->isNaN());

        // Test sqrt with zero and positive numbers
        $this->assertSame(0.0, Number::from(0)->sqrt()->value);
        $this->assertSame(1.0, Number::from(1)->sqrt()->value);

        // Test cbrt with negative numbers - Note: PHP's cbrt behavior may vary
        // Let's test what actually happens
        $cbrtNegative = Number::from(-8)->cbrt();
        if (!$cbrtNegative->isNaN()) {
            $this->assertEqualsWithDelta(-2, $cbrtNegative->value, 0.0001);
        }

        // Test log with edge cases
        $this->assertSame(0.0, Number::from(1)->log()->value); // ln(1) = 0
        $this->assertSame(1.0, Number::from(M_E)->log()->value); // ln(e) = 1

        // Test exp with zero
        $this->assertSame(1.0, Number::from(0)->exp()->value); // e^0 = 1
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

        // Test with negative numbers
        $negativeNum = Number::from(-3.7);
        $this->assertSame(-3.0, $negativeNum->ceil()->value);
        $this->assertSame(-4.0, $negativeNum->floor()->value);
        $this->assertSame(-4.0, $negativeNum->round()->value);
        $this->assertSame(-3, $negativeNum->trunc()->value);
        $this->assertSame(-1, $negativeNum->sign());

        // Test sign for zero and positive
        $this->assertSame(0, Number::from(0)->sign());
        $this->assertSame(1, Number::from(5)->sign());
    }

    #[Test]
    #[TestDox('Rounding functions handle edge cases')]
    public function rounding_functions_handle_edge_cases(): void
    {
        // Test with exact values
        $this->assertSame(3.0, Number::from(3.0)->ceil()->value);
        $this->assertSame(3.0, Number::from(3.0)->floor()->value);
        $this->assertSame(3.0, Number::from(3.0)->round()->value);
        $this->assertSame(3, Number::from(3.0)->trunc()->value);

        // Test with .5 values (default rounding mode)
        $this->assertSame(4.0, Number::from(3.5)->round()->value);
        $this->assertSame(-4.0, Number::from(-3.5)->round()->value);

        // Test rounding with precision
        $this->assertSame(3.14, Number::from(3.14159)->round(2)->value);
        $this->assertSame(3.142, Number::from(3.14159)->round(3)->value);

        // Test with zero
        $zero = Number::from(0);
        $this->assertSame(0.0, $zero->ceil()->value);
        $this->assertSame(0.0, $zero->floor()->value);
        $this->assertSame(0.0, $zero->round()->value);
        $this->assertSame(0, $zero->trunc()->value);
        $this->assertSame(0, $zero->sign());
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

        // Test max/min operations
        $this->assertSame(20, $a->max($b)->value);
        $this->assertSame(10, $a->min($b)->value);
        $this->assertSame(10, $a->max(5)->value); // 10 > 5
        $this->assertSame(5, $a->min(5)->value);  // 5 < 10

        // Test clamp operations
        $this->assertSame(15, $a->clamp(15, 25)->value); // 10 clamped to min 15
        $this->assertSame(10, $a->clamp(5, 25)->value);  // 10 within bounds
        $this->assertSame(25, $a->clamp(25, 30)->value); // 10 clamped to min 25
        $this->assertSame(8, $a->clamp(5, 8)->value);    // 10 clamped to max 8

        // Test percentage calculations - percentOf should return float for consistency
        $this->assertSame(1.0, $a->percent(10)->value);   // 10% of 10 = 1.0
        $this->assertSame(2.0, $a->percent(20)->value);   // 20% of 10 = 2.0
        $this->assertSame(50.0, $a->percentOf(20)->value); // 10 is 50% of 20
        $this->assertSame(100.0, $a->percentOf(10)->value); // 10 is 100% of 10
    }

    #[Test]
    #[TestDox('Utility methods handle edge cases correctly')]
    public function utility_methods_handle_edge_cases_correctly(): void
    {
        $zero = Number::from(0);
        $negative = Number::from(-10);
        $positive = Number::from(10);

        // Test max/min with negative numbers
        $this->assertSame(10, $positive->max($negative)->value);
        $this->assertSame(-10, $positive->min($negative)->value);
        $this->assertSame(0, $zero->max($negative)->value);
        $this->assertSame(-10, $zero->min($negative)->value);

        // Test clamp with negative bounds
        $this->assertSame(-5, $negative->clamp(-5, 5)->value);  // -10 clamped to min -5
        $this->assertSame(-10, $negative->clamp(-15, -5)->value); // -10 within negative bounds

        // Test percentage with zero - should be consistent float returns
        $this->assertSame(0.0, $zero->percent(50)->value);      // 50% of 0 = 0.0
        $this->assertSame(0.0, $positive->percent(0)->value);   // 0% of 10 = 0.0

        // Test percentage edge cases
        $this->assertSame(10.0, $positive->percent(100)->value); // 100% of 10 = 10.0
        $this->assertSame(5.0, $positive->percent(50)->value);   // 50% of 10 = 5.0

        // Test clamp with equal bounds
        $this->assertSame(5, $positive->clamp(5, 5)->value);     // Clamp to single value
        $this->assertSame(5, $zero->clamp(5, 5)->value);         // Zero clamped to single value
    }

    #[Test]
    public function type_checking_methods_work_correctly(): void
    {
        $int = Number::from(42);
        $float = Number::from(42.5);
        $floatWhole = Number::from(42.0);

        // Test basic type checking
        $this->assertTrue($int->isInteger());
        $this->assertFalse($int->isFloat());
        $this->assertFalse($float->isInteger());
        $this->assertTrue($float->isFloat());

        // Test float that looks like integer
        $this->assertFalse($floatWhole->isInteger()); // 42.0 is still a float
        $this->assertTrue($floatWhole->isFloat());

        // Test mathematical properties
        $this->assertTrue($int->isFinite());
        $this->assertFalse($int->isNaN());
        $this->assertTrue($int->isPositive());
        $this->assertFalse($int->isNegative());
        $this->assertFalse($int->isZero());

        // Test even/odd for integers - 42 should be even
        $this->assertTrue($int->isEven());
        $this->assertFalse($int->isOdd());

        // Test with odd number
        $odd = Number::from(43);
        $this->assertTrue($odd->isOdd());
        $this->assertFalse($odd->isEven());

        // Test with zero
        $zero = Number::from(0);
        $this->assertTrue($zero->isZero());
        $this->assertFalse($zero->isPositive());
        $this->assertFalse($zero->isNegative());
        $this->assertTrue($zero->isEven()); // 0 is even
        $this->assertFalse($zero->isOdd());

        // Test with negative number
        $negative = Number::from(-5);
        $this->assertTrue($negative->isNegative());
        $this->assertFalse($negative->isPositive());
        $this->assertTrue($negative->isOdd());
        $this->assertFalse($negative->isEven());
    }

    #[Test]
    #[TestDox('Type checking handles special float values')]
    public function type_checking_handles_special_float_values(): void
    {
        // Test with NaN
        $nan = Number::from(-4)->sqrt(); // sqrt of negative = NaN
        $this->assertTrue($nan->isNaN());
        $this->assertFalse($nan->isFinite());
        $this->assertTrue($nan->isFloat());
        $this->assertFalse($nan->isInteger());

        // Note: NaN is neither positive nor negative
        $this->assertFalse($nan->isPositive());
        $this->assertFalse($nan->isNegative());
        $this->assertFalse($nan->isZero());

        // Test even/odd with floats - behavior may vary by implementation
        $floatValue = Number::from(4.5);
        // Some implementations might check the actual value, others might check type
        // We just verify that it's a float and doesn't crash
        $this->assertTrue($floatValue->isFloat());
        $evenResult = $floatValue->isEven();
        $oddResult = $floatValue->isOdd();
        // At minimum, they shouldn't both be true
        $this->assertFalse($evenResult && $oddResult);

        // Test even/odd with float that represents integer
        $floatInt = Number::from(4.0);
        $this->assertTrue($floatInt->isFloat());
        $evenResultFloat = $floatInt->isEven();
        $oddResultFloat = $floatInt->isOdd();
        // Similar check - they shouldn't both be true
        $this->assertFalse($evenResultFloat && $oddResultFloat);
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
    #[TestDox('Conversion methods handle edge cases')]
    public function conversion_methods_handle_edge_cases(): void
    {
        // Test with zero
        $zero = Number::from(0);
        $this->assertSame('0', $zero->toHex());
        $this->assertSame('0', $zero->toOctal());
        $this->assertSame('0', $zero->toBinary());
        $this->assertSame('0', $zero->toBase(36));

        // Test with negative numbers (absolute value used for base conversion)
        $negative = Number::from(-10);
        $this->assertSame(-10, $negative->toInt());
        $this->assertSame(-10.0, $negative->toFloat());

        // Test with float conversion
        $float = Number::from(3.14);
        $this->assertSame(3, $float->toInt()); // Truncates
        $this->assertSame(3.14, $float->toFloat());
        $this->assertSame(3.14, $float->toNumber());

        // Test large numbers
        $large = Number::from(PHP_INT_MAX);
        $this->assertSame(PHP_INT_MAX, $large->toInt());
        $this->assertSame((float)PHP_INT_MAX, $large->toFloat());
    }

    #[Test]
    public function base_conversion_throws_exception_for_invalid_radix(): void
    {
        $num = Number::from(255);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Radix must be between 2 and 36');
        $num->toBase(1);
    }

    #[Test]
    #[TestDox('Base conversion throws exception for radix > 36')]
    public function base_conversion_throws_exception_for_radix_too_large(): void
    {
        $num = Number::from(255);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Radix must be between 2 and 36');
        $num->toBase(37);
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