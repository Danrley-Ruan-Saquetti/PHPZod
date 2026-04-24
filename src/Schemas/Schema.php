<?php

namespace Zod\Schemas;

use Zod\Results\ParseResult;
use Zod\Errors\ZodError;
use Zod\Errors\ZodException;
use Zod\Validation\Rule;
use Closure;

abstract class Schema {

  /** @var Rule[] */
  protected array $rules = [];
  /** @var Closure[] */
  protected array $transforms = [];
  protected bool $isOptional = false;
  protected mixed $default = null;
  protected bool $hasDefault = false;

  public function __clone(): void {
    $this->rules = array_map(
      static fn(Rule $rule): Rule => clone $rule,
      $this->rules
    );

    $this->transforms = array_values($this->transforms);

    if ($this->hasDefault && is_object($this->default)) {
      $this->default = clone $this->default;
    }
  }

  /**
   * @param string[] $path
   */
  protected function _parse(mixed $value, array $path = []): ParseResult {
    if (is_null($value)) {
      if ($this->hasDefault) {
        $value = $this->default instanceof Closure ? ($this->default)() : $this->default;
      } elseif ($this->isOptional) {
        return ParseResult::ok();
      } else {
        return ParseResult::fail([new ZodError($path, 'Value is required', 'required')]);
      }
    }

    $typeResult = $this->parseType($value, $path);

    if (!$typeResult->success) {
      return $typeResult;
    }

    $value = $typeResult->data;

    $typeResult = $this->validateType($value, $path);

    if (!$typeResult->success) {
      return $typeResult;
    }

    $value = $typeResult->data;

    $errors = $this->validateRules($value, $path);

    if (!empty($errors)) {
      return ParseResult::fail($errors);
    }

    $value = $this->applyTransforms($value);

    return ParseResult::ok($value);
  }

  /**
   * @param string[] $path
   */
  abstract protected function parseType(mixed $value, array $path = []): ParseResult;

  /**
   * @param string[] $path
   */
  protected function validateType(mixed $value, array $path = []): ParseResult {
    return ParseResult::ok($value);
  }

  /**
   * @param string[] $path
   * @return ZodError[]
   */
  protected function validateRules(mixed $value, array $path = []): array {
    $errors = [];

    foreach ($this->rules as $rule) {
      if ($rule->validate($value) === false) {
        $errors[] = new ZodError($path, $rule->resolveMessage($value), $rule->code);
      }
    }

    return $errors;
  }

  public function parse(mixed $value): mixed {
    $result = $this->safeParse($value);

    if (!$result->success) {
      throw new ZodException($result->errors);
    }

    return $result->data;
  }

  public function safeParse(mixed $value): ParseResult {
    return $this->_parse($value, []);
  }

  public function apply(Closure $callable): static {
    return $callable($this);
  }

  public function refine(Closure $callable, string|Closure|null $message = null): static {
    return $this->addRule(new Rule(
      'refinement',
      'custom',
      $callable,
      $message ?? ''
    ));
  }

  protected function addRule(Rule $rule): static {
    $clone = clone $this;
    $clone->rules[] = $rule;

    return $clone;
  }

  public function transform(Closure $callable): static {
    $clone = clone $this;
    $clone->transforms[] = $callable;

    return $clone;
  }

  public function _default(mixed $value): static {
    $clone = clone $this;
    $clone->default = $value;
    $clone->hasDefault = true;

    return $clone;
  }

  protected function applyTransforms(mixed $value): mixed {
    foreach ($this->transforms as $fn) {
      $value = $fn($value);
    }

    return $value;
  }

  public function optional(): static {
    $clone = clone $this;
    $clone->isOptional = true;

    return $clone;
  }

  protected function isAssociativeArray(array $array): bool {
    if (empty($array)) {
      return false;
    }

    return array_keys($array) !== range(0, count($array) - 1);
  }
}
