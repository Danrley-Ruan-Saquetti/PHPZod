<?php

namespace Esliph\Schemas\Primitive;

use Esliph\Schemas\Schema;
use Esliph\Results\ParseResult;
use Esliph\Errors\ValidatorError;

final class BooleanSchema extends Schema {

  protected bool $coerce = false;

  protected function parseType(mixed $value, array $path = []): ParseResult {
    if ($this->coerce) {
      $value = $this->coerceToBoolean($value);
    }

    if (!is_bool($value)) {
      return ParseResult::fail([new ValidatorError($path, 'Expected boolean, received ' . gettype($value), 'invalid_type')]);
    }

    return ParseResult::ok($value);
  }

  private function coerceToBoolean(mixed $value): bool {
    if (is_bool($value)) {
      return $value;
    }

    if (is_string($value)) {
      $lower = mb_strtolower(trim($value));
      if (in_array($lower, ['true', '1', 'yes', 'on'], true)) {
        return true;
      }
      if (in_array($lower, ['false', '0', 'no', 'off', ''], true)) {
        return false;
      }
    }

    if (is_numeric($value)) {
      return (bool) $value;
    }

    if (is_null($value)) {
      return false;
    }

    if (is_array($value)) {
      return !empty($value);
    }

    return (bool) $value;
  }

  public function coerce(): static {
    $clone = clone $this;
    $clone->coerce = true;

    return $clone;
  }
}
