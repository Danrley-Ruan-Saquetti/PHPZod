<?php

namespace Esliph\Validator\Schemas\Primitive;

use Esliph\Validator\Schemas\CoercibleSchema;
use Esliph\Validator\Results\ParseResult;
use Esliph\Validator\Errors\Issue;
use Esliph\Validator\Validation\Rule;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Closure;
use Exception;
use Override;

final class DateSchema extends CoercibleSchema {

  protected ?string $format = null;
  protected ?DateTimeZone $timezone = null;

  #[Override]
  protected function parseType(mixed $value, array $path = []): ParseResult {
    if ($value instanceof DateTimeInterface) {
      return ParseResult::ok(DateTimeImmutable::createFromInterface($value));
    }

    if (!$this->coerce) {
      return ParseResult::fail([new Issue($path, 'Expected DateTimeImmutable, received ' . gettype($value), 'invalid_type')]);
    }

    if (is_int($value)) {
      return ParseResult::ok((new DateTimeImmutable())->setTimestamp($value));
    }

    if (!is_string($value)) {
      return ParseResult::fail([new Issue($path, 'Expected DateTimeImmutable, received ' . gettype($value), 'invalid_type')]);
    }

    try {
      if ($this->format !== null) {
        $date = DateTimeImmutable::createFromFormat($this->format, $value, $this->timezone);

        if ($date === false) {
          return ParseResult::fail([new Issue($path, 'Invalid date string', 'invalid_date')]);
        }

        return ParseResult::ok($date);
      }

      return ParseResult::ok(new DateTimeImmutable($value, $this->timezone));
    } catch (Exception) {
      return ParseResult::fail([new Issue($path, 'Invalid date string', 'invalid_date')]);
    }
  }

  public function format(string $format): static {
    $clone = clone $this;
    $clone->format = $format;

    return $clone;
  }

  public function timezone(string|DateTimeZone $timezone): static {
    $clone = clone $this;
    $clone->timezone = is_string($timezone) ? new DateTimeZone($timezone) : $timezone;

    return $clone;
  }

  public function min(DateTimeInterface|string|int $min, string|Closure|null $message = null): static {
    return $this->after($min, $message);
  }

  public function max(DateTimeInterface|string|int $max, string|Closure|null $message = null): static {
    return $this->before($max, $message);
  }

  public function after(DateTimeInterface|string|int $min, string|Closure|null $message = null): static {
    $minDate = $this->normalizeDate($min);

    return $this->addRule(new Rule(
      'after',
      'too_early',
      static fn(mixed $value, array $params): bool => $value > $params['min'],
      $message ?? static fn(mixed $value, array $params): string => "Must be after {$params['min']->format(DateTimeInterface::ATOM)}",
      ['min' => $minDate]
    ));
  }

  public function before(DateTimeInterface|string|int $max, string|Closure|null $message = null): static {
    $maxDate = $this->normalizeDate($max);

    return $this->addRule(new Rule(
      'before',
      'too_late',
      static fn(mixed $value, array $params): bool => $value < $params['max'],
      $message ?? static fn(mixed $value, array $params): string => "Must be before {$params['max']->format(DateTimeInterface::ATOM)}",
      ['max' => $maxDate]
    ));
  }

  public function between(DateTimeInterface|string|int $min, DateTimeInterface|string|int $max, string|Closure|null $message = null): static {
    $minDate = $this->normalizeDate($min);
    $maxDate = $this->normalizeDate($max);

    return $this->addRule(new Rule(
      'between',
      'out_of_range',
      static fn(mixed $value, array $params): bool => $value >= $params['min'] && $value <= $params['max'],
      $message ?? static fn(mixed $value, array $params): string => "Must be between {$params['min']->format(DateTimeInterface::ATOM)} and {$params['max']->format(DateTimeInterface::ATOM)}",
      ['min' => $minDate, 'max' => $maxDate]
    ));
  }

  protected function normalizeDate(DateTimeInterface|string|int $value): DateTimeImmutable {
    if ($value instanceof DateTimeInterface) {
      return DateTimeImmutable::createFromInterface($value);
    }

    if (is_int($value)) {
      return (new DateTimeImmutable())->setTimestamp($value);
    }

    return new DateTimeImmutable($value);
  }
}
