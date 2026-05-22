<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Spec\Domain\Kanban\Ticket;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Ssc\Dtk\Domain\Kanban\Ticket\Slug;
use Ssc\Dtk\Domain\Kanban\Ticket\Title;
use Ssc\Dtk\Tests\Fixtures\Domain\Kanban\Ticket\SlugFixture;
use Ssc\Dtk\Tests\Fixtures\Domain\Kanban\Ticket\TitleFixture;

#[CoversClass(Title::class)]
#[Small]
final class TitleTest extends TestCase
{
    #[TestDox('It can be converted from/to string')]
    public function test_it_can_be_converted_from_and_to_string(): void
    {
        $stringTitle = TitleFixture::makeString();
        $title = Title::fromString($stringTitle);

        $this->assertInstanceOf(Title::class, $title);
        $this->assertSame($stringTitle, $title->toString());
    }

    #[TestDox('It can be converted to Slug')]
    public function test_it_can_be_converted_to_slug(): void
    {
        $title = TitleFixture::make();
        $slug = $title->toSlug();

        $this->assertInstanceOf(Slug::class, $slug);
        $this->assertSame(SlugFixture::makeString(), $slug->toString());
    }
}
