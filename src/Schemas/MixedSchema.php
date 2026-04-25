<?php

namespace Esliph\Schemas;

use Esliph\Results\ParseResult;

class MixedSchema extends Schema {

  protected function parseType(mixed $value, array $path = []): ParseResult {
    return ParseResult::ok($value);
  }
}
