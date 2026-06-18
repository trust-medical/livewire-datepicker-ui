<?php

declare(strict_types=1);

namespace TrustMedical\LivewireDatepickerUi\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use TrustMedical\LivewireDatepickerUi\Application\UseCases\DisabledRuleFactory;
use TrustMedical\LivewireDatepickerUi\Application\UseCases\ParseInputUseCase;
use TrustMedical\LivewireDatepickerUi\Application\UseCases\PickerConfigFactory;
use TrustMedical\LivewireDatepickerUi\Application\UseCases\ValidateSelectionUseCase;
use TrustMedical\LivewireDatepickerUi\Support\Locale;

/**
 * Optional Laravel validation rule that enforces the same constraints a picker
 * applies in the browser (format + min/max + disabled dates/weekdays/times).
 *
 * Usage:
 *   'starts_at' => ['required', new ValidSelection('datetime', [
 *       'valueFormat' => 'Y-m-d\TH:i:s',
 *       'min' => now()->toDateString(),
 *       'disabledWeekdays' => [0, 6],
 *   ])],
 */
final class ValidSelection implements ValidationRule
{
    /**
     * @param  array<string, mixed>  $options  Same keys as the component props
     *                                         (valueFormat, min, max, disabledDates,
     *                                         disabledWeekdays, disabledTimes, minuteStep, hourCycle).
     */
    public function __construct(
        private readonly string $mode = 'date',
        private readonly array $options = [],
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || trim($value) === '') {
            return; // Empty is the responsibility of the `required` rule.
        }

        $locale = Locale::english();

        /** @var array<string, mixed> $packageConfig */
        $packageConfig = (array) config('datepicker', []);

        $config = (new PickerConfigFactory)->create(
            array_merge(['mode' => $this->mode], $this->options),
            $packageConfig,
            $locale,
            'validation-rule',
        );

        $parsed = (new ParseInputUseCase)->execute($value, $config->valueFormat, $config->mode, $locale);

        if ($parsed->failed() || $parsed->value === null) {
            $fail('datepicker::datepicker.validation.format')->translate();

            return;
        }

        $rule = (new DisabledRuleFactory)->fromConfig($config);
        $result = (new ValidateSelectionUseCase)->execute($parsed->value, $rule);

        if ($result->failed()) {
            $fail('datepicker::datepicker.validation.not_allowed')->translate();
        }
    }
}
