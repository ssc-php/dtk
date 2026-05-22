<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Spec\Domain\Kanban;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Ssc\Dtk\Domain\Exception\ValidationFailedException;
use Ssc\Dtk\Domain\Kanban\BaseUrl;
use Ssc\Dtk\Tests\Fixtures\Domain\Kanban\BaseUrlFixture;

#[CoversClass(BaseUrl::class)]
#[Small]
final class BaseUrlTest extends TestCase
{
    #[TestDox('It can be converted from/to string')]
    public function test_it_can_be_converted_from_and_to_string(): void
    {
        $stringBaseUrl = BaseUrlFixture::makeString();
        $baseUrl = BaseUrl::fromString($stringBaseUrl);

        $this->assertInstanceOf(BaseUrl::class, $baseUrl);
        $this->assertSame($stringBaseUrl, $baseUrl->toString());
    }

    #[DataProvider('invalidBaseUrlProvider')]
    #[TestDox('It fails when: raw base URL $scenario')]
    public function test_it_fails_when_raw_base_url_is_invalid(
        string $scenario,
        string $invalidBaseUrl,
    ): void {
        $this->expectException(ValidationFailedException::class);

        BaseUrl::fromString($invalidBaseUrl);
    }

    /**
     * @return \Iterator<array{
     *     scenario: string,
     *     invalidBaseUrl: string,
     * }>
     */
    public static function invalidBaseUrlProvider(): \Iterator
    {
        yield [
            'scenario' => 'is empty (``)',
            'invalidBaseUrl' => '',
        ];
        $url = 'company.atlassian.net';
        yield [
            'scenario' => "has no scheme (`{$url}`)",
            'invalidBaseUrl' => $url,
        ];
        $url = 'ftp://company.atlassian.net';
        yield [
            'scenario' => "has wrong scheme (`{$url}`)",
            'invalidBaseUrl' => $url,
        ];
        $url = 'https://company.atlassian.net/browse';
        yield [
            'scenario' => "has a path (`{$url}`)",
            'invalidBaseUrl' => $url,
        ];
    }
}
