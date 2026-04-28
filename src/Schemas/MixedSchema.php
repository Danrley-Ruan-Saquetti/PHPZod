<?php

namespace Esliph\Validator\Schemas;

use Esliph\Validator\Results\ParseResult;
use Override;

class MixedSchema extends Schema {

  #[Override]
  protected function parseType(mixed $value, array $path = []): ParseResult {
    return ParseResult::ok($value);
  }
}
