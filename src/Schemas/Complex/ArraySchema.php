<?php

namespace Esliph\Schemas\Complex;

use Closure;
use Esliph\Schemas\Schema;
use Esliph\Results\ParseResult;
use Esliph\Errors\ValidatorError;
use Esliph\Validation\Rule;

final class ArraySchema extends Schema {

  protected bool $coerce = false;

  public function __construct(
    protected ?Schema $elementSchema = null
  ) {
  }

  public function __clone(): void {
    parent::__clone();

    if ($this->elementSchema !== null) {
      $this->elementSchema = clone $this->elementSchema;
    }
  }

  protected function parseType(mixed $value, array $path = []): ParseResult {
    if ($this->coerce && is_string($value)) {
      try {
        $decoded = json_decode($value, true, flags: JSON_THROW_ON_ERROR);

        if (is_array($decoded)) {
          return ParseResult::ok($decoded);
        }
      } catch (\Exception) {
      }
    }

    if (!is_array($value)) {
      return ParseResult::fail([new ValidatorError($path, 'Expected array, received ' . gettype($value), 'invalid_type')]);
    }

    if ($this->isAssociativeArray($value)) {
      return ParseResult::fail([new ValidatorError($path, 'Expected indexed array, received object', 'invalid_type')]);
    }

    return ParseResult::ok($value);
  }

  protected function validateType(mixed $value, array $path = []): ParseResult {
    $parsedValue = [];
    $errors = [];

    if ($this->elementSchema !== null) {
      foreach ($value as $index => $item) {
        $itemPath = array_merge($path, [$index]);
        $result = $this->elementSchema->_parse($item, $itemPath);

        if (!$result->success) {
          $errors = array_merge($errors, $result->errors);
        } else {
          $parsedValue[] = $result->data;
        }
      }
    } else {
      $parsedValue = $value;
    }

    if (!empty($errors)) {
      return ParseResult::fail($errors);
    }

    return ParseResult::ok($parsedValue);
  }

  public function of(Schema $schema): static {
    $clone = clone $this;
    $clone->elementSchema = $schema;

    return $clone;
  }

  public function length(int $length, string|Closure|null $message = null): static {
    return $this->addRule(new Rule(
      'length',
      'invalid_type',
      static fn(array $value, array $params): bool => count($value) === $params['length'],
      $message ?? static fn(array $value, array $params): string => "Array must have exactly {$params['length']} elements",
      ['length' => $length]
    ));
  }

  public function min(int $length, string|Closure|null $message = null): static {
    return $this->addRule(new Rule(
      'min',
      'too_small',
      static fn(array $value, array $params): bool => count($value) >= $params['length'],
      $message ?? static fn(array $value, array $params): string => "Array must have at least {$params['length']} elements",
      ['length' => $length]
    ));
  }

  public function max(int $length, string|Closure|null $message = null): static {
    return $this->addRule(new Rule(
      'max',
      'too_big',
      static fn(array $value, array $params): bool => count($value) <= $params['length'],
      $message ?? static fn(array $value, array $params): string => "Array must have at most {$params['length']} elements",
      ['length' => $length]
    ));
  }

  public function nonempty(string|Closure|null $message = null): static {
    return $this->addRule(new Rule(
      'nonempty',
      'too_small',
      static fn(array $value): bool => count($value) > 0,
      $message ?? 'Array must not be empty'
    ));
  }

  public function coerce(): static {
    $clone = clone $this;
    $clone->coerce = true;

    return $clone;
  }
}
