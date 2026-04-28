<?php

namespace Esliph\Validator\Schemas;

use Esliph\Validator\Results\ParseResult;

class MixedSchema extends Schema {

  protected function parseType(mixed $value, array $path = []): ParseResult {
    return ParseResult::ok($value);
  }
}
