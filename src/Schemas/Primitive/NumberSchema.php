<?php

namespace Esliph\Validator\Schemas\Primitive;

use Esliph\Validator\Schemas\CoercibleSchema;
use Esliph\Validator\Results\ParseResult;
use Esliph\Validator\Errors\ValidatorError;
use Esliph\Validator\Validation\Rule;
use Closure;

final class NumberSchema extends CoercibleSchema {

  protected bool $integer = false;

  protected function parseType(mixed $value, array $path = []): ParseResult {
    if ($this->coerce && is_numeric($value)) {
      $value = $this->integer ? (int) $value : (float) $value;
    }

    if ($this->integer) {
      if (!is_int($value)) {
        return ParseResult::fail([new ValidatorError($path, 'Expected integer, received ' . gettype($value), 'invalid_type')]);
      }
    } else if (!is_int($value) && !is_float($value)) {
      return ParseResult::fail([new ValidatorError($path, 'Expected number, received ' . gettype($value), 'invalid_type')]);
    }

    return ParseResult::ok($value);
  }

  public function int(): static {
    $clone = clone $this;
    $clone->integer = true;

    return $clone;
  }

  public function min(int|float $min, string|Closure|null $message = null): static {
    return $this->gte($min, $message);
  }

  public function max(int|float $max, string|Closure|null $message = null): static {
    return $this->lte($max, $message);
  }

  public function gt(int|float $min, string|Closure|null $message = null): static {
    return $this->addRule(new Rule(
      'gt',
      'too_small',
      static fn(mixed $value, array $params): bool => $value > $params['min'],
      $message ?? static fn(mixed $value, array $params): string => "Must be greater than {$params['min']}",
      ['min' => $min]
    ));
  }

  public function gte(int|float $min, string|Closure|null $message = null): static {
    return $this->addRule(new Rule(
      'gte',
      'too_small',
      static fn(mixed $value, array $params): bool => $value >= $params['min'],
      $message ?? static fn(mixed $value, array $params): string => "Must be greater than or equal to {$params['min']}",
      ['min' => $min]
    ));
  }

  public function lt(int|float $max, string|Closure|null $message = null): static {
    return $this->addRule(new Rule(
      'lt',
      'too_big',
      static fn(mixed $value, array $params): bool => $value < $params['max'],
      $message ?? static fn(mixed $value, array $params): string => "Must be less than {$params['max']}",
      ['max' => $max]
    ));
  }

  public function lte(int|float $max, string|Closure|null $message = null): static {
    return $this->addRule(new Rule(
      'lte',
      'too_big',
      static fn(mixed $value, array $params): bool => $value <= $params['max'],
      $message ?? static fn(mixed $value, array $params): string => "Must be less than or equal to {$params['max']}",
      ['max' => $max]
    ));
  }

  public function nonnegative(string|Closure|null $message = null): static {
    return $this->gt(0, $message ?? 'Must be a positive number');
  }

  public function nonpositive(string|Closure|null $message = null): static {
    return $this->lt(0, $message ?? 'Must be a negative number');
  }

  public function positive(string|Closure|null $message = null): static {
    return $this->gte(0, $message ?? 'Must be a non-negative number');
  }

  public function negative(string|Closure|null $message = null): static {
    return $this->lte(0, $message ?? 'Must be a non-positive number');
  }

  public function between(int|float $min, int|float $max, string|Closure|null $message = null): static {
    return $this->addRule(new Rule(
      'between',
      'out_of_range',
      static fn(mixed $value, array $params): bool => $value >= $params['min'] && $value <= $params['max'],
      $message ?? static fn(mixed $value, array $params): string => "Must be between {$params['min']} and {$params['max']}",
      ['min' => $min, 'max' => $max]
    ));
  }

  public function multipleOf(int|float $divisor, string|Closure|null $message = null): static {
    return $this->addRule(new Rule(
      'multipleOf',
      'not_multiple',
      static fn(mixed $value, array $params): bool => fmod($value, $params['divisor']) === 0.0,
      $message ?? static fn(mixed $value, array $params): string => "Must be a multiple of {$params['divisor']}",
      ['divisor' => $divisor]
    ));
  }
}
