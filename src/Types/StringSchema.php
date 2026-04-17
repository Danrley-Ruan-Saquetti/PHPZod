<?php

namespace Zod\Types;

use Zod\ParseResult;
use Zod\Rule;
use Zod\Schema;
use Zod\ZodError;

class StringSchema extends Schema {

  protected function parseType($value, $path = []) {
    if (!is_string($value)) {
      return ParseResult::fail([new ZodError($path, 'Expected string, receive ' . gettype($value), 'invalid_type')]);
    }

    return ParseResult::ok($value);
  }

  public function min($length, $message = null) {
    return $this->addRule(new Rule(
      'min',
      'too_small',
      function ($value, $params) {
        return mb_strlen($value) >= $params['length'];
      },
      $message ?: function ($value, $params) {
        return "Must be at least {$params['length']} characters";
      },
      ['length' => $length]
    ));
  }

  public function max($length, $message = null) {
    return $this->addRule(new Rule(
      'max',
      'too_big',
      function ($value, $params) {
        return mb_strlen($value) <= $params['length'];
      },
      $message ?: function ($value, $params) {
        return "Must be at most {$params['length']} characters";
      },
      ['length' => $length]
    ));
  }

  public function email($message = null) {
    return $this->addRule(new Rule(
      'email',
      'invalid_format',
      function ($value) {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
      },
      $message ?: 'Invalid email address'
    ));
  }

  public function url($message = null) {
    return $this->addRule(new Rule(
      'url',
      'invalid_format',
      function ($value) {
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
      },
      $message ?: 'Invalid URL'
    ));
  }

  public function regex($pattern, $message = null) {
    return $this->addRule(new Rule(
      'regex',
      'invalid_format',
      function ($value, $params) {
        return preg_match($params['pattern'], $value) === 1;
      },
      $message ?: "Invalid format",
      ['pattern' => $pattern]
    ));
  }

  public function nonempty($message = null) {
    return $this->addRule(new Rule(
      'nonempty',
      'too_small',
      function ($value) {
        return mb_strlen($value) > 0;
      },
      $message ?: 'String must not be empty'
    ));
  }

  public function startsWith($prefix, $message = null) {
    return $this->addRule(new Rule(
      'startsWith',
      'invalid_format',
      function ($value, $params) {
        return strpos($value, $params['prefix']) === 0;
      },
      $message ?: function ($value, $params) {
        return "Must start with '{$params['prefix']}'";
      },
      ['prefix' => $prefix]
    ));
  }

  public function trim() {
    return $this->transform(function ($value) {
      return trim($value);
    });
  }

  public function toLowerCase() {
    return $this->transform(function ($value) {
      return mb_strtolower($value);
    });
  }

  public function toUpperCase() {
    return $this->transform(function ($value) {
      return mb_strtoupper($value);
    });
  }
}
