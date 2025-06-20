<?php

declare(strict_types=1);

namespace Bermuda\Stdlib;

use InvalidArgumentException;
use ArithmeticError;
use JsonSerializable;

/**
 * Class for working with numbers
 */
final readonly class Number implements JsonSerializable, \Stringable
{
    /**
     * Mathematical constants
     */
    public const float PI = M_PI;
    public const float E = M_E;
    public const float GOLDEN_RATIO = 1.618033988749;
    public const float EULER_GAMMA = 0.5772156649015329;

    public function __construct(
        public int|float $value = 0
    ) {}

    /**
     * Creates Number from any value
     */
    public static function from(mixed $value): self
    {
        return new self(self::normalize($value));
    }

    /**
     * Checks if value is an integer
     */
    public static function checkInteger(mixed $value): bool
    {
        return is_int($value) || (is_float($value) && floor($value) === $value);
    }

    /**
     * Checks if value is a finite number
     */
    public static function checkFinite(mixed $value): bool
    {
        return is_numeric($value) && is_finite((float)$value);
    }

    /**
     * Checks if value is NaN
     */
    public static function checkNaN(mixed $value): bool
    {
        return is_float($value) && is_nan($value);
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
     * Maximum safe integer
     */
    public static function maxSafeInteger(): int
    {
        return PHP_INT_MAX;
    }

    /**
     * Minimum safe integer
     */
    public static function minSafeInteger(): int
    {
        return PHP_INT_MIN;
    }

    /**
     * Checks if a number is prime
     */
    public static function isPrime(int|self $number): bool
    {
        $value = $number instanceof self ? $number->toInt() : $number;

        if ($value < 2) {
            return false;
        }

        if ($value === 2) {
            return true;
        }

        if ($value % 2 === 0) {
            return false;
        }

        $sqrt = (int)sqrt($value);
        for ($i = 3; $i <= $sqrt; $i += 2) {
            if ($value % $i === 0) {
                return false;
            }
        }

        return true;
    }

    /**
     * Calculates factorial of a number
     */
    public static function factorial(int|self $number): self
    {
        $value = $number instanceof self ? $number->toInt() : $number;

        if ($value < 0) {
            throw new InvalidArgumentException('Factorial is not defined for negative numbers');
        }

        if ($value <= 1) {
            return new self(1);
        }

        $result = 1;
        for ($i = 2; $i <= $value; $i++) {
            $result *= $i;
        }

        return new self($result);
    }

    /**
     * Calculates Fibonacci number at given position
     */
    public static function fibonacci(int|self $position): self
    {
        $n = $position instanceof self ? $position->toInt() : $position;

        if ($n < 0) {
            throw new InvalidArgumentException('Fibonacci position must be non-negative');
        }

        if ($n <= 1) {
            return new self($n);
        }

        $a = 0;
        $b = 1;

        for ($i = 2; $i <= $n; $i++) {
            $temp = $a + $b;
            $a = $b;
            $b = $temp;
        }

        return new self($b);
    }

    /**
     * Calculates greatest common divisor (GCD)
     */
    public static function gcd(int|self $a, int|self $b): self
    {
        $valueA = abs($a instanceof self ? $a->toInt() : $a);
        $valueB = abs($b instanceof self ? $b->toInt() : $b);

        while ($valueB !== 0) {
            $temp = $valueB;
            $valueB = $valueA % $valueB;
            $valueA = $temp;
        }

        return new self($valueA);
    }

    /**
     * Calculates least common multiple (LCM)
     */
    public static function lcm(int|self $a, int|self $b): self
    {
        $valueA = abs($a instanceof self ? $a->toInt() : $a);
        $valueB = abs($b instanceof self ? $b->toInt() : $b);

        if ($valueA === 0 || $valueB === 0) {
            return new self(0);
        }

        $gcd = self::gcd($valueA, $valueB)->toInt();
        return new self(($valueA * $valueB) / $gcd);
    }

    /**
     * Checks if number is perfect (sum of proper divisors equals the number)
     */
    public static function isPerfect(int|self $number): bool
    {
        $value = $number instanceof self ? $number->toInt() : $number;

        if ($value <= 1) {
            return false;
        }

        $sum = 1; // 1 is always a proper divisor
        $sqrt = (int)sqrt($value);

        for ($i = 2; $i <= $sqrt; $i++) {
            if ($value % $i === 0) {
                $sum += $i;
                if ($i !== $value / $i) { // avoid counting square root twice
                    $sum += $value / $i;
                }
            }
        }

        return $sum === $value;
    }

    /**
     * Converts degrees to radians
     */
    public static function degreesToRadians(int|float|self $degrees): self
    {
        $value = $degrees instanceof self ? $degrees->toNumber() : $degrees;
        return new self($value * (self::PI / 180));
    }

    /**
     * Converts radians to degrees
     */
    public static function radiansToDegrees(int|float|self $radians): self
    {
        $value = $radians instanceof self ? $radians->toNumber() : $radians;
        return new self($value * (180 / self::PI));
    }

    /**
     * Calculates distance between two points in 2D space
     */
    public static function distance2D(
        int|float|self $x1, int|float|self $y1,
        int|float|self $x2, int|float|self $y2
    ): self {
        $dx = ($x2 instanceof self ? $x2->toNumber() : $x2) -
            ($x1 instanceof self ? $x1->toNumber() : $x1);
        $dy = ($y2 instanceof self ? $y2->toNumber() : $y2) -
            ($y1 instanceof self ? $y1->toNumber() : $y1);

        return new self(sqrt($dx * $dx + $dy * $dy));
    }

    /**
     * Calculates distance between two points in 3D space
     */
    public static function distance3D(
        int|float|self $x1, int|float|self $y1, int|float|self $z1,
        int|float|self $x2, int|float|self $y2, int|float|self $z2
    ): self {
        $dx = ($x2 instanceof self ? $x2->toNumber() : $x2) -
            ($x1 instanceof self ? $x1->toNumber() : $x1);
        $dy = ($y2 instanceof self ? $y2->toNumber() : $y2) -
            ($y1 instanceof self ? $y1->toNumber() : $y1);
        $dz = ($z2 instanceof self ? $z2->toNumber() : $z2) -
            ($z1 instanceof self ? $z1->toNumber() : $z1);

        return new self(sqrt($dx * $dx + $dy * $dy + $dz * $dz));
    }

    /**
     * Linear interpolation between two values
     */
    public static function lerp(
        int|float|self $start,
        int|float|self $end,
        float|self $t
    ): self {
        $startValue = $start instanceof self ? $start->toNumber() : $start;
        $endValue = $end instanceof self ? $end->toNumber() : $end;
        $tValue = $t instanceof self ? $t->toNumber() : $t;

        return new self($startValue + ($endValue - $startValue) * $tValue);
    }

    /**
     * Maps a value from one range to another
     */
    public static function map(
        int|float|self $value,
        int|float|self $fromMin, int|float|self $fromMax,
        int|float|self $toMin, int|float|self $toMax
    ): self {
        $val = $value instanceof self ? $value->toNumber() : $value;
        $fMin = $fromMin instanceof self ? $fromMin->toNumber() : $fromMin;
        $fMax = $fromMax instanceof self ? $fromMax->toNumber() : $fromMax;
        $tMin = $toMin instanceof self ? $toMin->toNumber() : $toMin;
        $tMax = $toMax instanceof self ? $toMax->toNumber() : $toMax;

        $normalized = ($val - $fMin) / ($fMax - $fMin);
        return new self($tMin + ($tMax - $tMin) * $normalized);
    }

    /**
     * Generates array of numbers in arithmetic progression
     */
    public static function range(
        int|float|self $start,
        int|float|self $end,
        int|float|self $step = 1
    ): array {
        $startValue = $start instanceof self ? $start->toNumber() : $start;
        $endValue = $end instanceof self ? $end->toNumber() : $end;
        $stepValue = $step instanceof self ? $step->toNumber() : $step;

        if ($stepValue === 0) {
            throw new InvalidArgumentException('Step cannot be zero');
        }

        $result = [];

        if ($stepValue > 0) {
            for ($i = $startValue; $i <= $endValue; $i += $stepValue) {
                $result[] = new self($i);
            }
        } else {
            for ($i = $startValue; $i >= $endValue; $i += $stepValue) {
                $result[] = new self($i);
            }
        }

        return $result;
    }

    /**
     * Finds statistical mode (most frequent value) in array of numbers
     */
    public static function mode(array $numbers): self|null
    {
        if (empty($numbers)) {
            return null;
        }

        $frequency = [];

        foreach ($numbers as $number) {
            $value = $number instanceof self ? $number->toNumber() : $number;
            $key = (string)$value;
            $frequency[$key] = ($frequency[$key] ?? 0) + 1;
        }

        $maxFreq = max($frequency);
        $modes = array_keys($frequency, $maxFreq);

        // Return first mode if multiple exist
        return new self((float)$modes[0]);
    }

    /**
     * Calculates mean (average) of array of numbers
     * Returns the natural result of division, preserving type when possible
     */
    public static function mean(array $numbers): self
    {
        if (empty($numbers)) {
            throw new InvalidArgumentException('Cannot calculate mean of empty array');
        }

        $sum = 0;
        foreach ($numbers as $number) {
            $sum += $number instanceof self ? $number->toNumber() : $number;
        }

        return new self(($sum / count($numbers)));
    }

    /**
     * Calculates midrange (center of range) of array of numbers
     * Returns the arithmetic mean of minimum and maximum values
     */
    public static function midrange(array $numbers): self
    {
        if (empty($numbers)) {
            throw new InvalidArgumentException('Cannot calculate midrange of empty array');
        }

        // Extract numeric values from mixed array
        $values = [];
        foreach ($numbers as $number) {
            $values[] = $number instanceof self ? $number->toNumber() : $number;
        }

        $min = min($values);
        $max = max($values);

        return new self(($min + $max) / 2);
    }

    /**
     * Calculates median of array of numbers
     * For odd count: preserves type of middle element
     * For even count: returns average (typically float)
     */
    public static function median(array $numbers): self
    {
        if (empty($numbers)) {
            throw new InvalidArgumentException('Cannot calculate median of empty array');
        }

        // Extract values preserving types
        $values = [];
        foreach ($numbers as $num) {
            $values[] = ($num instanceof self) ? $num->value : $num;
        }

        sort($values);
        $count = count($values);

        if ($count % 2 === 0) {
            // Even count: average of two middle values
            $i1 = intval(($count / 2) - 1);
            $i2 = intval($count / 2);
            return new self(($values[$i1] + $values[$i2]) / 2);
        } else {
            // Odd count: return middle value preserving its type
            $middleIndex = intval(($count - 1) / 2);
            $middleValue = $values[$middleIndex];

            return new self($middleValue);
        }
    }

    /**
     * Calculates standard deviation of array of numbers
     */
    public static function standardDeviation(array $numbers): self
    {
        if (count($numbers) < 2) {
            throw new InvalidArgumentException('Need at least 2 numbers for standard deviation');
        }

        $mean = self::mean($numbers)->toNumber();
        $sumSquaredDifferences = 0;

        foreach ($numbers as $number) {
            $value = $number instanceof self ? $number->toNumber() : $number;
            $difference = $value - $mean;
            $sumSquaredDifferences += $difference * $difference;
        }

        $variance = $sumSquaredDifferences / (count($numbers) - 1);
        return new self(sqrt($variance));
    }

    /**
     * Generates random number between min and max (inclusive)
     */
    public static function random(
        int|float|self $min = 0,
        int|float|self $max = 1
    ): self {
        $minValue = $min instanceof self ? $min->toNumber() : $min;
        $maxValue = $max instanceof self ? $max->toNumber() : $max;

        if ($minValue > $maxValue) {
            throw new InvalidArgumentException('Min value cannot be greater than max value');
        }

        $random = mt_rand() / mt_getrandmax();
        return new self($minValue + ($maxValue - $minValue) * $random);
    }

    /**
     * Generates random integer between min and max (inclusive)
     */
    public static function randomInt(
        int|self $min = 0,
        int|self $max = PHP_INT_MAX
    ): self {
        $minValue = $min instanceof self ? $min->toInt() : $min;
        $maxValue = $max instanceof self ? $max->toInt() : $max;

        return new self(mt_rand($minValue, $maxValue));
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

        // Traditional octal notation: 0755 (starts with 0 and length > 1)
        if (str_starts_with($value, '0') && strlen($value) > 1) {
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
    public static function convertBase(string $value, ?int $fromBase = null): self
    {
        if ($value === '') {
            throw new InvalidArgumentException('Empty string cannot be converted');
        }

        // Auto-detect base if not specified
        if ($fromBase === null) {
            // Priority order: HEX -> BINARY -> OCTAL -> DECIMAL
            if (self::isHex($value)) {
                return new self(hexdec($value));
            }

            if (self::isBinary($value)) {
                if (str_starts_with(strtolower($value), '0b')) {
                    return new self(bindec(substr($value, 2)));
                }
            }

            if (self::isOctal($value)) {
                // Handle different octal formats
                if (str_starts_with(strtolower($value), '0o')) {
                    return new self(octdec(substr($value, 2)));
                } elseif (str_starts_with($value, '0') && strlen($value) > 1) {
                    return new self(octdec(substr($value, 1)));
                }
            }

            // Default to decimal
            if (is_numeric($value)) {
                return new self($value + 0);
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

        return new self((int)base_convert($value, $fromBase, 10));
    }

    /**
     * Format a number with a thousand separators and decimal places
     */
    public static function formatNumber(
        int|float|self $number,
        int $decimals = 0,
        string $decimalSeparator = '.',
        string $thousandsSeparator = ',',
        string $prefix = '',
        string $suffix = ''
    ): string {
        $value = $number instanceof self ? $number->value : $number;
        $formatted = number_format($value, $decimals, $decimalSeparator, $thousandsSeparator);
        return $prefix . $formatted . $suffix;
    }

    /**
     * Returns absolute value
     */
    public function abs(): self
    {
        return new self(abs($this->value));
    }

    /**
     * Returns remainder of division (modulo)
     */
    public function mod(int|float|self $divisor): self
    {
        $divisorValue = self::normalize($divisor);
        if ($divisorValue == 0) {
            throw new ArithmeticError('Division by zero');
        }
        return new self($this->value % $divisorValue);
    }

    /**
     * Division
     */
    public function divide(int|float|self $divisor): self
    {
        $divisorValue = self::normalize($divisor);
        if ($divisorValue == 0) {
            throw new ArithmeticError('Division by zero');
        }
        return new self($this->value / $divisorValue);
    }

    /**
     * Integer division
     */
    public function integerDivide(int|float|self $divisor): self
    {
        $divisorValue = self::normalize($divisor);
        if ($divisorValue == 0) {
            throw new ArithmeticError('Division by zero');
        }
        return new self(intdiv((int)$this->value, (int)$divisorValue));
    }

    /**
     * Calculates percentage of the number
     */
    public function percent(int|float|self $percentage): self
    {
        return new self($this->value / 100 * self::normalize($percentage));
    }

    /**
     * Calculates what percentage another number is of the current number
     */
    public function percentOf(int|float|self $total): self
    {
        $totalValue = self::normalize($total);
        if ($totalValue == 0) {
            throw new ArithmeticError('Division by zero');
        }
        return new self($this->value / $totalValue * 100);
    }

    /**
     * Multiplication
     */
    public function multiply(int|float|self $multiplier): self
    {
        return new self($this->value * self::normalize($multiplier));
    }

    /**
     * Addition
     */
    public function add(int|float|self $addend): self
    {
        return new self($this->value + self::normalize($addend));
    }

    /**
     * Subtraction
     */
    public function subtract(int|float|self $subtrahend): self
    {
        return new self($this->value - self::normalize($subtrahend));
    }

    /**
     * Exponentiation
     */
    public function power(int|float $exponent): self
    {
        return new self(pow($this->value, $exponent));
    }

    /**
     * Square root
     */
    public function sqrt(): self
    {
        if ($this->value < 0) {
            return new self(NAN);
        }
        return new self(sqrt($this->value));
    }

    /**
     * Cube root
     */
    public function cbrt(): self
    {
        return new self(pow($this->value, 1/3));
    }

    /**
     * Natural logarithm
     */
    public function log(): self
    {
        return new self(log($this->value));
    }

    /**
     * Base-10 logarithm
     */
    public function log10(): self
    {
        return new self(log10($this->value));
    }

    /**
     * Base-2 logarithm
     */
    public function log2(): self
    {
        return new self(log($this->value, 2));
    }

    /**
     * Exponential function
     */
    public function exp(): self
    {
        return new self(exp($this->value));
    }

    /**
     * Sine
     */
    public function sin(): self
    {
        return new self(sin($this->value));
    }

    /**
     * Cosine
     */
    public function cos(): self
    {
        return new self(cos($this->value));
    }

    /**
     * Tangent
     */
    public function tan(): self
    {
        return new self(tan($this->value));
    }

    /**
     * Arcsine
     */
    public function asin(): self
    {
        return new self(asin($this->value));
    }

    /**
     * Arccosine
     */
    public function acos(): self
    {
        return new self(acos($this->value));
    }

    /**
     * Arctangent
     */
    public function atan(): self
    {
        return new self(atan($this->value));
    }

    /**
     * Rounds up to the next largest integer
     */
    public function ceil(): self
    {
        return new self(ceil($this->value));
    }

    /**
     * Rounds down to the next smallest integer
     */
    public function floor(): self
    {
        return new self(floor($this->value));
    }

    /**
     * Rounds to the nearest integer
     */
    public function round(int $precision = 0, int $mode = PHP_ROUND_HALF_UP): self
    {
        return new self(round($this->value, $precision, $mode));
    }

    /**
     * Truncates the fractional part
     */
    public function trunc(): self
    {
        return new self((int)$this->value);
    }

    /**
     * Returns the sign of the number (-1, 0, 1)
     */
    public function sign(): int
    {
        return $this->value <=> 0;
    }

    /**
     * Maximum of two numbers
     */
    public function max(int|float|self $other): self
    {
        return new self(max($this->value, self::normalize($other)));
    }

    /**
     * Minimum of two numbers
     */
    public function min(int|float|self $other): self
    {
        return new self(min($this->value, self::normalize($other)));
    }

    /**
     * Clamps value between min and max
     */
    public function clamp(int|float|self $min, int|float|self $max): self
    {
        $minValue = self::normalize($min);
        $maxValue = self::normalize($max);
        return new self(max($minValue, min($maxValue, $this->value)));
    }

    /**
     * Equality check
     */
    public function equals(int|float|self $other, bool $strict = false): bool
    {
        $otherValue = self::normalize($other);
        return $strict ? $this->value === $otherValue : $this->value == $otherValue;
    }

    /**
     * Strict equality check (===)
     */
    public function strictEquals(int|float|self $other): bool
    {
        return $this->equals($other, true);
    }

    /**
     * Compares two numbers
     */
    public function compare(int|float|self $other): int
    {
        return $this->value <=> self::normalize($other);
    }

    /**
     * Checks if current number is greater
     */
    public function greaterThan(int|float|self $other): bool
    {
        return $this->value > self::normalize($other);
    }

    /**
     * Checks if current number is greater than or equal
     */
    public function greaterThanOrEqual(int|float|self $other): bool
    {
        return $this->value >= self::normalize($other);
    }

    /**
     * Checks if current number is less
     */
    public function lessThan(int|float|self $other): bool
    {
        return $this->value < self::normalize($other);
    }

    /**
     * Checks if current number is less than or equal
     */
    public function lessThanOrEqual(int|float|self $other): bool
    {
        return $this->value <= self::normalize($other);
    }

    /**
     * Checks if number is an integer
     */
    public function isInteger(): bool
    {
        return is_int($this->value) || (is_float($this->value) && floor($this->value) === $this->value);
    }

    /**
     * Checks if number is a float
     */
    public function isFloat(): bool
    {
        return is_float($this->value);
    }

    /**
     * Checks if number is finite
     */
    public function isFinite(): bool
    {
        return is_finite((float)$this->value);
    }

    /**
     * Checks if number is NaN
     */
    public function isNaN(): bool
    {
        return is_float($this->value) && is_nan($this->value);
    }

    /**
     * Checks if number is positive
     */
    public function isPositive(): bool
    {
        return $this->value > 0;
    }

    /**
     * Checks if number is negative
     */
    public function isNegative(): bool
    {
        return $this->value < 0;
    }

    /**
     * Checks if number is zero
     */
    public function isZero(): bool
    {
        return $this->value == 0;
    }

    /**
     * Checks if number is even
     */
    public function isEven(): bool
    {
        return $this->isInteger() && (int)$this->value % 2 === 0;
    }

    /**
     * Checks if number is odd
     */
    public function isOdd(): bool
    {
        return $this->isInteger() && (int)$this->value % 2 !== 0;
    }

    /**
     * Converts to string with fixed number of decimal places
     */
    public function toFixed(int $digits = 0): string
    {
        return number_format($this->value, $digits, '.', '');
    }

    /**
     * Converts to string with specified precision
     */
    public function toPrecision(int $precision): string
    {
        return sprintf("%.{$precision}g", $this->value);
    }

    /**
     * Converts to exponential notation
     */
    public function toExponential(int $fractionDigits = 6): string
    {
        return sprintf("%.{$fractionDigits}e", $this->value);
    }

    /**
     * Converts to string
     */
    public function toString(): string
    {
        return (string)$this->value;
    }

    /**
     * Converts to int
     */
    public function toInt(): int
    {
        return (int)$this->value;
    }

    /**
     * Converts to float
     */
    public function toFloat(): float
    {
        return (float)$this->value;
    }

    /**
     * Gets numeric value
     */
    public function toNumber(): int|float
    {
        return $this->value;
    }

    /**
     * Converts to hexadecimal string
     */
    public function toHex(): string
    {
        return dechex((int)$this->value);
    }

    /**
     * Converts to octal string
     */
    public function toOctal(): string
    {
        return decoct((int)$this->value);
    }

    /**
     * Converts to binary string
     */
    public function toBinary(): string
    {
        return decbin((int)$this->value);
    }

    /**
     * Converts to string with specified base
     */
    public function toBase(int $radix): string
    {
        if ($radix < 2 || $radix > 36) {
            throw new InvalidArgumentException('Radix must be between 2 and 36');
        }

        return match ($radix) {
            2 => $this->toBinary(),
            8 => $this->toOctal(),
            10 => $this->toString(),
            16 => $this->toHex(),
            default => base_convert((string)(int)$this->value, 10, $radix)
        };
    }

    /**
     * Formats number for display
     */
    public function format(
        int $decimals = 0,
        string $decimalSeparator = '.',
        string $thousandsSeparator = ','
    ): string {
        return number_format($this->value, $decimals, $decimalSeparator, $thousandsSeparator);
    }

    /**
     * String conversion when used in string context
     */
    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * JSON serialization
     */
    public function jsonSerialize(): int|float
    {
        return $this->value;
    }

    /**
     * Normalizes input value to numeric type
     * Handles special number formats and type conversion
     */
    private static function normalize(mixed $value): int|float
    {
        if ($value instanceof self) {
            return $value->value;
        }

        if (is_string($value)) {
            // Check for special number formats first (with prefixes)
            if (self::isHex($value)) {
                return hexdec($value);
            }

            if (self::isOctal($value)) {
                // Handle different octal formats
                if (str_starts_with(strtolower($value), '0o')) {
                    return octdec(substr($value, 2));
                } elseif (str_starts_with($value, '0') && strlen($value) > 1) {
                    return octdec(substr($value, 1));
                }
            }

            if (self::isBinary($value)) {
                if (str_starts_with(strtolower($value), '0b')) {
                    return bindec(substr($value, 2));
                }
            }

            // Check if it's a valid decimal number
            if (is_numeric($value)) {
                return $value + 0;
            }

            // Strings with letters without proper prefix are invalid
            if (preg_match('/[a-zA-Z]/', $value)) {
                throw new InvalidArgumentException('Invalid number format: ' . $value);
            }
        }

        if (is_numeric($value)) {
            return $value + 0;
        }

        if (is_bool($value)) {
            return (int)$value;
        }

        if (is_null($value)) {
            return 0;
        }

        throw new InvalidArgumentException('Cannot normalize value to number: ' . gettype($value));
    }
}