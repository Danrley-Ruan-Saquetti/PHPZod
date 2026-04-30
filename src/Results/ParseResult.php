<?php

namespace Esliph\Validator\Results;

use Esliph\Validator\Errors\Issue;

readonly final class ParseResult {

  /**
   * @param Issue[] $issues
   */
  private function __construct(
    public bool $success,
    public mixed $data = null,
    public array $issues = []
  ) {
  }

  public static function ok(mixed $data = null): self {
    return new self(
      success: true,
      data: $data
    );
  }

  /**
   * @param Issue[] $issues
   */
  public static function fail(array $issues): self {
    return new self(
      success: false,
      issues: $issues
    );
  }
}
