<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Spec\Domain\Kanban\Jira\JiraColumn;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Ssc\Dtk\Domain\Exception\ValidationFailedException;
use Ssc\Dtk\Domain\Kanban\Jira\JiraColumn\JiraColumnName;
use Ssc\Dtk\Tests\Fixtures\Domain\Kanban\Jira\JiraColumn\JiraColumnNameFixture;

#[CoversClass(JiraColumnName::class)]
#[Small]
final class JiraColumnNameTest extends TestCase
{
    #[TestDox('It can be converted from/to string')]
    public function test_it_can_be_converted_from_and_to_string(): void
    {
        $stringJiraColumnName = JiraColumnNameFixture::makeString();
        $jiraColumnName = JiraColumnName::fromString($stringJiraColumnName);

        $this->assertInstanceOf(JiraColumnName::class, $jiraColumnName);
        $this->assertSame($stringJiraColumnName, $jiraColumnName->toString());
    }

    #[DataProvider('invalidJiraColumnNameProvider')]
    #[TestDox('It fails when raw Jira column name $scenario')]
    public function test_it_fails_when_raw_jira_column_name_is_invalid(
        string $scenario,
        string $invalidJiraColumnName,
    ): void {
        $this->expectException(ValidationFailedException::class);

        JiraColumnName::fromString($invalidJiraColumnName);
    }

    /**
     * @return \Iterator<array{
     *     scenario: string,
     *     invalidJiraColumnName: string,
     * }>
     */
    public static function invalidJiraColumnNameProvider(): \Iterator
    {
        yield [
            'scenario' => 'is empty (``)',
            'invalidJiraColumnName' => '',
        ];
    }
}
