<?php

declare(strict_types=1);

namespace TrustMedical\LivewireDatepickerUi\Domain\ValueObjects;

use DateTimeInterface;
use InvalidArgumentException;

/**
 * An immutable wall-clock time (hour / minute / second), no date, no timezone.
 */
final class TimeValue
{
    public function __construct(
        public readonly int $hour,
        public readonly int $minute,
        public readonly int $second = 0,
    ) {
        if ($hour < 0 || $hour > 23) {
            throw new InvalidArgumentException("Hour must be between 0 and 23, got {$hour}.");
        }

        if ($minute < 0 || $minute > 59) {
            throw new InvalidArgumentException("Minute must be between 0 and 59, got {$minute}.");
        }

        if ($second < 0 || $second > 59) {
            throw new InvalidArgumentException("Second must be between 0 and 59, got {$second}.");
        }
    }

    public static function fromComponents(int $hour, int $minute, int $second = 0): self
    {
        return new self($hour, $minute, $second);
    }

    /** @param array{hour: int, minute: int, second?: int} $data */
    public static function fromArray(array $data): self
    {
        return new self($data['hour'], $data['minute'], $data['second'] ?? 0);
    }

    public static function fromDateTimeInterface(DateTimeInterface $time): self
    {
        return new self(
            (int) $time->format('G'),
            (int) $time->format('i'),
            (int) $time->format('s'),
        );
    }

    public function totalSeconds(): int
    {
        return $this->hour * 3600 + $this->minute * 60 + $this->second;
    }

    public function totalMinutes(): int
    {
        return $this->hour * 60 + $this->minute;
    }

    public function equals(self $other): bool
    {
        return $this->totalSeconds() === $other->totalSeconds();
    }

    public function compareTo(self $other): int
    {
        return $this->totalSeconds() <=> $other->totalSeconds();
    }

    public function isBefore(self $other): bool
    {
        return $this->compareTo($other) < 0;
    }

    public function isAfter(self $other): bool
    {
        return $this->compareTo($other) > 0;
    }

    public function withHour(int $hour): self
    {
        return new self($hour, $this->minute, $this->second);
    }

    public function withMinute(int $minute): self
    {
        return new self($this->hour, $minute, $this->second);
    }

    public function withSecond(int $second): self
    {
        return new self($this->hour, $this->minute, $second);
    }

    /** The 12-hour-clock hour (1..12) for the `h`/`g` tokens. */
    public function hour12(): int
    {
        $hour = $this->hour % 12;

        return $hour === 0 ? 12 : $hour;
    }

    public function isPm(): bool
    {
        return $this->hour >= 12;
    }

    public function toIsoString(): string
    {
        return sprintf('%02d:%02d:%02d', $this->hour, $this->minute, $this->second);
    }

    /** @return array{hour: int, minute: int, second: int} */
    public function toArray(): array
    {
        return ['hour' => $this->hour, 'minute' => $this->minute, 'second' => $this->second];
    }
}
