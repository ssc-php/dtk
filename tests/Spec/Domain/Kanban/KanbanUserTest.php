<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Spec\Domain\Kanban;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Ssc\Dtk\Domain\Kanban\KanbanUser;
use Ssc\Dtk\Tests\Fixtures\Domain\Kanban\KanbanUser\KanbanUserIdFixture;

#[CoversClass(KanbanUser::class)]
#[Small]
final class KanbanUserTest extends TestCase
{
    #[TestDox('It has KanbanUserId')]
    public function test_it_has_kanban_user_id(): void
    {
        $kanbanUserId = KanbanUserIdFixture::make();
        $kanbanUser = new KanbanUser($kanbanUserId);

        $this->assertSame($kanbanUserId, $kanbanUser->kanbanUserId);
    }
}
