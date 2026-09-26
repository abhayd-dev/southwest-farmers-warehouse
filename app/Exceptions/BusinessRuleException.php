<?php

namespace App\Exceptions;

/**
 * An expected, human-readable business rule ("Insufficient Stock.
 * Available: 676.00", "Cannot delete the Main Store Manager.") rather than
 * a technical failure. Shown to the user as-is in a warning, without the
 * technical detail App\Support\ErrorMessage adds to real errors.
 *
 * Extends \Exception, so existing catch (\Exception $e) blocks still catch it.
 */
class BusinessRuleException extends \Exception
{
}
