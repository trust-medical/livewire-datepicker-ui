<?php

declare(strict_types=1);

namespace TrustMedical\LivewireDatepickerUi\Domain\Exceptions;

use RuntimeException;

/**
 * Base type for every exception thrown by the package.
 *
 * Catching this type lets a consumer handle all picker failures in one place
 * without depending on the concrete subclasses.
 */
class DatepickerException extends RuntimeException {}
