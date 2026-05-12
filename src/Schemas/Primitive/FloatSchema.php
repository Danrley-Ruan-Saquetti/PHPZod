<?php

namespace Esliph\Validator\Schemas\Primitive;

use Override;

use Esliph\Validator\Errors\Issue;
use Esliph\Validator\Results\ParseResult;

class FloatSchema extends NumberSchema {

  #[Override]
  protected function parseType(mixed $value, array $path = []): ParseResult {
    if (is_float($value)) {
      return ParseResult::ok($value);
    }

    if (is_int($value)) {
      return ParseResult::ok((int) $value);
    }

    if ($this->coerce && is_string($value) && is_numeric($value)) {
      return ParseResult::ok((float) $value);
    }

    return ParseResult::fail([new Issue($path, 'Expected float, received ' . gettype($value), 'invalid_type')]);
  }
}
