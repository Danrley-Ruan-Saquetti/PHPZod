<?php

namespace Esliph\Validator\Schemas\Primitive;

use Override;

use Esliph\Validator\Errors\Issue;
use Esliph\Validator\Results\ParseResult;

class IntegerSchema extends NumberSchema {

  #[Override]
  protected function parseType(mixed $value, array $path = []): ParseResult {
    if (is_int($value)) {
      return ParseResult::ok($value);
    }

    if (is_float($value)) {
      if (fmod($value, 1.0) === 0.0) {
        return ParseResult::ok((int) $value);
      }

      return ParseResult::fail([new Issue($path, 'Expected integer, received float with decimal part', 'invalid_type')]);
    }

    if ($this->coerce && is_string($value) && is_numeric($value)) {
      $value = (float) $value;

      if (fmod($value, 1.0) !== 0.0) {
        return ParseResult::fail([new Issue($path, 'Expected integer, received non-integer value', 'invalid_type')]);
      }

      return ParseResult::ok((int) $value);
    }

    return ParseResult::fail([new Issue($path, 'Expected integer, received ' . gettype($value), 'invalid_type')]);
  }
}
