<?php

namespace Zod\Results;

use Zod\Errors\ZodError;

class ParseResult {

  public $success;
  public $data;
  public $errors;

  /**
   * @param bool $success
   * @param mixed $data
   * @param ZodError[] $errors
   */
  private function __construct($success, $data = null, $errors = []) {
    $this->success = $success;
    $this->data = $data;
    $this->errors = $errors;
  }

  /**
   * @param mixed $data
   * @return self
   */
  public static function ok($data = null) {
    return new self(true, $data);
  }

  /**
   * @param ZodError[] $errors
   * @return self
   */
  public static function fail($errors) {
    return new self(false, null, $errors);
  }
}
