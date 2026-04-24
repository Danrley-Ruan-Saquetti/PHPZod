<?php

declare(strict_types=1);

namespace Zod;

use Zod\Schemas\Primitive\StringSchema;
use Zod\Schemas\Primitive\NumberSchema;
use Zod\Schemas\Complex\ObjectSchema;
use Zod\Schemas\Complex\ArraySchema;
use Zod\Schemas\Primitive\BooleanSchema;
use Zod\Schemas\Schema;

final class Z {

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
