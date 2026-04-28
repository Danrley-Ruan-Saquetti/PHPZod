<?php

namespace Esliph\Validator\Errors;

readonly final class ValidatorError {

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
