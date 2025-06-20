# Bermuda Number

A PHP library for number operations, mathematical computations, and statistical analysis.

[![PHP Version](https://img.shields.io/badge/php-%5E8.4-blue)](https://php.net)
[![Tests](https://github.com/bermudaphp/number/workflows/Tests/badge.svg)](https://github.com/bermudaphp/number/actions)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

[🇷🇺 Russian](README.RU.md) | **🇺🇸 English**

---

## Overview

The Bermuda Number library provides an immutable `Number` class with mathematical functions, statistical operations, and number base conversions. It is designed for applications requiring numerical computations, data analysis, or precise mathematical operations.

## Core Features

- **Core Features**: Mathematical operations, statistical functions, number base conversions
- **Type Safety**: Immutable objects with intelligent type preservation  
- **Validation**: Number format validation and type checking
- **Utility Functions**: Formatting, comparisons, random generation

## Installation

```bash
composer require bermudaphp/number
```

## Requirements

- PHP 8.4 or higher

## Basic Usage

```php

use Bermuda\Stdlib\Number;

// Create numbers
$num = Number::from(42);
$fromString = Number::from('123.45');
$fromHex = Number::from('0xFF'); // 255

// Mathematical operations (immutable)
$result = Number::from(10)
    ->add(5)        // 15
    ->multiply(2)   // 30
    ->subtract(5)   // 25
    ->divide(5);    // 5

// Statistical operations
$numbers = [1, 2, 3, 4, 5];
$mean = Number::mean($numbers);     // 3
$median = Number::median($numbers); // 3
$mode = Number::mode([1,2,2,3]);   // 2.0
```

## API Reference

### Number Creation

```php
// From various types
Number::from(42);           // integer
Number::from(3.14);         // float  
Number::from('123');        // string
Number::from(true);         // boolean → 1
Number::from(null);         // null → 0

// From different number bases
Number::from('0xFF');       // hexadecimal → 255
Number::from('0755');       // octal → 493
Number::from('0b1010');     // binary → 10
```

### Mathematical Operations

```php
$num = Number::from(12);

// Basic arithmetic
$num->add(8);               // 20
$num->subtract(4);          // 8
$num->multiply(3);          // 36
$num->divide(4);            // 3
$num->mod(5);               // 2
$num->power(2);             // 144

// Advanced functions
$num->sqrt();               // √12 ≈ 3.46
$num->cbrt();               // ∛12 ≈ 2.29
$num->log();                // ln(12) ≈ 2.48
$num->log10();              // log₁₀(12) ≈ 1.08
```

### Statistical Functions

```php
$dataset = [12, 15, 18, 13, 19, 21, 14, 16, 17];

// Central tendency measures
Number::mean($dataset);         // 16.11 (arithmetic mean)
Number::median($dataset);       // 16 (middle value)
Number::mode([1,2,2,3]);       // 2.0 (most frequent)
Number::midrange($dataset);     // 16.5 ((min+max)/2)

// Variability
Number::standardDeviation($dataset); // ~3.18

// Type behavior examples
Number::mean([2, 4, 6]);       // 4 (exact division → int)
Number::mean([1, 2, 4]);       // 2.33... (inexact → float)
Number::median([1,2,3]);       // 2 (odd count → preserves type)
Number::median([1,2,3,4]);     // 2.5 (even count → float)
Number::midrange([1, 5]);      // 3 (exact division → int)
Number::midrange([1, 4]);      // 2.5 (inexact → float)
```

### Number Base Conversions

```php
$num = Number::from(255);

// Convert to different bases
$num->toHex();              // "ff"
$num->toOctal();            // "377"  
$num->toBinary();           // "11111111"
$num->toBase(36);           // "73"

// Validation
Number::isHex('0xFF');      // true
Number::isOctal('0755');    // true
Number::isBinary('0b1010'); // true

// Base conversion with auto-detection
Number::convertBase('0xFF');    // 255 (from hex)
Number::convertBase('0755');    // 493 (from octal)
Number::convertBase('123');     // 123 (from decimal)
```

### Number Theory Functions

```php
// Prime numbers
Number::isPrime(17);        // true
Number::isPrime(18);        // false

// Perfect numbers  
Number::isPerfect(6);       // true (1+2+3=6)
Number::isPerfect(28);      // true

// Mathematical sequences
Number::fibonacci(10);      // 55
Number::factorial(5);       // 120

// Number relationships
Number::gcd(48, 18);        // 6 (greatest common divisor)
Number::lcm(48, 18);        // 144 (least common multiple)
```

### Formatting and Display

```php
$num = Number::from(1234.567);

$num->format(2);            // "1,234.57"
$num->toFixed(2);           // "1234.57"
$num->toExponential(2);     // "1.23e+3"
```
### Custom Formatting

```php
$price = Number::from(1234.56);

// Currency formatting
$formatted = Number::formatNumber(
    $price,
    2,          // decimals
    '.',        // decimal separator
    ',',        // thousands separator
    '$',        // prefix
    ' USD'      // suffix
);
echo $formatted; // "$1,234.56 USD"
```

### Utility Functions

```php
// Range generation
$range = Number::range(1, 10, 2);       // Numbers from 1 to 10, step 2
$values = array_map(fn($n) => $n->value, $range); // [1, 3, 5, 7, 9]

// Interpolation and mapping
Number::lerp(0, 100, 0.5);              // 50 (linear interpolation)
Number::map(5, 0, 10, 0, 100);          // 50 (map from one range to another)

// Distance calculations  
Number::distance2D(0, 0, 3, 4);         // 5 (2D Euclidean distance)
Number::distance3D(0, 0, 0, 1, 1, 1);   // √3 (3D Euclidean distance)

// Random number generation
Number::random(10, 20);                 // Random float between 10-20
Number::randomInt(1, 6);                // Random integer 1-6

// Angle conversions
Number::degreesToRadians(180);          // π radians
Number::radiansToDegrees(Math.PI);      // 180 degrees
```

### Type Checking and Validation

```php
$num = Number::from(42);

// Type and property checks
$num->isInteger();          // true
$num->isFloat();            // false  
$num->isEven();             // true
$num->isOdd();              // false
$num->isPositive();         // true
$num->isNegative();         // false
$num->isZero();             // false

// Mathematical validation
Number::checkFinite(42);    // true
Number::checkFinite(INF);   // false
Number::checkNaN(NAN);      // true
```

## NumberConverter Utility

The `NumberConverter` class provides locale-independent string-to-number conversion:

```php
use Bermuda\Stdlib\NumberConverter;

// Individual value conversion
NumberConverter::convertValue('123');      // 123 (int)
NumberConverter::convertValue('123.45');   // 123.45 (float)
NumberConverter::convertValue('1e5');      // 100000.0 (float)
NumberConverter::convertValue('hello');    // 'hello' (unchanged)

// Array conversion
$data = ['id' => '123', 'price' => '45.67', 'name' => 'product'];
$converted = NumberConverter::convertArray($data);
// Result: ['id' => 123, 'price' => 45.67, 'name' => 'product']

// Validation
NumberConverter::isNumeric('123.45');     // true
NumberConverter::isNumeric('hello');      // false
```

## Immutability and Method Chaining

All operations return new `Number` instances, allowing for safe method chaining:

```php
$result = Number::from(100)
    ->add(50)           // 150
    ->multiply(2)       // 300  
    ->sqrt()           // √300 ≈ 17.32
    ->round(2)         // 17.32
    ->power(2);        // 299.98

// Original number unchanged
$original = Number::from(10);
$modified = $original->add(5);
// $original->value === 10
// $modified->value === 15
```

## Error Handling

The library throws appropriate exceptions for invalid operations:

```php
// InvalidArgumentException
Number::factorial(-1);              // Negative factorial
Number::from('invalid');            // Invalid number format
Number::mean([]);                   // Empty array

// ArithmeticError  
Number::from(10)->divide(0);        // Division by zero
```

## 🔧 Advanced Usage

### Method Chaining

```php
$result = Number::from(100)
    ->add(50)           // 150
    ->multiply(2)       // 300
    ->sqrt()           // √300 ≈ 17.32
    ->round(2)         // 17.32
    ->power(2)         // 299.98
    ->clamp(200, 400); // 299.98 (within bounds)
```

### Working with Constants

```php
// Mathematical constants
echo Number::PI;          // 3.14159...
echo Number::E;           // 2.71828...
echo Number::GOLDEN_RATIO; // 1.61803...
echo Number::EULER_GAMMA; // 0.57721...

// Using constants in calculations
$circleArea = Number::from($radius)
    ->power(2)
    ->multiply(Number::PI);
```

## 📜 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
