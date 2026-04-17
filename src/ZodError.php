<?php

namespace Zod;

class ZodError {

  public $path;
  public $message;
  public $code;

  /**
   * @param array $path
   * @param string $message
   * @param string $code
   */
  public function __construct($path, $message, $code) {
    $this->path = $path;
    $this->message = $message;
    $this->code = $code;
  }

  /**
   * @return string
   */
  public function pathString() {
    return implode('.', $this->path);
  }
}
