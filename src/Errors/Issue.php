<?php

namespace Esliph\Validator\Errors;

readonly final class Issue {

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
