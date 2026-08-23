<?php

namespace App\Enums;

/**
 * Encodes the membership lifecycle from docs/DOMAIN_MODEL.md §10:
 *
 *   draft
 *     ↓
 *   active
 *     ├── frozen
 *     │     ↓
 *     │   active
 *     ├── cancelled
 *     └── expired
 *
 * The transition table lives here — on the enum — rather than scattered
 * across individual Action classes, so "what's a legal transition" has
 * exactly one source of truth that docs/TESTING.md §6's "invalid
 * transitions rejected" tests can exercise directly via canTransitionTo().
 *
 * Cancelled and Expired are terminal: nothing transitions out of them.
 * A draft can also go straight to cancelled (abandoned before it was ever
 * activated) without ever touching Active.
 */
enum MembershipStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Frozen = 'frozen';
    case Cancelled = 'cancelled';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Active => 'Active',
            self::Frozen => 'Frozen',
            self::Cancelled => 'Cancelled',
            self::Expired => 'Expired',
        };
    }

    /**
     * @return array<int, self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Draft => [self::Active, self::Cancelled],
            self::Active => [self::Frozen, self::Cancelled, self::Expired],
            self::Frozen => [self::Active, self::Cancelled],
            self::Cancelled => [],
            self::Expired => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }

    public function isTerminal(): bool
    {
        return $this === self::Cancelled || $this === self::Expired;
    }
}
