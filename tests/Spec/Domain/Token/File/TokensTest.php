<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Spec\Domain\Token\File;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Ssc\Dtk\Domain\Exception\ServerErrorException;
use Ssc\Dtk\Domain\Exception\ValidationFailedException;
use Ssc\Dtk\Domain\Token\File\Tokens;
use Ssc\Dtk\Tests\Fixtures\Domain\Token\ServiceFixture;
use Ssc\Dtk\Tests\Fixtures\Domain\Token\TokenFixture;

#[CoversClass(Tokens::class)]
final class TokensTest extends TestCase
{
    #[TestDox('It can be converted from/to array')]
    public function test_it_can_be_converted_from_to_array(): void
    {
        $service = ServiceFixture::makeString();
        $tokenValue = TokenFixture::makeString();

        $tokens = Tokens::fromArray([$service => $tokenValue]);

        $this->assertSame([$service => $tokenValue], $tokens->toArray());
    }

    /**
     * @param class-string<\Throwable> $exception
     * @param array<array-key, mixed>  $rawTokens
     */
    #[DataProvider('invalidArrayProvider')]
    #[TestDox('It fails to parse when: $scenario')]
    public function test_it_fails_to_parse_when(
        string $scenario,
        array $rawTokens,
        string $exception,
    ): void {
        $this->expectException($exception);
        Tokens::fromArray($rawTokens);
    }

    /**
     * @return \Iterator<array{
     *     scenario: string,
     *     rawTokens: array<array-key, mixed>,
     *     exception: class-string<\Throwable>,
     * }>
     */
    public static function invalidArrayProvider(): \Iterator
    {
        yield [
            'scenario' => 'service name is invalid',
            'rawTokens' => ['unknown' => 'token'],
            'exception' => ValidationFailedException::class,
        ];

        $service = ServiceFixture::makeString();
        yield [
            'scenario' => 'token value is not a string',
            'rawTokens' => [$service => 123],
            'exception' => ServerErrorException::class,
        ];
        yield [
            'scenario' => 'token value is empty',
            'rawTokens' => [$service => ''],
            'exception' => ValidationFailedException::class,
        ];
    }
}
