<?php

namespace Zod\Types;

use Zod\ParseResult;
use Zod\Schema;
use Zod\ZodError;

class ObjectSchema extends Schema {

  /** @var array<string, Schema> */
  protected $shape = [];
  protected $coerce = false;
  protected $strict = false;
  protected $catchall = null;

  /**
   * @param array<string, Schema> $shape
   */
  public function __construct($shape = []) {
    $this->shape = $shape;
  }

  /**
   * @inheritDoc
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

    $parsedValue = [];
    $errors = [];

    foreach ($this->shape as $key => $schema) {
      $fieldPath = array_merge($path, [$key]);
      $fieldValue = $value->$key ?: null;

      $result = $schema->_parse($fieldValue, $fieldPath);

      if (!$result->success) {
        $errors = array_merge($errors, $result->errors);
      } else {
        $parsedValue[$key] = $result->data;
      }
    }

    if ($this->strict) {
      foreach ($value as $key => $val) {
        if (!isset($this->shape[$key])) {
          $fieldPath = array_merge($path, [$key]);
          $errors[] = new ZodError($fieldPath, 'Unrecognized key in object', 'unrecognized_keys');
        }
      }
    } else if ($this->catchall !== null) {
      foreach ($value as $key => $val) {
        if (!isset($this->shape[$key])) {
          $fieldPath = array_merge($path, [$key]);
          $result = $this->catchall->_parse($val, $fieldPath);

          if (!$result->success) {
            $errors = array_merge($errors, $result->errors);
          } else {
            $parsedValue[$key] = $result->data;
          }
        }
      }
    } else {
      foreach ($value as $key => $val) {
        if (!isset($this->shape[$key])) {
          $parsedValue[$key] = $val;
        }
      }
    }

    if (!empty($errors)) {
      return ParseResult::fail($errors);
    }

    $ruleErrors = $this->validateRules($parsedValue, $path);

    if (!empty($ruleErrors)) {
      return ParseResult::fail($ruleErrors);
    }

    $parsedValue = $this->applyTransforms($parsedValue);

    return ParseResult::ok($parsedValue);
  }

  /**
   * @inheritDoc
   */
  protected function parseType($value, $path = []) {
    if ($this->coerce && is_array($value)) {
      if (!$this->isAssociativeArray($value)) {
        return ParseResult::fail([new ZodError($path, 'Expected object or associative array, received indexed array', 'invalid_type')]);
      }

      var_dump($value);

      return ParseResult::ok((object) $value);
    }

    if (!is_array($value)) {
      return ParseResult::fail([new ZodError($path, 'Expected object, received ' . gettype($value), 'invalid_type')]);
    }

    if (!$this->isAssociativeArray($value)) {
      return ParseResult::fail([new ZodError($path, 'Expected object (associative array), received indexed array', 'invalid_type')]);
    }

    return ParseResult::ok($value);
  }

  /**
   * @param array<string, Schema> $shape
   * @return static
   */
  public function shape($shape) {
    $clone = clone $this;
    $clone->shape = $shape;

    return $clone;
  }

  /**
   * @param string[] $keys
   * @return static
   */
  public function pick($keys) {
    $clone = clone $this;
    $newShape = [];

    foreach ($keys as $key) {
      if (isset($this->shape[$key])) {
        $newShape[$key] = $this->shape[$key];
      }
    }

    $clone->shape = $newShape;

    return $clone;
  }

  /**
   * @param string[] $keys
   * @return static
   */
  public function omit($keys) {
    $clone = clone $this;
    $keysToOmit = array_flip($keys);
    $newShape = [];

    foreach ($this->shape as $key => $schema) {
      if (!isset($keysToOmit[$key])) {
        $newShape[$key] = $schema;
      }
    }

    $clone->shape = $newShape;

    return $clone;
  }

  /**
   * @param array<string, Schema> $shape
   * @return static
   */
  public function extend($shape) {
    $clone = clone $this;
    $clone->shape = array_merge($this->shape, $shape);

    return $clone;
  }

  /**
   * @param ObjectSchema $other
   * @return static
   */
  public function merge($other) {
    return $this->extend($other->shape);
  }

  /**
   * @return static
   */
  public function partial() {
    $clone = clone $this;
    $newShape = [];

    foreach ($this->shape as $key => $schema) {
      $newShape[$key] = $schema->optional();
    }

    $clone->shape = $newShape;

    return $clone;
  }

  /**
   * @return static
   */
  public function required() {
    $clone = clone $this;
    $newShape = [];

    foreach ($this->shape as $key => $schema) {
      $newSchema = clone $schema;
      $newSchema->isOptional = false;
      $newShape[$key] = $newSchema;
    }

    $clone->shape = $newShape;

    return $clone;
  }

  /**
   * @return static
   */
  public function strict() {
    $clone = clone $this;
    $clone->strict = true;
    $clone->catchall = null;

    return $clone;
  }

  /**
   * @return static
   */
  public function passthrough() {
    $clone = clone $this;
    $clone->strict = false;
    $clone->catchall = null;

    return $clone;
  }

  /**
   * @param Schema $schema
   * @return static
   */
  public function catchall($schema) {
    $clone = clone $this;
    $clone->catchall = $schema;

    return $clone;
  }

  /**
   * @return static
   */
  public function coerce() {
    $clone = clone $this;
    $clone->coerce = true;

    return $clone;
  }

  public function asArray() {
    return $this->transform(function($value) {
      return (array) $value;
    });
  }

  /**
   * @param mixed $arr
   * @return bool
   */
  private function isAssociativeArray($arr) {
    if (!is_array($arr)) {
      return false;
    }

    if (empty($arr)) {
      return true;
    }

    $keys = array_keys($arr);
    $numKeys = count($keys);

    for ($i = 0; $i < $numKeys; $i++) {
      if ($keys[$i] !== $i) {
        return true;
      }
    }

    return false;
  }
}
