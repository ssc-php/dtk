<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Spec\Domain\Token;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Ssc\Dtk\Domain\Exception\ValidationFailedException;
use Ssc\Dtk\Domain\Token\Service;

#[CoversClass(Service::class)]
#[Small]
final class ServiceTest extends TestCase
{
    #[DataProvider('validValuesProvider')]
    #[TestDox('It can be converted from/to string: $scenario')]
    public function test_it_can_be_converted_from_to_string(
        string $scenario,
        Service $case,
    ): void {
        $this->assertSame($case, Service::fromString($scenario));
        $this->assertSame($scenario, $case->toString());
    }

    /**
     * @return \Iterator<array{
     *     scenario: string,
     *     case: Service,
     * }>
     */
    public static function validValuesProvider(): \Iterator
    {
        yield [
            'scenario' => 'youtrack',
            'case' => Service::Youtrack,
        ];
    }

    #[TestDox('It can list its values as an array of strings')]
    public function test_it_can_list_its_values_as_an_array_of_strings(): void
    {
        $this->assertSame(array_column(Service::cases(), 'value'), Service::toArray());
    }

    #[TestDox('It can list its values as a comma separated string')]
    public function test_it_can_list_its_values_as_a_comma_separated_string(): void
    {
        $this->assertSame(implode(', ', Service::toArray()), Service::toListString());
    }

    #[TestDox('It fails on invalid value (i.e. value not in enum)')]
    public function test_it_fails_on_invalid_value(): void
    {
        $this->expectException(ValidationFailedException::class);

        Service::fromString('invalid');
    }
}
