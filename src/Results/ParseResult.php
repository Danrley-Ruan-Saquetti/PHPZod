<?php

namespace Zod\Results;

use Zod\Errors\ZodError;

readonly final class ParseResult {

  /**
   * @param ZodError[] $errors
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
   * @param ZodError[] $errors
   */
  public static function fail(array $errors): self {
    return new self(false, null, $errors);
  }
}
