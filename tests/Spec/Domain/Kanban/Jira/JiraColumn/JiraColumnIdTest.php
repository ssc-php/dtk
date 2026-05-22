<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Spec\Domain\Kanban\Jira\JiraColumn;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Ssc\Dtk\Domain\Exception\ValidationFailedException;
use Ssc\Dtk\Domain\Kanban\Jira\JiraColumn\JiraColumnId;
use Ssc\Dtk\Tests\Fixtures\Domain\Kanban\Jira\JiraColumn\JiraColumnIdFixture;

#[CoversClass(JiraColumnId::class)]
#[Small]
final class JiraColumnIdTest extends TestCase
{
    #[TestDox('It can be converted from/to string')]
    public function test_it_can_be_converted_from_and_to_string(): void
    {
        $stringJiraColumnId = JiraColumnIdFixture::makeString();
        $jiraColumnId = JiraColumnId::fromString($stringJiraColumnId);

        $this->assertInstanceOf(JiraColumnId::class, $jiraColumnId);
        $this->assertSame($stringJiraColumnId, $jiraColumnId->toString());
    }

    #[DataProvider('invalidJiraColumnIdProvider')]
    #[TestDox('It fails when raw Jira column id $scenario')]
    public function test_it_fails_when_raw_jira_column_id_is_invalid(
        string $scenario,
        string $invalidJiraColumnId,
    ): void {
        $this->expectException(ValidationFailedException::class);

        JiraColumnId::fromString($invalidJiraColumnId);
    }

    /**
     * @return \Iterator<array{
     *     scenario: string,
     *     invalidJiraColumnId: string,
     * }>
     */
    public static function invalidJiraColumnIdProvider(): \Iterator
    {
        yield [
            'scenario' => 'is empty (``)',
            'invalidJiraColumnId' => '',
        ];
    }
}
