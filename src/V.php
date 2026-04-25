<?php

namespace Esliph;

use Esliph\Schemas\Primitive\StringSchema;
use Esliph\Schemas\Primitive\NumberSchema;
use Esliph\Schemas\Complex\ObjectSchema;
use Esliph\Schemas\Complex\ArraySchema;
use Esliph\Schemas\Primitive\BooleanSchema;
use Esliph\Schemas\Schema;

final class V {

  public static function number(): NumberSchema {
    return new NumberSchema();
  }

  public static function string(): StringSchema {
    return new StringSchema();
  }

  public static function boolean(): BooleanSchema {
    return new BooleanSchema();
  }

  /**
   * @param array<string, Schema> $shape
   */
  public static function object(array $shape = []): ObjectSchema {
    return new ObjectSchema($shape);
  }

  /**
   * @param Schema|null $schema
   */
  public static function _array(?Schema $schema = null): ArraySchema {
    return new ArraySchema($schema);
  }
}
