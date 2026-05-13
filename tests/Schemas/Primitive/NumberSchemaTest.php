<?php

namespace Esliph\Validator\Tests\Schemas\Primitive;

use Override;

use Esliph\Validator\Schemas\Primitive\NumberSchema;

class NumberSchemaTest extends NumberSchemaTestCase {

  #[Override]
  protected function createNumberSchema(): NumberSchema {
    return new NumberSchema();
  }

  #[Override]
  protected function getValidValue(): mixed {
    return 42;
  }

  #[Override]
  protected function getInvalidValue(): mixed {
    return 'not a number';
  }

  public function test_parseType_WithValidFloat_ShouldAccept(): void {
    $result = (new NumberSchema())->safeParse(3.14);

    $this->assertTrue($result->success);
    $this->assertSame(3.14, $result->data);
    $this->assertIsFloat($result->data);
  }

  public function test_parseType_WithValidStringNumeric_ShouldAccept(): void {
    $result = (new NumberSchema())->coerce()->safeParse('3.14');

    $this->assertTrue($result->success);
    $this->assertSame(3.14, $result->data);
    $this->assertIsFloat($result->data);
  }

  public function test_parseType_WithValidInteger_ShouldAccept(): void {
    $result = (new NumberSchema())->safeParse(42);

    $this->assertTrue($result->success);
    $this->assertEquals(42, $result->data);
    $this->assertIsInt($result->data);
  }

  public function test_parseType_WithZero_ShouldAccept(): void {
    $result = (new NumberSchema())->safeParse(0);

    $this->assertTrue($result->success);
    $this->assertEquals(0, $result->data);
  }

  public function test_parseType_WithNegativeNumber_ShouldAccept(): void {
    $result = (new NumberSchema())->safeParse(-42.5);

    $this->assertTrue($result->success);
    $this->assertSame(-42.5, $result->data);
  }

  public function test_parseType_WithLargeNumber_ShouldAccept(): void {
    $result = (new NumberSchema())->safeParse(PHP_INT_MAX);

    $this->assertTrue($result->success);
    $this->assertSame(PHP_INT_MAX, $result->data);
  }

  public function test_parseType_WithInfinity_ShouldAccept(): void {
    $result = (new NumberSchema())->safeParse(INF);

    $this->assertTrue($result->success);
    $this->assertTrue(is_infinite($result->data));
  }

  public function test_parseType_WithNaN_ShouldAccept(): void {
    $result = (new NumberSchema())->safeParse(NAN);

    $this->assertTrue($result->success);
    $this->assertTrue(is_nan($result->data));
  }

  public function test_parseType_WithString_ShouldReject(): void {
    $result = (new NumberSchema())->safeParse('not a number');

    $this->assertFalse($result->success);
    $this->assertSame('invalid_type', $result->issues[0]->code);
  }

  public function test_parseType_WithArray_ShouldReject(): void {
    $result = (new NumberSchema())->safeParse([42]);

    $this->assertFalse($result->success);
    $this->assertSame('invalid_type', $result->issues[0]->code);
  }

  public function test_parseType_WithObject_ShouldReject(): void {
    $result = (new NumberSchema())->safeParse(new \stdClass());

    $this->assertFalse($result->success);
    $this->assertSame('invalid_type', $result->issues[0]->code);
  }

  public function test_multipleOf_WithDivisorAndValueAsDecimal_ShouldPass(): void {
    $schema = $this->createNumberSchema()->multipleOf(0.5);

    $result = $schema->safeParse(3.5);

    $this->assertTrue($result->success);
  }
}
