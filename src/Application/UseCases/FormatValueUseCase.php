<?php

declare(strict_types=1);

namespace TrustMedical\LivewireDatepickerUi\Application\UseCases;

use TrustMedical\LivewireDatepickerUi\Application\DTO\FormatResult;
use TrustMedical\LivewireDatepickerUi\Domain\Enums\PickerMode;
use TrustMedical\LivewireDatepickerUi\Domain\Exceptions\InvalidDateFormatException;
use TrustMedical\LivewireDatepickerUi\Domain\Services\DateFormatter;
use TrustMedical\LivewireDatepickerUi\Domain\Services\DateParser;
use TrustMedical\LivewireDatepickerUi\Domain\ValueObjects\DateTimeValue;
use TrustMedical\LivewireDatepickerUi\Domain\ValueObjects\DateValue;
use TrustMedical\LivewireDatepickerUi\Domain\ValueObjects\TimeValue;
use TrustMedical\LivewireDatepickerUi\Support\Locale;

/**
 * Produces the submit (`value-format`) and display (`display-format`) strings
 * for a value, keeping the two representations cleanly separated.
 */
final class FormatValueUseCase
{
    public function __construct(
        private readonly DateFormatter $formatter = new DateFormatter,
        private readonly DateParser $parser = new DateParser,
    ) {}

    public function fromValue(
        DateValue|TimeValue|DateTimeValue $value,
        string $valueFormat,
        string $displayFormat,
        ?Locale $locale = null,
    ): FormatResult {
        return new FormatResult(
            $this->formatter->format($value, $valueFormat, $locale),
            $this->formatter->format($value, $displayFormat, $locale),
        );
    }

    /**
     * Re-derive the display + normalized value strings from a stored
     * value-format string. Returns null for an empty or unparseable input so
     * the caller can render an empty field rather than crash.
     */
    public function fromValueString(
        string $stored,
        string $valueFormat,
        string $displayFormat,
        PickerMode $mode,
        ?Locale $locale = null,
    ): ?FormatResult {
        $trimmed = trim($stored);

        if ($trimmed === '') {
            return null;
        }

        try {
            $value = $this->parser->parse($trimmed, $valueFormat, $mode, $locale);
        } catch (InvalidDateFormatException) {
            return null;
        }

        return $this->fromValue($value, $valueFormat, $displayFormat, $locale);
    }
}
