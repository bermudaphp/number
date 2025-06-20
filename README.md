# Bermuda Number

A PHP library for number operations, mathematical computations, and statistical analysis.

[![PHP Version](https://img.shields.io/badge/php-%5E8.4-blue)](https://php.net)
[![Tests](https://github.com/bermudaphp/number/workflows/Tests/badge.svg)](https://github.com/bermudaphp/number/actions)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

**🌍 English | [Русский](README.md)**

## 📦 Installation

```bash
composer require bermudafunk/number
```

## 🎯 Quick Start

```php
use Bermuda\Stdlib\Number;
use Bermuda\Stdlib\NumberConverter;

// Creation
$num = Number::from(42);
$num = Number::from('0xFF'); // Hex
$num = Number::from('0b1010'); // Binary

// Arithmetic
$result = Number::from(10)
    ->add(5)
    ->multiply(2)
    ->divide(3); // (10 + 5) * 2 / 3

// Mathematical functions
$num = Number::from(16);
echo $num->sqrt()->value; // 4
echo $num->log2()->value; // 4

// Percentages
$price = Number::from(100);
$tax = $price->percent(20); // 20% of 100 = 20
$discount = Number::from(80)->percentOf(100); // 80% of 100

// NumberConverter - safe conversion
$safe = NumberConverter::convertValue('123'); // 123 (int)
$safe = NumberConverter::convertValue('hello'); // 'hello' (string)

// NumberConverter - strict conversion
$strict = NumberConverter::convertToNumber('123'); // 123 (int)
// NumberConverter::convertToNumber('hello'); // InvalidArgumentException
```

## 📚 Documentation

### Creating Number Objects

```php
// From various types
Number::from(42);           // int
Number::from(3.14);         // float
Number::from('123');        // string
Number::from(true);         // bool -> 1
Number::from(null);         // null -> 0

// Special formats
Number::from('0xFF');       // hex -> 255
Number::from('0755');       // octal -> 493
Number::from('0b1010');     // binary -> 10
Number::from('1e3');        // scientific -> 1000
```

### NumberConverter - Conversion Utilities

```php
use Bermuda\Stdlib\NumberConverter;

// Safe conversion - returns original if not a number
$result = NumberConverter::convertValue('123');    // 123 (int)
$result = NumberConverter::convertValue('45.67');  // 45.67 (float)
$result = NumberConverter::convertValue('0xFF');   // 255 (int)
$result = NumberConverter::convertValue('hello');  // 'hello' (string)
$result = NumberConverter::convertValue('');       // '' (string)

// Strict conversion - throws exception for invalid data
$number = NumberConverter::convertToNumber('123');    // 123 (int)
$number = NumberConverter::convertToNumber('45.67');  // 45.67 (float)
$number = NumberConverter::convertToNumber('0xFF');   // 255 (int)

// Exceptions for invalid data
try {
    NumberConverter::convertToNumber('hello');     // InvalidArgumentException
    NumberConverter::convertToNumber('');          // InvalidArgumentException
    NumberConverter::convertToNumber(' 123 ');     // InvalidArgumentException
} catch (InvalidArgumentException $e) {
    echo $e->getMessage(); // Detailed error description
}
```

### Arithmetic Operations

```php
$num = Number::from(10);

$num->add(5);              // 15
$num->subtract(3);         // 7
$num->multiply(2);         // 20
$num->divide(4);           // 2.5
$num->mod(3);              // 1
$num->power(2);            // 100
$num->abs();               // 10
$num->integerDivide(3);    // 3
```

### Mathematical Functions

```php
$num = Number::from(16);

// Roots and powers
$num->sqrt();              // 4.0
$num->cbrt();              // 2.52
$num->exp();               // e^16

// Logarithms
$num->log();               // ln(16)
$num->log10();             // log10(16)
$num->log2();              // 4.0

// Trigonometry
$angle = Number::from(M_PI / 2);
$angle->sin();             // 1.0
$angle->cos();             // ~0
$angle->tan();             // large number

// Inverse trigonometric
Number::from(1)->asin();   // π/2
Number::from(1)->acos();   // 0
Number::from(1)->atan();   // π/4
```

### Rounding

```php
$num = Number::from(3.7);

$num->ceil();              // 4.0
$num->floor();             // 3.0
$num->round();             // 4.0
$num->round(2);            // 3.70
$num->trunc();             // 3
$num->sign();              // 1 (positive)
```

### Type Checking and Properties

```php
$num = Number::from(42);

// Types
$num->isInteger();         // true
$num->isFloat();           // false
$num->isFinite();          // true
$num->isNaN();             // false

// Mathematical properties
$num->isPositive();        // true
$num->isNegative();        // false
$num->isZero();            // false
$num->isEven();            // true
$num->isOdd();             // false
```

### Utilities

```php
$a = Number::from(10);
$b = Number::from(20);

// Min/Max/Clamp
$a->max($b);               // 20
$a->min($b);               // 10
$a->clamp(15, 25);         // 15 (clamped to minimum)

// Percentages
$a->percent(25);           // 2.5 (25% of 10)
$a->percentOf($b);         // 50.0 (10 is 50% of 20)
```

### Conversion

```php
$num = Number::from(255);

// Basic types
$num->toInt();             // 255
$num->toFloat();           // 255.0
$num->toNumber();          // 255

// Number systems
$num->toHex();             // "ff"
$num->toOctal();           // "377"
$num->toBinary();          // "11111111"
$num->toBase(36);          // "73"
```

### Statistical Functions

```php
$numbers = [1, 2, 3, 4, 5];

Number::mean($numbers);              // 3
Number::median($numbers);            // 3
Number::mode([1, 2, 2, 3]);         // 2.0
Number::standardDeviation($numbers); // ~1.58
Number::midrange($numbers);          // 3
```

### Random Numbers

```php
// Random float from 10 to 20
Number::random(10, 20);

// Random int from 5 to 15
Number::randomInt(5, 15);
```

### Special Functions

```php
// Factorial
Number::factorial(5);        // 120

// Fibonacci numbers
Number::fibonacci(10);       // 55

// Ranges
Number::range(1, 5);         // [1, 2, 3, 4, 5]
Number::range(0, 10, 2);     // [0, 2, 4, 6, 8, 10]
Number::range(5, 1, -1);     // [5, 4, 3, 2, 1]
```

### Exception Handling

```php
try {
    // Number exceptions
    Number::from('invalid');    // InvalidArgumentException
    Number::from(10)->divide(0); // ArithmeticError
    Number::factorial(-1);       // InvalidArgumentException
    
    // NumberConverter exceptions
    NumberConverter::convertToNumber('hello');     // InvalidArgumentException
    NumberConverter::convertToNumber('123abc');    // InvalidArgumentException
    NumberConverter::convertToNumber(' 123 ');     // InvalidArgumentException
    
} catch (InvalidArgumentException $e) {
    // Invalid input format
} catch (ArithmeticError $e) {
    // Mathematical error (division by zero)
}
```

## 📋 Requirements

- PHP 8.4+

## 📜 License

MIT License
