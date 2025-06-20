# Bermuda Number

**🌍 [English](README.en.md) | Русский**

Библиотека для работы с числами в PHP, предоставляющий API для математических операций, конвертации и проверки типов.

## 📦 Установка

```bash
composer require bermudaphp/number
```

## 🎯 Быстрый старт

```php
use Bermuda\Stdlib\Number;
use Bermuda\Stdlib\NumberConverter;

// Создание
$num = Number::from(42);
$num = Number::from('0xFF'); // Hex
$num = Number::from('0b1010'); // Binary

// Арифметика
$result = Number::from(10)
    ->add(5)
    ->multiply(2)
    ->divide(3); // (10 + 5) * 2 / 3

// Математические функции
$num = Number::from(16);
echo $num->sqrt()->value; // 4
echo $num->log2()->value; // 4

// Проценты
$price = Number::from(100);
$tax = $price->percent(20); // 20% от 100 = 20
$discount = Number::from(80)->percentOf(100); // 80% от 100

// NumberConverter - безопасная конвертация
$safe = NumberConverter::convertValue('123'); // 123 (int)
$safe = NumberConverter::convertValue('hello'); // 'hello' (string)

// NumberConverter - строгая конвертация
$strict = NumberConverter::convertToNumber('123'); // 123 (int)
// NumberConverter::convertToNumber('hello'); // InvalidArgumentException
```

## 📚 Документация

### Создание объектов Number

```php
// Из различных типов
Number::from(42);           // int
Number::from(3.14);         // float
Number::from('123');        // string
Number::from(true);         // bool -> 1
Number::from(null);         // null -> 0

// Специальные форматы
Number::from('0xFF');       // hex -> 255
Number::from('0755');       // octal -> 493
Number::from('0b1010');     // binary -> 10
Number::from('1e3');        // scientific -> 1000
```

### NumberConverter - утилиты конвертации

```php
use Bermuda\Stdlib\NumberConverter;

// Безопасная конвертация - возвращает оригинал если не число
$result = NumberConverter::convertValue('123');    // 123 (int)
$result = NumberConverter::convertValue('45.67');  // 45.67 (float)
$result = NumberConverter::convertValue('0xFF');   // 255 (int)
$result = NumberConverter::convertValue('hello');  // 'hello' (string)
$result = NumberConverter::convertValue('');       // '' (string)

// Строгая конвертация - выбрасывает исключение для некорректных данных
$number = NumberConverter::convertToNumber('123');    // 123 (int)
$number = NumberConverter::convertToNumber('45.67');  // 45.67 (float)
$number = NumberConverter::convertToNumber('0xFF');   // 255 (int)

// Исключения для невалидных данных
try {
    NumberConverter::convertToNumber('hello');     // InvalidArgumentException
    NumberConverter::convertToNumber('');          // InvalidArgumentException
    NumberConverter::convertToNumber(' 123 ');     // InvalidArgumentException
} catch (InvalidArgumentException $e) {
    echo $e->getMessage(); // Детальное описание ошибки
}
```

### Арифметические операции

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

### Математические функции

```php
$num = Number::from(16);

// Корни и степени
$num->sqrt();              // 4.0
$num->cbrt();              // 2.52
$num->exp();               // e^16

// Логарифмы
$num->log();               // ln(16)
$num->log10();             // log10(16)
$num->log2();              // 4.0

// Тригонометрия
$angle = Number::from(M_PI / 2);
$angle->sin();             // 1.0
$angle->cos();             // ~0
$angle->tan();             // большое число

// Обратные тригонометрические
Number::from(1)->asin();   // π/2
Number::from(1)->acos();   // 0
Number::from(1)->atan();   // π/4
```

### Округление

```php
$num = Number::from(3.7);

$num->ceil();              // 4.0
$num->floor();             // 3.0
$num->round();             // 4.0
$num->round(2);            // 3.70
$num->trunc();             // 3
$num->sign();              // 1 (положительное)
```

### Проверка типов и свойств

```php
$num = Number::from(42);

// Типы
$num->isInteger();         // true
$num->isFloat();           // false
$num->isFinite();          // true
$num->isNaN();             // false

// Математические свойства
$num->isPositive();        // true
$num->isNegative();        // false
$num->isZero();            // false
$num->isEven();            // true
$num->isOdd();             // false
```

### Утилиты

```php
$a = Number::from(10);
$b = Number::from(20);

// Min/Max/Clamp
$a->max($b);               // 20
$a->min($b);               // 10
$a->clamp(15, 25);         // 15 (зажато к минимуму)

// Проценты
$a->percent(25);           // 2.5 (25% от 10)
$a->percentOf($b);         // 50.0 (10 составляет 50% от 20)
```

### Конвертация

```php
$num = Number::from(255);

// Базовые типы
$num->toInt();             // 255
$num->toFloat();           // 255.0
$num->toNumber();          // 255

// Системы счисления
$num->toHex();             // "ff"
$num->toOctal();           // "377"
$num->toBinary();          // "11111111"
$num->toBase(36);          // "73"
```

### Статистические функции

```php
$numbers = [1, 2, 3, 4, 5];

Number::mean($numbers);              // 3
Number::median($numbers);            // 3
Number::mode([1, 2, 2, 3]);         // 2.0
Number::standardDeviation($numbers); // ~1.58
Number::midrange($numbers);          // 3
```

### Случайные числа

```php
// Случайное float от 10 до 20
Number::random(10, 20);

// Случайное int от 5 до 15
Number::randomInt(5, 15);
```

### Специальные функции

```php
// Факториал
Number::factorial(5);        // 120

// Числа Фибоначчи
Number::fibonacci(10);       // 55

// Диапазоны
Number::range(1, 5);         // [1, 2, 3, 4, 5]
Number::range(0, 10, 2);     // [0, 2, 4, 6, 8, 10]
Number::range(5, 1, -1);     // [5, 4, 3, 2, 1]
```

### Исключения

```php
try {
    // Number исключения
    Number::from('invalid');    // InvalidArgumentException
    Number::from(10)->divide(0); // ArithmeticError
    Number::factorial(-1);       // InvalidArgumentException
    
    // NumberConverter исключения
    NumberConverter::convertToNumber('hello');     // InvalidArgumentException
    NumberConverter::convertToNumber('123abc');    // InvalidArgumentException
    NumberConverter::convertToNumber(' 123 ');     // InvalidArgumentException
    
} catch (InvalidArgumentException $e) {
    // Неверный формат входных данных
} catch (ArithmeticError $e) {
    // Математическая ошибка (деление на ноль)
}
```

## 📋 Требования

- PHP 8.4+

## 📜 Лицензия

MIT License
