<?php

namespace Zod;

use RuntimeException;

class ZodException extends RuntimeException {

  /** @var ZodError[] */
  private $errors = [];

  /**
   * @param ZodError[] $errors
   */
  public function __construct($errors) {
    parent::__construct($this->buildMessage(), 0, null);

    $this->errors = $errors;
  }

  /**
   * @return ZodError[]
   */
  public function getErrors() {
    return $this->errors;
  }

  /**
   * @return ZodError|null
   */
  public function getFirstError() {
    return isset($this->errors[0]) ? $this->errors[0] : null;
  }

  /**
   * @return array<string, string[]>
   */
  public function getMessagesByPath() {
    $grouped = $this->getErrorsByPath();
    $result = [];

    foreach ($grouped as $path => $errors) {
      $result[$path] = array_map(function (ZodError $e) {
        return $e->message;
      }, $errors);
    }

    return $result;
  }

  /**
   * @return array<string, string>
   */
  public function getFlatMessages() {
    $grouped = $this->getErrorsByPath();
    $result = [];

    foreach ($grouped as $path => $errors) {
      $result[$path] = $errors[0]->message;
    }

    return $result;
  }

  /**
   * @param string $path
   * @return bool
   */
  public function hasErrorAt($path) {
    $grouped = $this->getErrorsByPath();

    return isset($grouped[$path]);
  }

  /**
   * @param string $path
   * @return ZodError[]
   */
  public function getErrorsAt($path) {
    $grouped = $this->getErrorsByPath();

    return isset($grouped[$path]) ? $grouped[$path] : [];
  }

  /**
   * @return array{code: string, message: string, path: string[]}[]
   */
  public function toArray() {
    return array_map(function (ZodError $e) {
      return [
        'path' => $e->path,
        'message' => $e->message,
        'code' => $e->code,
      ];
    }, $this->errors);
  }

  /**
   * @return string
   */
  public function toJson() {
    return json_encode($this->toArray(), JSON_UNESCAPED_UNICODE);
  }

  /**
   * @return array<string, ZodError[]>
   */
  public function getErrorsByPath() {
    $grouped = [];

    foreach ($this->errors as $error) {
      $key = $error->pathString();

      if (!isset($grouped[$key])) {
        $grouped[$key] = [];
      }

      $grouped[$key][] = $error;
    }

    return $grouped;
  }

  /**
   * @return string
   */
  private function buildMessage() {
    $lines = array_map(function (ZodError $e) {
      $path = $e->pathString() ?: '(root)';

      return "[{$path}] {$e->message} (code: {$e->code})";
    }, $this->errors);

    return 'Validation failed: ' . implode(' | ', $lines);
  }
}
