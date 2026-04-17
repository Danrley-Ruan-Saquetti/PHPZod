<?php

namespace Zod;

use Zod\Types\NumberSchema;
use Zod\Types\StringSchema;

class Z {

  public static function number() {
    return new NumberSchema();
  }

  public static function string() {
    return new StringSchema();
  }
}