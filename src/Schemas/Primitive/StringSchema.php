<?php

namespace Esliph\Validator\Schemas\Primitive;

use Esliph\Validator\Schemas\CoercibleSchema;
use Esliph\Validator\Results\ParseResult;
use Esliph\Validator\Errors\ValidatorError;
use Esliph\Validator\Validation\Rule;
use Closure;

final class StringSchema extends CoercibleSchema {

  protected function parseType(mixed $value, array $path = []): ParseResult {
    if (is_string($value)) {
      return ParseResult::ok($value);
    }

    if (!$this->coerce) {
      return ParseResult::fail([new ValidatorError($path, 'Expected string, received ' . gettype($value), 'invalid_type')]);
    }

    return ParseResult::ok((string) $value);
  }

  public function min(int $length, string|Closure|null $message = null): static {
    return $this->addRule(new Rule(
      'min',
      'too_small',
      static fn(string $value, array $params): bool => mb_strlen($value) >= $params['length'],
      $message ?? static fn(string $value, array $params): string => "Must be at least {$params['length']} characters",
      ['length' => $length]
    ));
  }

  public function max(int $length, string|Closure|null $message = null): static {
    return $this->addRule(new Rule(
      'max',
      'too_big',
      static fn(string $value, array $params): bool => mb_strlen($value) <= $params['length'],
      $message ?? static fn(string $value, array $params): string => "Must be at most {$params['length']} characters",
      ['length' => $length]
    ));
  }

  public function email(string|Closure|null $message = null): static {
    return $this->addRule(new Rule(
      'email',
      'invalid_format',
      static fn(string $value): bool => filter_var($value, FILTER_VALIDATE_EMAIL) !== false,
      $message ?? 'Invalid email address'
    ));
  }

  public function url(string|Closure|null $message = null): static {
    return $this->addRule(new Rule(
      'url',
      'invalid_format',
      static fn(string $value): bool => filter_var($value, FILTER_VALIDATE_URL) !== false,
      $message ?? 'Invalid URL'
    ));
  }

  public function regex(string $pattern, string|Closure|null $message = null): static {
    return $this->addRule(new Rule(
      'regex',
      'invalid_format',
      static fn(string $value, array $params): bool => preg_match($params['pattern'], $value) === 1,
      $message ?? 'Invalid format',
      ['pattern' => $pattern]
    ));
  }

  public function nonempty(string|Closure|null $message = null): static {
    return $this->addRule(new Rule(
      'nonempty',
      'too_small',
      static fn(string $value): bool => mb_strlen($value) > 0,
      $message ?? 'String must not be empty'
    ));
  }

  public function startsWith(string $prefix, string|Closure|null $message = null): static {
    return $this->addRule(new Rule(
      'startsWith',
      'invalid_format',
      static fn(string $value, array $params): bool => str_starts_with($value, $params['prefix']),
      $message ?? static fn(string $value, array $params): string => "Must start with '{$params['prefix']}'",
      ['prefix' => $prefix]
    ));
  }

  public function endsWith(string $suffix, string|Closure|null $message = null): static {
    return $this->addRule(new Rule(
      'endsWith',
      'invalid_format',
      static fn(string $value, array $params): bool => str_ends_with($value, $params['suffix']),
      $message ?? static fn(string $value, array $params): string => "Must end with '{$params['suffix']}'",
      ['suffix' => $suffix]
    ));
  }

  public function includes(string $substring, string|Closure|null $message = null): static {
    return $this->addRule(new Rule(
      'includes',
      'invalid_format',
      static fn(string $value, array $params): bool => str_contains($value, $params['substring']),
      $message ?? static fn(string $value, array $params): string => "Must include '{$params['substring']}'",
      ['substring' => $substring]
    ));
  }

  public function length(int $length, string|Closure|null $message = null): static {
    return $this->addRule(new Rule(
      'length',
      'invalid_length',
      static fn(string $value, array $params): bool => mb_strlen($value) === $params['length'],
      $message ?? static fn(string $value, array $params): string => "Must be exactly {$params['length']} characters",
      ['length' => $length]
    ));
  }

  public function lowercase(string|Closure|null $message = null): static {
    return $this->addRule(new Rule(
      'lowercase',
      'invalid_case',
      static fn(string $value): bool => mb_strtolower($value) === $value,
      $message ?? 'Must be all lowercase'
    ));
  }

  public function uppercase(string|Closure|null $message = null): static {
    return $this->addRule(new Rule(
      'uppercase',
      'invalid_case',
      static fn(string $value): bool => mb_strtoupper($value) === $value,
      $message ?? 'Must be all uppercase'
    ));
  }

  public function trim(): static {
    return $this->transform(static fn(string $value): string => trim($value));
  }

  public function toLowerCase(): static {
    return $this->transform(static fn(string $value): string => mb_strtolower($value));
  }

  public function toUpperCase(): static {
    return $this->transform(static fn(string $value): string => mb_strtoupper($value));
  }
}
