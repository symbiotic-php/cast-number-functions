<?php

declare(strict_types=1);

namespace Symbiotic\Tests\ToNumberHelper;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase as UnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

use function _S\cast_number;

use const _S\CAST_NUMBER_ALLOW_COMMA_SEPARATOR;
use const _S\CAST_NUMBER_BOOLEAN_AS_INT;
use const _S\CAST_NUMBER_NULL_AS_ZERO;
use const _S\CAST_NUMBER_STRICT_MODE;

class CastNumberTest extends UnitTestCase
{
    /**
     * @param mixed $expected
     * @param float $real
     *
     * @return void
     */
    #[DataProvider('floatLiteralsDataProvider')]
    #[CoversNothing()]
    public function testFloat(
        mixed $expected,
        float|string $realNotCommaSeparated,
        float|string $realCommaSeparated,
    ): void {
        $casted = cast_number($expected);

        $this->assertEquals(
            $realNotCommaSeparated,
            $casted,
            'Incorrect modify value from ' . $expected . ' to ' . $realNotCommaSeparated
        );
        $casted = cast_number($expected, mode: CAST_NUMBER_ALLOW_COMMA_SEPARATOR);

        $this->assertEquals(
            $realCommaSeparated,
            $casted,
            'Incorrect modify value from ' . $expected . ' to ' . $realNotCommaSeparated
        );
    }

    /**
     * @return void
     * @covers ::cast_number()
     */
    public function testRound(): void
    {
        $this->assertEquals(
            1.0,
            cast_number(1.5000, 0, mode: PHP_ROUND_HALF_DOWN),
            'Incorrect modify value from  1.5  to 1'
        );
        $this->assertEquals(
            2.0,
            cast_number('1.5000', 0, mode: PHP_ROUND_HALF_UP),
            'Incorrect modify value from  1.5  to 2'
        );
        $this->assertEquals(
            15000,
            cast_number('15500', -3, mode: PHP_ROUND_HALF_DOWN),
            'Incorrect modify value from  15500  to 15000'
        );
        $this->assertEquals(
            16000,
            cast_number('15500', -3, mode: PHP_ROUND_HALF_UP),
            'Incorrect modify value from  15500  to 16000'
        );
        $this->assertEquals(
            [],
            cast_number([], -3, mode: PHP_ROUND_HALF_UP),
            'Incorrect modify value from  15500  to 16000'
        );
    }

    /**
     * @param mixed $expected
     * @param int   $real
     *
     * @return void
     */
    #[DataProvider('intLiteralsDataProvider')]
    #[CoversNothing()]
    public function testInt(mixed $expected, int $real): void
    {
        $casted = cast_number($expected);

        $this->assertEquals(
            $real,
            $casted,
            'Incorrect modify value from ' . $expected . ' to ' . $real . ' actual ' . $casted
        );
    }

    /**
     * @param mixed $expected
     *
     * @return void
     */
    #[DataProvider('notNumbersLiteralsDataProvider')]
    #[CoversNothing()]
    public function testNotNumeric(mixed $expected, mixed $real): void
    {
        $casted = cast_number($expected);

        $this->assertEquals(
            $expected,
            $casted,
            'Incorrect modify value from ' . gettype($expected) . ' actual ' . gettype($casted)
        );
        $casted = cast_number($expected, mode: CAST_NUMBER_STRICT_MODE);

        $this->assertNan(
            $casted,
            'Incorrect modify value from ' . gettype($expected) . ' actual ' . gettype($casted)
        );

        $casted = cast_number($expected, mode: CAST_NUMBER_NULL_AS_ZERO | CAST_NUMBER_BOOLEAN_AS_INT);
        $this->assertEquals(
            $casted,
            $real,
            'Incorrect modify value from ' . gettype($expected) . ' actual ' . gettype($casted)
        );
    }


    public static function floatLiteralsDataProvider(): array
    {
        /**
         *  0 - excepted
         *  1 - base without hande comma separated
         *  2 - test with support comma separated
         */
        return [
            'string 1.234' => ['1.234', 1.234, 1.234,],
            'string excel 1,234' => ['1,234', '1,234', 1.234,],
            'float 1.234' => [1.234, 1.234, 1.234],
            '1.2e3' => ['1.2e3', 1.2e3, 1.2e3],
            '7e3' => ['7e3', 7e3, 7e3],
            '7e-3' => ['7e-3', 7e-3, 7e-3],
            '1.2E3' => ['1.2E3', 1.2E3, 1.2E3],
            '7E3' => ['7E3', 7E3, 7E3],
            '7E-3' => ['7E-3', 7E-3, 7E-3],
            '.42' => ['.42', .42, .42],
            '23.' => ['23.', 23., 23.],
            '-1.234' => ['-1.234', -1.234, -1.234],
            '-1.2e3' => ['-1.2e3', -1.2e3, -1.2e3],
            '-7e3' => ['-7e3', -7e3, -7e3],
            '-7e-3' => ['-7e-3', -7e-3, -7e-3],
            '-1.2E3' => ['-1.2E3', -1.2E3, -1.2E3],
            '-7E3' => ['-7E3', -7E3, -7E3],
            '-7E-3' => ['-7E-3', -7E-3, -7E-3],
            '-.42' => ['-.42', -.42, -.42],
            '-23.' => ['-23.', -23., -23.],
            '1_23443.36' => [1_234_43.36, 123443.36, 123443.36],
            '1_23443.' => [1_234_43., 123443., 123443.],
            '-1_23443.34' => [-1_23443.34, -123443.34, -123443.34],
            'stringable float object 1.4242' => [
                new class('1.4242') implements \Stringable {
                    public function __construct(private string $str) {}

                    public function __toString(): string
                    {
                        return $this->str;
                    }
                },
                1.4242,
                1.4242,
            ],
            'stringable float object 1,4242' => [
                new class('1,4243') implements \Stringable {
                    public function __construct(private string $str) {}

                    public function __toString(): string
                    {
                        return $this->str;
                    }
                },
                '1,4243',
                1.4243,
            ]
        ];
    }

    public static function intLiteralsDataProvider(): array
    {
        return [
            '1234' => ['1234', 1234],
            '-1234' => ['-1234', -1234],
            '1_234_567' => ['1234567', 1_234_567],
            '-1_234_567' => ['-1234567', -1_234_567],
            '0123' => ['0123', 0123],
            'string 042' => ['042', 042],
            '-0123' => ['-0123', -0123],
            '0x1a' => ['0x1a', 0x1a],
            '0X1A' => ['0X1A', 0X1A],
            '-0x1a' => ['-0x1a', -0x1a],
            '-0X1A' => ['-0X1A', -0X1A],
            '0b11111111' => ['0b11111111', 0b11111111],
            '0B11111111' => ['0B11111111', 0B11111111],
            '-0b11111111' => ['-0b11111111', -0b11111111],
            '-0B11111111' => ['-0B11111111', -0B11111111],
        ];
    }

    public static function moneyLiteralsDataProvider(): array
    {
        return [
            '123123123.32' => ['123123123.32'],
            '12345678' => ['12345678'],
            'asdasd 1 000 000,00007' => ['asdasd 1 000 000,00007'],
            'asdasd 1 000 000.00007' => ['asdasd 1 000 000.00007'],
            'asdasd 1,000,000.00007' => ['asdasd 1,000,000.00007'],
            '1 000 000,00007 ' => ['1 000 000,00007 space'],
            '1 000 000.00007 ' => ['1 000 000.00007 space'],
            '1,000,000.00007 ' => ['1,000,000.00007 space'],
            '1 000 000,00007' => [1000000.00007],
            '1 000 000.00007' => [1000000.00007],
            '1,000,000.00007' => [1000000.00007],
            '1 000 000,00' => [1000000.],
            '1 000 000.00' => [1000000.],
            '1,000,000.00' => [1000000.],
            '1 000 000 000,00007' => [1000000000.00007],
            '1 000 000 000.00007' => [1000000000.00007],
            '1,000,000,000.00007' => [1000000000.00007],
            '1 000 000' => [1000000],
            '1,000,000' => [1000000],
            '1 000 000 000' => [1000000000],
            '1,000,000,000' => [1000000000],
            '1234,321' => [1234.321],
            '1,234' => [1.234],
            '1,23' => [1.23],
            '1.23' => [1.23],
            '0.23' => [0.23],
            // Будьте внимательны: английский формат и одной группой нолей и без дробной части будет воспринят как дробное число!
        ];
    }

    public static function notNumbersLiteralsDataProvider(): array
    {
        $resource = fopen('php://output', 'a+');
        $object = new class('234') {
            public function __construct(private readonly string $var) {}

            public function getVar(): string
            {
                return $this->var;
            }
        };
        $closure = function () {};
        return [
            'empty string' => ['', ''],
            'string' => ['string', 'string'],
            'null' => [null, 0],
            'false' => [false, 0],
            'true' => [true, 1],
            'resource' => [$resource, $resource],
            'not iterable object' => [$object, $object],
            'closure' => [$closure, $closure]
        ];
    }
}




