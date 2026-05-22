<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Spec\Domain\Kanban\Ticket;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Ssc\Dtk\Domain\Kanban\Ticket\Slug;
use Ssc\Dtk\Domain\Kanban\Ticket\Type;
use Ssc\Dtk\Tests\Fixtures\Domain\Kanban\Ticket\TypeFixture;

#[CoversClass(Type::class)]
#[Small]
final class TypeTest extends TestCase
{
    #[TestDox('It can be converted from/to string')]
    public function test_it_can_be_converted_from_and_to_string(): void
    {
        $stringType = TypeFixture::makeString();
        $type = Type::fromString($stringType);

        $this->assertInstanceOf(Type::class, $type);
        $this->assertSame($stringType, $type->toString());
    }

    #[TestDox('It can be converted to Slug')]
    public function test_it_can_be_converted_to_slug(): void
    {
        $type = TypeFixture::make();
        $slug = $type->toSlug();

        $this->assertInstanceOf(Slug::class, $slug);
        $this->assertSame('bug', $slug->toString());
    }
}
