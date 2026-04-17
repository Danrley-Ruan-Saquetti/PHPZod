<?php

namespace Zod;

class Rule {

  public $name;
  public $code;
  public $check;
  public $message;
  public $params;

  /**
   * @param string $name
   * @param string $code
   * @param callable $check
   * @param null|string|callable $message
   * @param array $params
   */
  public function __construct($name, $code, $check, $message = null, $params = []) {
    $this->name = $name;
    $this->code = $code;
    $this->check = $check;
    $this->message = $message;
    $this->params = $params;
  }

  /**
   * @param mixed $value
   * @return bool
   */
  public function validate($value) {
    return (bool) call_user_func($this->check, $value, $this->params);
  }

  /**
   * @param mixed $value
   * @return string
   */
  public function resolveMessage($value) {
    if (is_callable($this->message)) {
      return call_user_func($this->message, $value, $this->params) ?: '';
    }

    return $this->message ?: '';
  }
}
