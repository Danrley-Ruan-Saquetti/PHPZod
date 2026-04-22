<?php

namespace Zod\Schemas\Primitive;

use Zod\Schemas\Schema;
use Zod\Results\ParseResult;
use Zod\Errors\ZodError;

class BooleanSchema extends Schema {

  protected $coerce = false;

  /**
   * @inheritDoc
   */
  protected function parseType($value, $path = []) {
    if ($this->coerce) {
      $value = $this->coerceToBoolean($value);
    }

    if (!is_bool($value)) {
      return ParseResult::fail([new ZodError($path, 'Expected boolean, received ' . gettype($value), 'invalid_type')]);
    }

    return ParseResult::ok($value);
  }

  /**
   * @param mixed $value
   * @return bool
   */
  private function coerceToBoolean($value) {
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

  /**
   * @return static
   */
  public function coerce() {
    $clone = clone $this;
    $clone->coerce = true;

    return $clone;
  }
}
