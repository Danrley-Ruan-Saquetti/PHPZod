<?php

namespace Esliph\Validator\Schemas;

abstract class CoercibleSchema extends Schema {

  protected bool $coerce = false;

  public function coerce(): static {
    $clone = clone $this;
    $clone->coerce = true;

    return $clone;
  }
}
