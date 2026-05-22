<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Spec\Domain\Kanban\Jira\JiraTransition;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Ssc\Dtk\Domain\Exception\ValidationFailedException;
use Ssc\Dtk\Domain\Kanban\Jira\JiraTransition\JiraTransitionName;
use Ssc\Dtk\Tests\Fixtures\Domain\Kanban\Jira\JiraTransition\JiraTransitionNameFixture;

#[CoversClass(JiraTransitionName::class)]
#[Small]
final class JiraTransitionNameTest extends TestCase
{
    #[TestDox('It can be converted from/to string')]
    public function test_it_can_be_converted_from_and_to_string(): void
    {
        $stringJiraTransitionName = JiraTransitionNameFixture::makeString();
        $jiraTransitionName = JiraTransitionName::fromString($stringJiraTransitionName);

        $this->assertInstanceOf(JiraTransitionName::class, $jiraTransitionName);
        $this->assertSame($stringJiraTransitionName, $jiraTransitionName->toString());
    }

    #[DataProvider('invalidJiraTransitionNameProvider')]
    #[TestDox('It fails when raw Jira transition name $scenario')]
    public function test_it_fails_when_raw_jira_transition_name_is_invalid(
        string $scenario,
        string $invalidJiraTransitionName,
    ): void {
        $this->expectException(ValidationFailedException::class);

        JiraTransitionName::fromString($invalidJiraTransitionName);
    }

    /**
     * @return \Iterator<array{
     *     scenario: string,
     *     invalidJiraTransitionName: string,
     * }>
     */
    public static function invalidJiraTransitionNameProvider(): \Iterator
    {
        yield [
            'scenario' => 'is empty (``)',
            'invalidJiraTransitionName' => '',
        ];
    }
}
