<?php

declare(strict_types=1);

namespace Ssc\Dtk\Domain\Kanban\Jira;

use Ssc\Dtk\Domain\Exception\ValidationFailedException;
use Ssc\Dtk\Domain\Kanban\BaseUrl;
use Ssc\Dtk\Domain\Kanban\Ticket\TicketId;

/**
 * @object-type ValueObject
 */
final readonly class JiraTicketUrl
{
    private function __construct(
        private string $baseUrl,
        private string $ticketId,
    ) {
    }

    public function toBaseUrl(): BaseUrl
    {
        return BaseUrl::fromString($this->baseUrl);
    }

    public function toTicketId(): TicketId
    {
        return TicketId::fromString($this->ticketId);
    }

    public function toString(): string
    {
        return "{$this->baseUrl}/browse/{$this->ticketId}";
    }

    /**
     * @throws ValidationFailedException If $value isn't a valid Jira ticket URL
     */
    public static function fromString(string $value): self
    {
        if (1 !== preg_match('#^(https?://[^/]+)/browse/([A-Z][A-Z0-9]+-\d+)$#', $value, $matches)) {
            throw ValidationFailedException::make(
                "Invalid \"ticketUrl\" parameter: it should be a valid Jira ticket URL (`https://<dns>/browse/<ticket-id`) (`{$value}` given)",
            );
        }

        return new self($matches[1], $matches[2]);
    }
}
