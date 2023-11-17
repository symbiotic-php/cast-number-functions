# Приведение строк к числам с поддержкой денежных форматов

Пакет имеет три функции:

- cast_number() - основная функция приведения значений к числам
- iterator_cast_numbers() - работает со списками
- money_parse() - работает с денежными форматами чисел

## Какие проблемы решает

- Преобразование строк-чисел с запятой в качестве разделителя дробной части (опционально)
- Преобразование строк с денежными форматами в числа (опционально)
- Преобразование `boolean` и `NULL` (опционально)
- Преобразование специальных форматов (`0x00`.., `0b1010`...) (опционально)
- Массовая обработка итераторов

### Примеры использования

```php
// 
 \_S\CAST_number('1234.234') // 1234.234 (float)
 \_S\CAST_number('1234.234') // 1234.234 (float)
 \_S\CAST_number('1234')     // 1234 (int)
 \_S\CAST_number('7e3')      // 7000.0 (float)
 \_S\CAST_number('7E-3')     // 0.007 (float)
 // Пограничный случай тысячи до 10 000
 \_S\CAST_number('1,234')    // `1,234` (string)
 \_S\CAST_number('1,234', \_S\CAST_number_STRICT_MODE)    // (float)NAN
 \_S\CAST_number('1,234', \_S\CAST_number_ALLOW_COMMA_SEPARATOR)  // (float) 1.234 
 \_S\CAST_number('1,234', \_S\CAST_number_HANDLE_MONEY_FORMAT)  // (int) 1234 
// Не числовые строки
 \_S\CAST_number('string')     // (string)'string' 
 \_S\CAST_number('string', \_S\CAST_number_STRICT_MODE) // (float)NAN

 \_S\CAST_number('1 000 000,007') // (string)'1 000 000,007'
 \_S\CAST_number('1 000 000')     // (string)'1 000 000'
 \_S\CAST_number('10,000.007')    // (string)'10,000.007'
 \_S\CAST_number('1.234.345,89')  // (string)'1.234.345,89' - not supported in money parse
 // С режимом парсинга денежных форматов
 \_S\CAST_number('1 000 000,007', \_S\CAST_number_HANDLE_MONEY_FORMAT) // (float) 1000000.007
 \_S\CAST_number('1 000 000', \_S\CAST_number_HANDLE_MONEY_FORMAT)     // (int) 1000000
 \_S\CAST_number('10,000.007', \_S\CAST_number_HANDLE_MONEY_FORMAT)    // (float)10000.007
 \_S\CAST_number('1.234.345,89', \_S\CAST_number_HANDLE_MONEY_FORMAT)  // (string)'1.234.345,89' - not supported in money parse
 
 // Обработка 0x00... и 0b1000...
 \_S\CAST_number('0x1a')     // (string) 0x1a
 \_S\CAST_number('0X1A')     // (string) 0X1A
 \_S\CAST_number('0b11111111')     // (string) 0b11111111
 \_S\CAST_number('0B11111111')     // (string) 0B11111111
 \_S\CAST_number('0x1a', \_S\CAST_number_HANDLE_SPECIAL_FORMATS)  // (int)26
 \_S\CAST_number('0X1A', \_S\CAST_number_HANDLE_SPECIAL_FORMATS)  // (int)26
 \_S\CAST_number('0b11111111', \_S\CAST_number_HANDLE_SPECIAL_FORMATS)  // (int)26
 \_S\CAST_number('0B11111111', \_S\CAST_number_HANDLE_SPECIAL_FORMATS)  // (int)26

 // Boolean and NULL
 \_S\CAST_number(true) // (bool) true
 \_S\CAST_number(false) // (bool) false
 \_S\CAST_number(null) // null
 // 
 \_S\CAST_number(true, \_S\CAST_number_STRICT_MODE) // (float)NAN
 \_S\CAST_number(false, \_S\CAST_number_STRICT_MODE) // (float)NAN
 \_S\CAST_number(null, \_S\CAST_number_STRICT_MODE)  // (float)NAN
 
 \_S\CAST_number(true, \_S\CAST_number_STRICT_MODE |  \_S\CAST_NUMBER_BOOLEAN_AS_INT)  // (int) 1
 \_S\CAST_number(false, \_S\CAST_number_STRICT_MODE | \_S\CAST_NUMBER_BOOLEAN_AS_INT)) // (int) 0
 \_S\CAST_number(null, \_S\CAST_number_STRICT_MODE |  \_S\CAST_NUMBER_NULL_AS_ZERO)    // (int) 0
 
 
 \_S\CAST_number(['123', 123])   // ['123',123]
 \_S\CAST_number(new stdClass()) // object stdClass
 \_S\CAST_number(['123',123],    \_S\CAST_number_STRICT_MODE) // (float)NAN
 \_S\CAST_number(new stdClass(), \_S\CAST_number_STRICT_MODE) // (float)NAN

/**
 *  Округление и форматирование в денежный формат
 */
  \_S\CAST_number('1234.23445', 2)              // 1234.23 (float)
  \_S\CAST_number('1234567.23445', 2,',',' ')   // 1 234 567,23445 (string)
  \_S\CAST_number('1,234,567.23445', 2,',',' ',\_S\CAST_NUMBER_HANDLE_MONEY_FORMAT) // 1 234 567,23445 (string)

```

### Примеры использования iterator_cast_numbers

```php
  /**
   * Array
   */
  $data = [
      '1.34',
      '221,05',
      345,
      1e3,
      '0x131d',
      [
          '123.234',
          4322,
          23e2,
          '1324'
      ]
  ];
  $iterator = \_S\iterator_cast_numbers($data, mode: \_S\CAST_NUMBER_ALLOW_COMMA_SEPARATOR); // Generator
  // [1.34, 221.05, 345, 1000, 4893, [123.234, 4322, 2300, 1324]]
  $handledData = iterator_to_array($iterator);
  /**
   *  Iterator
   */
  $generator = function (): \Generator {
      for ($i = 0; $i < 3; $i++) {
          yield $i => " " . $i;
      }
      yield 'float' => '1.234';
      yield 'sci' => '1e3';
  };
  //  [0, 1, 2, 'float' => 1.234, 'sci' => 1e3]
  $handledData = iterator_to_array(\_S\iterator_cast_numbers($generator));
```

### Основная функция `cast_number()`

Парамеры:

- `mixed $value`  Значение для преобразования
- `int|null $floatPrecision`  Точность округления, null не округлять
- `string|null $decimal_separator` Разделитель дробной части, если передан число будет отформатировано с разделителями и
  вернется строка
- `string|null $thousands_separator` Разделитель тысячных
- `int $mode` Режим работы, принимает битовую маску из констант

Режимы работы (константы):

`mode: \_S\CAST_NUMBER_ALLOW_COMMA_SEPARATOR`

Разрешает преобразование в числа с запятой в качестве дробного разделителя.

`Если будет передан денежный формат: 1,234,345.43 | 1 1234,56 он не будет обработан, для этого отдельное разрешение`

```
   (string)'1,123'      -> 1.123   (float)
   (string)'1234,00'    -> 1234.00 (float)
   (string)'1234567,23' -> 1234567.23 (float)
```

`mode: \_S\CAST_NUMBER_HANDLE_MONEY_FORMAT`

При использовании денежные форматы будут преобразованы в целые или дробные числа

СМОТРИТЕ РАБОТУ ФУНКЦИИ `money_parse()`

`mode: \_S\CAST_NUMBER_HANDLE_SPECIAL_FORMATS`

Разрешает преобразования для шестнадцатеричного и битового представления целых

```
   0x1a         ->   26 (int)
  '0X1A'        ->   26 (int)
  '-0x1a'       ->   26 (int)
  '-0X1A'       ->   26 (int)
  '0b11111111'  ->  255 (int)
  '0B11111111'  ->  255 (int)
  '-0b11111111' -> -255 (int)
  '-0B11111111' => -255 (int)
```

`mode: \_S\CAST_NUMBER_BOOLEAN_AS_INT`

При включении булев тип будет приведен в integer

```
 true  -> 1
 false -> 0
```

`mode: \_S\CAST_NUMBER_NULL_AS_ZERO`

При включении NULL будет приведен к нулю

`
null -> 0
`

`mode: \_S\CAST_NUMBER_STRICT_MODE`

При включении не числовые типы будут приведены к NAN (float)

```
 iterable -> NAN // Для обработки итераторов используйте iterator_cast_numbers()
 string   -> NAN // Если строка не будет определена как числовая
 object   -> NAN 
 resource -> NAN
 
 true     -> NAN // Если не добавлен флаг CAST_NUMBER_BOOLEAN_AS_INT
 false    -> NAN // Если не добавлен флаг CAST_NUMBER_BOOLEAN_AS_INT
 null     -> NAN // Если не добавлен флаг CAST_NUMBER_NULL_AS_ZERO
```

### Функция `iterator_cast_numbers()`

Параметры

Параметры:

- `iterable $iterator`   Массив или итератор
- `int|null $floatPrecision`   see in cast_number()
- `string|null $decimal_separator`  see in cast_number()
- `string|null $thousands_separator`  see in cast_number()
- `int $mode`  see in cast_number()

`\_S\CAST_NUMBER_DISALLOW_RECURSIVE` - константа запрещает обходить вложенные массивы и итераторы

### Функция `money_parse()`

Параметры:

- `string|\Stringable $number`  Строка с числом
- `bool $returnAsString` - вернуть результат без принудительного приведения к числу

Будьте внимательны: функция не обработает числа с пробелами в начале и конце, а так же с названием валют и их
кодами.
Для парсинга таких строк подготовьте строки или используйте отдельное расширение `NumberFormatter::parseCurrency`

```
 1 000 000,00        -> 1000000.0,
 1 000 000.00        -> 1000000.0,
 1,000,000.00        -> 1000000.0,
 1 000 000 000,00007 -> 1000000000.00007,
 1 000 000 000.00007 -> 1000000000.00007,
 1,000,000,000.00007 -> 1000000000.00007,
 1 000 000           -> 1000000,
 1,000,000           -> 1000000,
 1 000 000 000       -> 1000000000,
 1,000,000,000       -> 1000000000,
 1.234.567,89        -> (string)'1.234.567,89' not supported
 1,234               -> 1234 (int)
 1234,321            -> 1234.321 (float)
 1,54                -> 1.54 (float) 
```






