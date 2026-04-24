<?php

namespace Zod\Errors;

readonly final class ZodError {

  public function __construct(
    public array $path,
    public string $message,
    public string $code,
  ) {
  }

  public function pathString(): string {
    return implode('.', $this->path);
  }
}
