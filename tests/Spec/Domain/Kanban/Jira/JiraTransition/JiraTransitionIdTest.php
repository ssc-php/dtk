<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Spec\Domain\Kanban\Jira\JiraTransition;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Ssc\Dtk\Domain\Exception\ValidationFailedException;
use Ssc\Dtk\Domain\Kanban\Jira\JiraTransition\JiraTransitionId;
use Ssc\Dtk\Tests\Fixtures\Domain\Kanban\Jira\JiraTransition\JiraTransitionIdFixture;

#[CoversClass(JiraTransitionId::class)]
#[Small]
final class JiraTransitionIdTest extends TestCase
{
    #[TestDox('It can be converted from/to string')]
    public function test_it_can_be_converted_from_and_to_string(): void
    {
        $stringJiraTransitionId = JiraTransitionIdFixture::makeString();
        $jiraTransitionId = JiraTransitionId::fromString($stringJiraTransitionId);

        $this->assertInstanceOf(JiraTransitionId::class, $jiraTransitionId);
        $this->assertSame($stringJiraTransitionId, $jiraTransitionId->toString());
    }

    #[DataProvider('invalidJiraTransitionIdProvider')]
    #[TestDox('It fails when raw Jira transition id $scenario')]
    public function test_it_fails_when_raw_jira_transition_id_is_invalid(
        string $scenario,
        string $invalidJiraTransitionId,
    ): void {
        $this->expectException(ValidationFailedException::class);

        JiraTransitionId::fromString($invalidJiraTransitionId);
    }

    /**
     * @return \Iterator<array{
     *     scenario: string,
     *     invalidJiraTransitionId: string,
     * }>
     */
    public static function invalidJiraTransitionIdProvider(): \Iterator
    {
        yield [
            'scenario' => 'is empty (``)',
            'invalidJiraTransitionId' => '',
        ];
    }
}
