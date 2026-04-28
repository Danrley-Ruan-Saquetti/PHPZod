<?php

namespace Esliph\Validator\Schemas\Primitive;

use Esliph\Validator\Results\ParseResult;
use Esliph\Validator\Errors\Issue;
use Esliph\Validator\Schemas\CoercibleSchema;
use Override;

final class BooleanSchema extends CoercibleSchema {

  #[Override]
  protected function parseType(mixed $value, array $path = []): ParseResult {
    if (is_bool($value)) {
      return ParseResult::ok($value);
    }

    if (!$this->coerce) {
      return ParseResult::fail([new Issue($path, 'Expected boolean, received ' . gettype($value), 'invalid_type')]);
    }

    return ParseResult::ok($this->coerceToBoolean($value));
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
}
