<?php

declare(strict_types=1);

namespace Symbiotic\Tests\CastNumberHelper;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase as UnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

use function _S\iterator_cast_numbers;

use const _S\CAST_NUMBER_DISALLOW_RECURSIVE;
use const _S\CAST_NUMBER_STRICT_MODE;

class IterableCastNumberTest extends UnitTestCase
{
    /**
     * @param mixed $expected
     * @param mixed $real
     *
     * @return void
     */
    #[DataProvider('iterableDataProvider')]
    #[CoversNothing()]
    public function testIterable(iterable $expected, iterable $real): void
    {
        $casted = iterator_cast_numbers($expected, mode: CAST_NUMBER_STRICT_MODE);

        $this->assertEquals(
            $real,
            iterator_to_array($casted),
            'Incorrect modify value from ' . gettype($expected) . ' actual ' . gettype($casted)
        );
    }

    /**
     * @param mixed $expected
     * @param mixed $real
     *
     * @return void
     */
    #[DataProvider('iterableNoStrictDataProvider')]
    #[CoversNothing()]
    public function testIterableNotStrict(iterable $expected, iterable $real): void
    {
        $casted = iterator_cast_numbers($expected);

        $this->assertEquals(
            $real,
            iterator_to_array($casted),
            'Incorrect modify value from ' . gettype($expected) . ' actual ' . gettype($casted)
        );
    }


    /**
     * @return void
     * @covers ::iterator_cast_numbers()
     */
    public function testIterableNotDeep(): void
    {
        $data = [
            ['1.34', '221', 345, 1e3, '0x131d', ['123.234', 4322, 23e2, '1324']],
            [1.34, 221, 345, 1e3, 0x131d, ['123.234', 4322, 23e2, '1324']],
        ];

        $casted = iterator_to_array(
            iterator_cast_numbers($data[0], mode: CAST_NUMBER_DISALLOW_RECURSIVE | CAST_NUMBER_STRICT_MODE)
        );
        $this->assertEquals(
            $data[1],
            $casted,
            'Incorrect modify value from ' . json_encode($data[0]) . ' actual ' . json_encode($casted)
        );
    }

    public static function iterableDataProvider(): array
    {
        $generator = function (): \Generator {
            for ($i = 0; $i < 3; $i++) {
                yield $i => " " . $i;
            }
            yield 'float' => '1.234';
            yield 'sci' => '1e3';
        };
        return [
            'generator' => [
                $generator(),
                [0, 1, 2, 'float' => 1.234, 'sci' => 1e3]
            ],
            'array' => [
                ['1.34', '221', 345, 1e3, '0x131d', ['123.234', 4322, 23e2, '1324']],
                [1.34, 221, 345, 1e3, 0x131d, [123.234, 4322, 23e2, 1324]],
            ],
        ];
    }

    public static function iterableNoStrictDataProvider(): array
    {
        $generator = function (): \Generator {
            for ($i = 0; $i < 3; $i++) {
                yield $i => " " . $i;
            }
            yield 'float' => '1.234';
            yield 'sci' => '1e3';
            yield 'empty string' => '';
            yield 'string' => 'string';
        };

        $std = new \stdClass();
        return [
            'generator' => [
                $generator(),
                [0, 1, 2, 'float' => 1.234, 'sci' => 1e3, 'empty string' => '', 'string' => 'string']
            ],
            'array' => [
                ['object' => $std, '221', 345, 1e3, '0x131d', ['123.234', 4322, 23e2, '1324']],
                ['object' => $std, 221, 345, 1e3, 0x131d, [123.234, 4322, 23e2, 1324]],
            ],
        ];
    }


}




