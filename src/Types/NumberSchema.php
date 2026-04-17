<?php

namespace Zod\Types;

use Zod\ParseResult;
use Zod\Rule;
use Zod\Schema;
use Zod\ZodError;

class NumberSchema extends Schema {

  protected $coerce = false;
  protected $integer = false;

  /**
   * @inheritDoc
   */
  protected function parseType($value, $path = []) {
    if ($this->coerce && is_numeric($value)) {
      $value = $this->integer ? (int) $value : (float) $value;
    }

    if ($this->integer) {
      if (!is_int($value)) {
        return ParseResult::fail([new ZodError($path, 'Expected integer, received ' . gettype($value), 'invalid_type')]);
      }
    } else if (!is_int($value) && !is_float($value)) {
      return ParseResult::fail([new ZodError($path, 'Expected number, received ' . gettype($value), 'invalid_type')]);
    }

    return ParseResult::ok($value);
  }

  /**
   * @return static
   */
  public function coerce() {
    $clone = clone $this;
    $clone->coerce = true;

    return $clone;
  }

  /**
   * @return static
   */
  public function int() {
    $clone = clone $this;
    $clone->integer = true;

    return $clone;
  }

  /**
   * @param float|int $min
   * @param null|string|callable $message
   * @return static
   */
  public function min($min, $message = null) {
    return $this->gte($min, $message);
  }

  /**
   * @param float|int $max
   * @param null|string|callable $message
   * @return static
   */
  public function max($max, $message = null) {
    return $this->lte($max, $message);
  }

  /**
   * @param float|int $min
   * @param null|string|callable $message
   * @return static
   */
  public function gt($min, $message = null) {
    return $this->addRule(new Rule(
      'gt',
      'too_small',
      function ($value, $params) {
        return $value > $params['min'];
      },
      $message ?: function ($value, $params) {
        return "Must be greater than {$params['min']}";
      },
      ['min' => $min]
    ));
  }

  /**
   * @param float|int $min
   * @param null|string|callable $message
   * @return static
   */
  public function gte($min, $message = null) {
    return $this->addRule(new Rule(
      'gte',
      'too_small',
      function ($value, $params) {
        return $value >= $params['min'];
      },
      $message ?: function ($value, $params) {
        return "Must be greater than or equal to {$params['min']}";
      },
      ['min' => $min]
    ));
  }

  /**
   * @param float|int $max
   * @param null|string|callable $message
   * @return static
   */
  public function lt($max, $message = null) {
    return $this->addRule(new Rule(
      'lt',
      'too_big',
      function ($value, $params) {
        return $value < $params['max'];
      },
      $message ?: function ($value, $params) {
        return "Must be less than {$params['max']}";
      },
      ['max' => $max]
    ));
  }

  /**
   * @param float|int $max
   * @param null|string|callable $message
   * @return static
   */
  public function lte($max, $message = null) {
    return $this->addRule(new Rule(
      'lte',
      'too_big',
      function ($value, $params) {
        return $value <= $params['max'];
      },
      $message ?: function ($value, $params) {
        return "Must be less than or equal to {$params['max']}";
      },
      ['max' => $max]
    ));
  }

  /**
   * @param null|string|callable $message
   * @return static
   */
  public function nonnegative($message = null) {
    return $this->gt(0, $message ?: 'Must be a positive number');
  }

  /**
   * @param null|string|callable $message
   * @return static
   */
  public function nonpositive($message = null) {
    return $this->lt(0, $message ?: 'Must be a negative number');
  }

  /**
   * @param null|string|callable $message
   * @return static
   */
  public function positive($message = null) {
    return $this->gte(0, $message ?: 'Must be a non-negative number');
  }

  /**
   * @param null|string|callable $message
   * @return static
   */
  public function negative($message = null) {
    return $this->lte(0, $message ?: 'Must be a non-positive number');
  }

  /**
   * @param float|int $min
   * @param float|int $max
   * @param null|string|callable $message
   * @return static
   */
  public function between($min, $max, $message = null) {
    return $this->addRule(new Rule(
      'between',
      'out_of_range',
      function ($value, $params) {
        return $value >= $params['min'] && $value <= $params['max'];
      },
      $message ?: function ($value, $params) {
        return "Must be between {$params['min']} and {$params['max']}";
      },
      ['min' => $min, 'max' => $max]
    ));
  }

  /**
   * @param float|int $divisor
   * @param null|string|callable $message
   * @return static
   */
  public function multipleOf($divisor, $message = null) {
    return $this->addRule(new Rule(
      'multipleOf',
      'not_multiple',
      function ($value, $params) {
        return fmod($value, $params['divisor']) === 0.0;
      },
      $message ?: function ($value, $params) {
        return "Must be a multiple of {$params['divisor']}";
      },
      ['divisor' => $divisor]
    ));
  }
}
