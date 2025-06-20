# Bermuda Number

PHP-библиотека для операций с числами, математических вычислений и статистического анализа.

[![PHP Version](https://img.shields.io/badge/php-%5E8.4-blue)](https://php.net)
[![Tests](https://github.com/bermudaphp/number/workflows/Tests/badge.svg)](https://github.com/bermudaphp/number/actions)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

**🇷🇺 Русский** | [🇺🇸 English](README.md)

---

## Обзор

Библиотека Bermuda Number предоставляет неизменяемый класс `Number` с математическими функциями, статистическими операциями и конвертацией систем счисления. Предназначена для приложений, требующих числовых вычислений, анализа данных или точных математических операций.

## Основные возможности

- **Основные возможности**: математические операции, статистические функции, конвертация систем счисления
- **Типобезопасность**: неизменяемые объекты с интеллектуальным сохранением типов
- **Валидация**: проверка форматов чисел и типов
- **Утилиты**: форматирование, сравнения, генерация случайных чисел

## Установка

```bash
composer require bermudaphp/number
```

## Требования

- PHP 8.4 или выше

## Базовое использование

```php
<?php

use Bermuda\Stdlib\Number;

// Создание чисел
$num = Number::from(42);
$fromString = Number::from('123.45');
$fromHex = Number::from('0xFF'); // 255

// Математические операции (неизменяемые)
$result = Number::from(10)
    ->add(5)        // 15
    ->multiply(2)   // 30
    ->subtract(5)   // 25
    ->divide(5);    // 5

// Статистические операции
$numbers = [1, 2, 3, 4, 5];
$mean = Number::mean($numbers);     // 3
$median = Number::median($numbers); // 3
$mode = Number::mode([1,2,2,3]);   // 2.0
```

## Справочник API

### Создание чисел

```php
// Из различных типов
Number::from(42);           // целое число
Number::from(3.14);         // число с плавающей точкой
Number::from('123');        // строка
Number::from(true);         // логическое → 1
Number::from(null);         // null → 0

// Из различных систем счисления
Number::from('0xFF');       // шестнадцатеричная → 255
Number::from('0755');       // восьмеричная → 493
Number::from('0b1010');     // двоичная → 10
```

### Математические операции

```php
$num = Number::from(12);

// Базовая арифметика
$num->add(8);               // 20
$num->subtract(4);          // 8
$num->multiply(3);          // 36
$num->divide(4);            // 3
$num->mod(5);               // 2
$num->power(2);             // 144

// Продвинутые функции
$num->sqrt();               // √12 ≈ 3.46
$num->cbrt();               // ∛12 ≈ 2.29
$num->log();                // ln(12) ≈ 2.48
$num->log10();              // log₁₀(12) ≈ 1.08
```

### Статистические функции

```php
$dataset = [12, 15, 18, 13, 19, 21, 14, 16, 17];

// Меры центральной тенденции
Number::mean($dataset);         // 16.11 (среднее арифметическое)
Number::median($dataset);       // 16 (средний элемент)
Number::mode([1,2,2,3]);       // 2.0 (наиболее частое)
Number::midrange($dataset);     // 16.5 ((мин+макс)/2)

// Изменчивость
Number::standardDeviation($dataset); // ~3.18

// Примеры поведения типов
Number::mean([2, 4, 6]);       // 4 (точное деление → int)
Number::mean([1, 2, 4]);       // 2.33... (неточное → float)
Number::median([1,2,3]);       // 2 (нечет. кол-во → сохр. тип)
Number::median([1,2,3,4]);     // 2.5 (чет. кол-во → float)
Number::midrange([1, 5]);      // 3 (точное деление → int)
Number::midrange([1, 4]);      // 2.5 (неточное → float)
```

### Конвертация систем счисления

```php
$num = Number::from(255);

// Конвертация в различные системы
$num->toHex();              // "ff"
$num->toOctal();            // "377"
$num->toBinary();           // "11111111"
$num->toBase(36);           // "73"

// Валидация
Number::isHex('0xFF');      // true
Number::isOctal('0755');    // true
Number::isBinary('0b1010'); // true

// Конвертация с автоопределением
Number::convertBase('0xFF');    // 255 (из шестн.)
Number::convertBase('0755');    // 493 (из восьм.)
Number::convertBase('123');     // 123 (из десят.)
```

### Функции теории чисел

```php
// Простые числа
Number::isPrime(17);        // true
Number::isPrime(18);        // false

// Совершенные числа
Number::isPerfect(6);       // true (1+2+3=6)
Number::isPerfect(28);      // true

// Математические последовательности
Number::fibonacci(10);      // 55
Number::factorial(5);       // 120

// Связи между числами
Number::gcd(48, 18);        // 6 (наибольший общий делитель)
Number::lcm(48, 18);        // 144 (наименьшее общее кратное)
```

### Форматирование и отображение

```php
$num = Number::from(1234.567);

$num->format(2);            // "1,234.57"
$num->toFixed(2);           // "1234.57"
$num->toExponential(2);     // "1.23e+3"
```

### Пользовательское форматирование

```php
$price = Number::from(1234.56);

// Форматирование валюты
$formatted = Number::formatNumber(
    $price,
    2,          // знаки после запятой
    '.',        // разделитель дробной части
    ',',        // разделитель тысяч
    '$',        // префикс
    ' USD'      // суффикс
);
echo $formatted; // "$1,234.56 USD"
```

### Утилиты

```php
// Генерация диапазонов
$range = Number::range(1, 10, 2);       // Числа от 1 до 10, шаг 2
$values = array_map(fn($n) => $n->value, $range); // [1, 3, 5, 7, 9]

// Интерполяция и проекция
Number::lerp(0, 100, 0.5);              // 50 (линейная интерполяция)
Number::map(5, 0, 10, 0, 100);          // 50 (проекция из одного диапазона в другой)

// Расчеты расстояний
Number::distance2D(0, 0, 3, 4);         // 5 (2D евклидово расстояние)
Number::distance3D(0, 0, 0, 1, 1, 1);   // √3 (3D евклидово расстояние)

// Генерация случайных чисел
Number::random(10, 20);                 // Случайное число с плавающей точкой между 10-20
Number::randomInt(1, 6);                // Случайное целое число 1-6

// Конвертация углов
Number::degreesToRadians(180);          // π радиан
Number::radiansToDegrees(Math.PI);      // 180 градусов
```

### Проверка типов и валидация

```php
$num = Number::from(42);

// Проверки типов и свойств
$num->isInteger();          // true
$num->isFloat();            // false
$num->isEven();             // true
$num->isOdd();              // false
$num->isPositive();         // true
$num->isNegative();         // false
$num->isZero();             // false

// Математическая валидация
Number::checkFinite(42);    // true
Number::checkFinite(INF);   // false
Number::checkNaN(NAN);      // true
```

## Утилита NumberConverter

Класс `NumberConverter` обеспечивает локале-независимую конвертацию строк в числа:

```php
use Bermuda\Stdlib\NumberConverter;

// Конвертация отдельных значений
NumberConverter::convertValue('123');      // 123 (int)
NumberConverter::convertValue('123.45');   // 123.45 (float)
NumberConverter::convertValue('1e5');      // 100000.0 (float)
NumberConverter::convertValue('hello');    // 'hello' (без изменений)

// Конвертация массивов
$data = ['id' => '123', 'price' => '45.67', 'name' => 'product'];
$converted = NumberConverter::convertArray($data);
// Результат: ['id' => 123, 'price' => 45.67, 'name' => 'product']

// Валидация
NumberConverter::isNumeric('123.45');     // true
NumberConverter::isNumeric('hello');      // false
```

## Неизменяемость и цепочки методов

Все операции возвращают новые экземпляры `Number`, обеспечивая безопасные цепочки методов:

```php
$result = Number::from(100)
    ->add(50)           // 150
    ->multiply(2)       // 300
    ->sqrt()           // √300 ≈ 17.32
    ->round(2)         // 17.32
    ->power(2);        // 299.98

// Исходное число не изменяется
$original = Number::from(10);
$modified = $original->add(5);
// $original->value === 10
// $modified->value === 15
```

## Обработка ошибок

Библиотека выбрасывает соответствующие исключения для некорректных операций:

```php
// InvalidArgumentException
Number::factorial(-1);              // Отрицательный факториал
Number::from('invalid');            // Неверный формат числа
Number::mean([]);                   // Пустой массив

// ArithmeticError
Number::from(10)->divide(0);        // Деление на ноль
```

## Тестирование

Запуск тестов:

```bash
composer test
```

Генерация отчета о покрытии:

```bash
composer test-coverage
```

## Участие в разработке

Приветствуются вклады в проект. Отправляйте pull requests или создавайте issues для сообщений об ошибках и предложений новых функций.

## Лицензия

Проект лицензирован под лицензией MIT. См. файл [LICENSE](LICENSE) для подробностей.

## Ссылки

- **Репозиторий**: [GitHub](https://github.com/bermudaphp/number)
- **Проблемы**: [GitHub Issues](https://github.com/bermudaphp/number/issues)
- **Пакет**: [Packagist](https://packagist.org/packages/bermudaphp/number), ' USD'
); // "$1,234.57 USD"
```

## 🔧 Продвинутое использование

### Цепочки методов

```php
$result = Number::from(100)
    ->add(50)           // 150
    ->multiply(2)       // 300
    ->sqrt()           // √300 ≈ 17.32
    ->round(2)         // 17.32
    ->power(2)         // 299.98
    ->clamp(200, 400); // 299.98 (в пределах границ)
```

### Работа с константами

```php
// Математические константы
echo Number::PI;          // 3.14159...
echo Number::E;           // 2.71828...
echo Number::GOLDEN_RATIO; // 1.61803...
echo Number::EULER_GAMMA; // 0.57721...

// Использование констант в вычислениях
$circleArea = Number::from($radius)
    ->power(2)
    ->multiply(Number::PI);
```

## 📜 Лицензия

Этот проект лицензирован под лицензией MIT - см. файл [LICENSE](LICENSE) для подробностей.
