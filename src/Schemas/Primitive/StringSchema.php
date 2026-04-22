<?php

namespace Zod\Schemas\Primitive;

use Zod\Schemas\Schema;
use Zod\Results\ParseResult;
use Zod\Errors\ZodError;
use Zod\Validation\Rule;

class StringSchema extends Schema {

  /**
   * @inheritDoc
   */
  protected function parseType($value, $path = []) {
    if (!is_string($value)) {
      return ParseResult::fail([new ZodError($path, 'Expected string, receive ' . gettype($value), 'invalid_type')]);
    }

    return ParseResult::ok($value);
  }

  /**
   * @param int $length
   * @param null|string|callable $message
   * @return static
   */
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

  /**
   * @param int $length
   * @param null|string|callable $message
   * @return static
   */
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

  /**
   * @param null|string|callable $message
   * @return static
   */
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

  /**
   * @param null|string|callable $message
   * @return static
   */
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

  /**
   * @param string $pattern
   * @param null|string|callable $message
   * @return static
   */
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

  /**
   * @param null|string|callable $message
   * @return static
   */
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

  /**
   * @param string $prefix
   * @param null|string|callable $message
   * @return static
   */
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

  /**
   * @param string $suffix
   * @param null|string|callable $message
   * @return static
   */
  public function endsWith($suffix, $message = null) {
    return $this->addRule(new Rule(
      'endsWith',
      'invalid_format',
      function ($value, $params) {
        $suffixLen = mb_strlen($params['suffix']);
        return mb_substr($value, -$suffixLen) === $params['suffix'];
      },
      $message ?: function ($value, $params) {
        return "Must end with '{$params['suffix']}'";
      },
      ['suffix' => $suffix]
    ));
  }

  /**
   * @param string $substring
   * @param null|string|callable $message
   * @return static
   */
  public function includes($substring, $message = null) {
    return $this->addRule(new Rule(
      'includes',
      'invalid_format',
      function ($value, $params) {
        return mb_strpos($value, $params['substring']) !== false;
      },
      $message ?: function ($value, $params) {
        return "Must include '{$params['substring']}'";
      },
      ['substring' => $substring]
    ));
  }

  /**
   * @param int $length
   * @param null|string|callable $message
   * @return static
   */
  public function length($length, $message = null) {
    return $this->addRule(new Rule(
      'length',
      'invalid_length',
      function ($value, $params) {
        return mb_strlen($value) === $params['length'];
      },
      $message ?: function ($value, $params) {
        return "Must be exactly {$params['length']} characters";
      },
      ['length' => $length]
    ));
  }

  /**
   * @param null|string|callable $message
   * @return static
   */
  public function lowercase($message = null) {
    return $this->addRule(new Rule(
      'lowercase',
      'invalid_case',
      function ($value) {
        return mb_strtolower($value) === $value;
      },
      $message ?: 'Must be all lowercase'
    ));
  }

  /**
   * @param null|string|callable $message
   * @return static
   */
  public function uppercase($message = null) {
    return $this->addRule(new Rule(
      'uppercase',
      'invalid_case',
      function ($value) {
        return mb_strtoupper($value) === $value;
      },
      $message ?: 'Must be all uppercase'
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
