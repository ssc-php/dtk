<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Fixtures\Domain\Kanban;

use Ssc\Dtk\Domain\Kanban\KanbanUser;
use Ssc\Dtk\Tests\Fixtures\Domain\Kanban\KanbanUser\KanbanUserIdFixture;

final readonly class KanbanUserFixture
{
    public static function make(): KanbanUser
    {
        return new KanbanUser(
            KanbanUserIdFixture::make(),
        );
    }
}
