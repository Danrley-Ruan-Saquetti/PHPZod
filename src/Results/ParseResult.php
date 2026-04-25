<?php

namespace Esliph\Results;

use Esliph\Errors\ValidatorError;

readonly final class ParseResult {

  /**
   * @param ValidatorError[] $errors
   */
  private function __construct(
    public bool $success,
    public mixed $data = null,
    public array $errors = []
  ) {
  }

  public static function ok(mixed $data = null): self {
    return new self(true, $data);
  }

  /**
   * @param ValidatorError[] $errors
   */
  public static function fail(array $errors): self {
    return new self(false, null, $errors);
  }
}
