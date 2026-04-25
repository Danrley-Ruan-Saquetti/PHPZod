<?php

namespace Esliph\Validation;

use Closure;

readonly final class Rule {

  public function __construct(
    public string $name,
    public string $code,
    public Closure $check,
    public string|Closure|null $message = null,
    public array $params = [],
  ) {
  }

  public function validate(mixed $value): bool {
    return (bool) ($this->check)($value, $this->params);
  }

  public function resolveMessage(mixed $value): string {
    if ($this->message instanceof Closure) {
      return ($this->message)($value, $this->params) ?? '';
    }

    return $this->message ?? '';
  }
}
