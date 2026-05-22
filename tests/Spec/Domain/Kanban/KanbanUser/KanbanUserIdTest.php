<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Spec\Domain\Kanban\KanbanUser;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Ssc\Dtk\Domain\Exception\ValidationFailedException;
use Ssc\Dtk\Domain\Kanban\KanbanUser\KanbanUserId;
use Ssc\Dtk\Tests\Fixtures\Domain\Kanban\KanbanUser\KanbanUserIdFixture;

#[CoversClass(KanbanUserId::class)]
#[Small]
final class KanbanUserIdTest extends TestCase
{
    #[TestDox('It can be converted from/to string')]
    public function test_it_can_be_converted_from_and_to_string(): void
    {
        $stringKanbanUserId = KanbanUserIdFixture::makeString();
        $kanbanUserId = KanbanUserId::fromString($stringKanbanUserId);

        $this->assertInstanceOf(KanbanUserId::class, $kanbanUserId);
        $this->assertSame($stringKanbanUserId, $kanbanUserId->toString());
    }

    #[DataProvider('invalidKanbanUserIdProvider')]
    #[TestDox('It fails when raw kanban user id $scenario')]
    public function test_it_fails_when_raw_kanban_user_id_is_invalid(
        string $scenario,
        string $invalidKanbanUserId,
    ): void {
        $this->expectException(ValidationFailedException::class);

        KanbanUserId::fromString($invalidKanbanUserId);
    }

    /**
     * @return \Iterator<array{
     *     scenario: string,
     *     invalidKanbanUserId: string,
     * }>
     */
    public static function invalidKanbanUserIdProvider(): \Iterator
    {
        yield [
            'scenario' => 'is empty',
            'invalidKanbanUserId' => '',
        ];
    }
}
