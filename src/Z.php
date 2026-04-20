<?php

namespace Zod;

use Zod\Types\NumberSchema;
use Zod\Types\ObjectSchema;
use Zod\Types\StringSchema;

class Z {

  public static function number() {
    return new NumberSchema();
  }

  public static function string() {
    return new StringSchema();
  }

  /**
   * @param array<string, Schema> $shape
   * @return ObjectSchema
   */
  public static function object($shape = []) {
    return new ObjectSchema($shape);
  }
}