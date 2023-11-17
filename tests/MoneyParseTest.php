<?php

declare(strict_types=1);

namespace Symbiotic\Tests\ToNumberHelper;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase as UnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

use function _S\cast_number;

use function _S\money_parse;


class MoneyParseTest extends UnitTestCase
{
    /**
     * @param mixed        $expected
     * @param float|string $real
     *
     * @return void
     */
    #[DataProvider('moneyLiteralsDataProvider')]
    #[CoversNothing()]
    public function testMoneyReturnAsString(mixed $expected, mixed $real, mixed $realFloat): void
    {
        $casted = money_parse($expected, true);

        $this->assertEquals(
            $real,
            $casted,
            'Incorrect modify value from ' . $expected . ' to ' . $real
        );
    }

    /**
     * @param mixed        $expected
     * @param float|string $real
     *
     * @return void
     */
    #[DataProvider('moneyLiteralsDataProvider')]
    #[CoversNothing()]
    public function testMoneyNotAsString(mixed $expected, mixed $real, mixed $realFloat): void
    {
        $casted = money_parse($expected);

        $this->assertEquals(
            $realFloat,
            $casted,
            'Incorrect modify value from ' . $expected . ' to ' . $realFloat
        );
    }


    public static function moneyLiteralsDataProvider(): array
    {
        $string = new class('1 000 000,23') implements \Stringable {
            public function __construct(private string $money) {}

            public function __toString()
            {
                return $this->money;
            }
        };

        return [

            '123123123.32' => ['123123123.32', '123123123.32', 123123123.32,],
            '12345678' => ['12345678', '12345678', 12345678.],
            'asdasd 1 000 000,00007' => ['asdasd 1 000 000,00007', 'asdasd 1 000 000,00007', 0],
            'asdasd 1 000 000.00007' => ['asdasd 1 000 000.00007', 'asdasd 1 000 000.00007', 0],
            'asdasd 1,000,000.00007' => ['asdasd 1,000,000.00007', 'asdasd 1,000,000.00007', 0],
            '1 000 000,00007 space' => ['1 000 000,00007 space', '1 000 000,00007 space', 1],
            '1 000 000.00007 space' => ['1 000 000.00007 space', '1 000 000.00007 space', 1],
            '1,000,000.00007 space' => ['1,000,000.00007 space', '1,000,000.00007 space', 1],
            '1 000 000,00007' => ['1 000 000,00007', 1000000.00007, 1000000.00007],
            '1 000 000.00007' => ['1 000 000.00007', 1000000.00007, 1000000.00007],
            '1,000,000.00007' => ['1,000,000.00007', 1000000.00007, 1000000.00007],
            '100 000,00' => ['100 000,00', 100000., 100000.],
            '100,000.00' => ['100,000.00', 100000., 100000.],
            '100 000' => ['100 000', 100000, 100000],
            '100,000' => ['100,000', 100000, 100000],
            '10 000,23' => ['10 000,23', 10000.23, 10000.23],
            '10,000.23' => ['10,000.23', 10000.23, 10000.23],
            '10,000' => ['10,000', 10000, 10000],
            '1,000' => ['1,000', 1000, 1000],
            '1 000 000,00' => ['1 000 000,00', 1000000., 1000000.],
            '1 000 000.00' => ['1 000 000.00', 1000000., 1000000.],
            '1,000,000.00' => ['1,000,000.00', 1000000., 1000000.],
            '1 000 000 000,0007' => ['1 000 000 000,0007', 1000000000.0007, 1000000000.0007],
            '1 000 000 000.0007' => ['1 000 000 000.0007', 1000000000.0007, 1000000000.0007],
            '1,000,000,000.0007' => ['1,000,000,000.0007', 1000000000.0007, 1000000000.0007],
            '1 000 000' => ['1 000 000', 1000000, 1000000],
            '1,000,000' => ['1,000,000', 1000000, 1000000],
            '1 000 000 000' => ['1 000 000 000', 1000000000, 1000000000],
            '1,000,000,000' => ['1,000,000,000', 1000000000, 1000000000],
            '1234,321' => ['1234,321', '1234,321', 1234],
            '1,234' => ['1,234', 1234, 1234],
            'string 1,23' => ['1,23', '1,23', 1],
            'string 1.23' => ['1.23', '1.23', 1.23],
            'string 0.23' => ['-0.23', '-0.23', -0.23],
            'string' => ['string', 'string', 0.0],
            'empty string' => ['', '', 0.0],
            'stringable number' => [$string, 1000000.23, 1000000.23],
        ];
    }
}




