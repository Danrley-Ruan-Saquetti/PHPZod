<?php

namespace Zod;

use Zod\Schemas\Primitive\StringSchema;
use Zod\Schemas\Primitive\NumberSchema;
use Zod\Schemas\Complex\ObjectSchema;
use Zod\Schemas\Complex\ArraySchema;
use Zod\Schemas\Schema;

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

  /**
   * @param Schema $schema
   * @return ArraySchema
   */
  public static function _array($schema = null) {
    return new ArraySchema($schema);
  }
}