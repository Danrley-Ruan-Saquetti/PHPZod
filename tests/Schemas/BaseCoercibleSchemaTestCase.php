<?php

namespace Esliph\Validator\Tests\Schemas;

use Esliph\Validator\Schemas\CoercibleSchema;
use Esliph\Validator\Schemas\Schema;

use Override;

abstract class BaseCoercibleSchemaTestCase extends BaseSchemaTestCase {

  #[Override]
  protected function createSchema(): Schema {
    return $this->createSchemaCoercible();
  }

  abstract protected function createSchemaCoercible(): CoercibleSchema;
}
