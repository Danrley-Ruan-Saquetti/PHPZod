<?php

namespace Esliph\Validator\Errors;

use RuntimeException;

final class ValidatorException extends RuntimeException {

  /**
   * @param ValidatorError[] $errors
   */
  public function __construct(
    private array $errors = []
  ) {
    parent::__construct($this->buildMessage(), 0, null);
  }

  /**
   * @return ValidatorError[]
   */
  public function getErrors(): array {
    return $this->errors;
  }

  public function getFirstError(): ?ValidatorError {
    return $this->errors[0] ?? null;
  }

  /**
   * @return array<string, string[]>
   */
  public function getMessagesByPath(): array {
    $grouped = $this->getErrorsByPath();
    $result = [];

    foreach ($grouped as $path => $errors) {
      $result[$path] = array_map(
        static fn(ValidatorError $e): string => $e->message,
        $errors
      );
    }

    return $result;
  }

  /**
   * @return array<string, string>
   */
  public function getFlatMessages(): array {
    $grouped = $this->getErrorsByPath();
    $result = [];

    foreach ($grouped as $path => $errors) {
      $result[$path] = $errors[0]->message;
    }

    return $result;
  }

  public function hasErrorAt(string $path): bool {
    $grouped = $this->getErrorsByPath();

    return isset($grouped[$path]);
  }

  /**
   * @return ValidatorError[]
   */
  public function getErrorsAt(string $path): array {
    $grouped = $this->getErrorsByPath();

    return $grouped[$path] ?? [];
  }

  /**
   * @return array<int, array{code: string, message: string, path: string[]}>
   */
  public function toArray(): array {
    return array_map(
      static fn(ValidatorError $e): array => [
        'path' => $e->path,
        'message' => $e->message,
        'code' => $e->code,
      ],
      $this->errors
    );
  }

  public function toJson(): string {
    return json_encode($this->toArray(), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
  }

  /**
   * @return array<string, ValidatorError[]>
   */
  public function getErrorsByPath(): array {
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

  private function buildMessage(): string {
    $lines = array_map(
      static fn(ValidatorError $e): string => '[' . ($e->pathString() ?: '(root)') . "] {$e->message} (code: {$e->code})",
      $this->errors
    );

    return 'Validation failed: ' . implode(' | ', $lines);
  }
}
