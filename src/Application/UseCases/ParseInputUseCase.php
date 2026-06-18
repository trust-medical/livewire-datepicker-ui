<?php

declare(strict_types=1);

namespace TrustMedical\LivewireDatepickerUi\Application\UseCases;

use TrustMedical\LivewireDatepickerUi\Application\DTO\ParseResult;
use TrustMedical\LivewireDatepickerUi\Domain\Enums\PickerMode;
use TrustMedical\LivewireDatepickerUi\Domain\Exceptions\InvalidDateFormatException;
use TrustMedical\LivewireDatepickerUi\Domain\Services\DateParser;
use TrustMedical\LivewireDatepickerUi\Support\Locale;

/**
 * Parses an input string into a value object, returning an explicit
 * {@see ParseResult} instead of throwing.
 */
final class ParseInputUseCase
{
    public function __construct(
        private readonly DateParser $parser = new DateParser,
    ) {}

    public function execute(
        string $input,
        string $format,
        PickerMode $mode,
        ?Locale $locale = null,
    ): ParseResult {
        $trimmed = trim($input);

        if ($trimmed === '') {
            return ParseResult::failure('empty');
        }

        try {
            return ParseResult::success($this->parser->parse($trimmed, $format, $mode, $locale));
        } catch (InvalidDateFormatException $exception) {
            return ParseResult::failure($exception->getMessage());
        }
    }
}
