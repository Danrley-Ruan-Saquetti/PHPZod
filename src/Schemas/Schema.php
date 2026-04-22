<?php

namespace Zod\Schemas;

use Zod\Results\ParseResult;
use Zod\Errors\ZodError;
use Zod\Errors\ZodException;
use Zod\Validation\Rule;

abstract class Schema {

  /** @var Rule[] */
  protected $rules = [];
  protected $transforms = [];
  protected $isOptional = false;

  public function __clone() {
    $this->rules = array_map(function ($rule) {
      return clone $rule;
    }, $this->rules);

    $this->transforms = array_values($this->transforms);
  }

  /**
   * @param mixed $value
   * @param array $path
   * @return ParseResult
   */
  protected function _parse($value, $path = []) {
    if (is_null($value)) {
      if ($this->isOptional) {
        return ParseResult::ok();
      }

      return ParseResult::fail([new ZodError($path, 'Value is required', 'required')]);
    }

    $typeResult = $this->parseType($value, $path);

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
   * @param mixed $value
   * @param array $path
   * @return ParseResult
   */
  abstract protected function parseType($value, $path = []);

  /**
   * @param mixed $value
   * @param string[] $path
   * @return ZodError[]
   */
  protected function validateRules($value, $path = []) {
    $errors = [];

    foreach ($this->rules as $rule) {
      if ($rule->validate($value) === false) {
        $errors[] = new ZodError($path, $rule->resolveMessage($value), $rule->code);
      }
    }

    return $errors;
  }

  /**
   * @param mixed $value
   * @return mixed
   */
  public function parse($value) {
    $result = $this->safeParse($value);

    if (!$result->success) {
      throw new ZodException($result->errors);
    }

    return $result->data;
  }

  /**
   * @param mixed $value
   * @return ParseResult
   */
  public function safeParse($value) {
    return $this->_parse($value, []);
  }

  /**
   * @param callable $callable
   * @param null|string|callable $message
   * @return static
   */
  public function refine($callable, $message = null) {
    return $this->addRule(new Rule(
      'refinement',
      'custom',
      $callable,
      $message ?: ''
    ));
  }

  /**
   * @param Rule $rule
   * @return static
   */
  protected function addRule($rule) {
    $clone = clone $this;
    $clone->rules[] = $rule;

    return $clone;
  }

  /**
   * @param callable $callable
   * @return static
   */
  public function transform($callable) {
    $clone = clone $this;
    $clone->transforms[] = $callable;

    return $clone;
  }

  /**
   * @param mixed $value
   * @return mixed
   */
  protected function applyTransforms($value) {
    foreach ($this->transforms as $fn) {
      $value = call_user_func($fn, $value);
    }

    return $value;
  }

  /**
   * @return static
   */
  public function optional() {
    $clone = clone $this;
    $clone->isOptional = true;

    return $clone;
  }
}
