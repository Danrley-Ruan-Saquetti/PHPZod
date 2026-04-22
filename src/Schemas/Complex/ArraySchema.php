<?php

namespace Zod\Schemas\Complex;

use Zod\Schemas\Schema;
use Zod\Results\ParseResult;
use Zod\Errors\ZodError;
use Zod\Validation\Rule;

class ArraySchema extends Schema {

  /** @var Schema */
  protected $elementSchema = null;
  protected $coerce = false;

  /**
   * @param ?Schema $elementSchema
   */
  public function __construct($elementSchema = null) {
    $this->elementSchema = $elementSchema;
  }

  public function __clone() {
    parent::__clone();

    if ($this->elementSchema !== null) {
      $this->elementSchema = clone $this->elementSchema;
    }
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
    if ($this->coerce && is_string($value)) {
      try {
        $decoded = json_decode($value, true);

        if (is_array($decoded)) {
          return ParseResult::ok($decoded);
        }
      } catch (\Exception $e) {
      }
    }

    if (!is_array($value)) {
      return ParseResult::fail([new ZodError($path, 'Expected array, received ' . gettype($value), 'invalid_type')]);
    }

    if ($this->isAssociativeArray($value)) {
      return ParseResult::fail([new ZodError($path, 'Expected indexed array, received object', 'invalid_type')]);
    }

    return ParseResult::ok($value);
  }

  /**
   * @param Schema $schema
   * @return static
   */
  public function of($schema) {
    $clone = clone $this;
    $clone->elementSchema = $schema;

    return $clone;
  }

  /**
   * @param int $length
   * @param string|null $message
   * @return static
   */
  public function length($length, $message = null) {
    return $this->addRule(new Rule(
      'length',
      'invalid_type',
      function ($value, $params) {
        return count($value) === $params['length'];
      },
      $message ?: function ($value, $params) {
        return "Array must have exactly {$params['length']} elements";
      },
      ['length' => $length]
    ));
  }

  /**
   * @param int $length
   * @param string|null $message
   * @return static
   */
  public function min($length, $message = null) {
    return $this->addRule(new Rule(
      'min',
      'too_small',
      function ($value, $params) {
        return count($value) >= $params['length'];
      },
      $message ?: function ($value, $params) {
        return "Array must have at least {$params['length']} elements";
      },
      ['length' => $length]
    ));
  }

  /**
   * @param int $length
   * @param string|null $message
   * @return static
   */
  public function max($length, $message = null) {
    return $this->addRule(new Rule(
      'max',
      'too_big',
      function ($value, $params) {
        return count($value) <= $params['length'];
      },
      $message ?: function ($value, $params) {
        return "Array must have at most {$params['length']} elements";
      },
      ['length' => $length]
    ));
  }

  /**
   * @param string|null $message
   * @return static
   */
  public function nonempty($message = null) {
    return $this->addRule(new Rule(
      'nonempty',
      'too_small',
      function ($value) {
        return count($value) > 0;
      },
      $message ?: 'Array must not be empty'
    ));
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
   * @param mixed $arr
   * @return bool
   */
  private function isAssociativeArray($arr) {
    if (!is_array($arr)) {
      return false;
    }

    if (empty($arr)) {
      return false;
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
