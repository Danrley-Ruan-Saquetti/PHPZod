<?php

namespace Esliph\Validator\Tests\Schemas\Primitive;

use Closure;
use Override;

use PHPUnit\Framework\Attributes\DataProvider;

use Esliph\Validator\Schemas\Schema;
use Esliph\Validator\Schemas\Primitive\NumberSchema;

use Esliph\Validator\Tests\Schemas\BaseSchemaTestCase;

class NumberSchemaTest extends BaseSchemaTestCase {

  #[Override]
  protected function createSchema(): Schema {
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

  #[Override]
  protected function getDefaultValue(): mixed {
    return 100;
  }

  #[Override]
  protected function getTransformFunction(): Closure {
    return fn(mixed $value) => $value;
  }

  #[Override]
  protected function getRefineCheck(): Closure {
    return fn(mixed $value, array $path): bool => $value > 0;
  }

  public function test_parseType_WithValidFloat_ShouldAccept(): void {
    $schema = new NumberSchema();

    $result = $schema->safeParse(3.14);

    $this->assertTrue($result->success);
    $this->assertSame(3.14, $result->data);
    $this->assertIsFloat($result->data);
  }

  public function test_parseType_WithValidInteger_ShouldAccept(): void {
    $schema = new NumberSchema();

    $result = $schema->safeParse(42);

    $this->assertTrue($result->success);
    $this->assertSame(42, $result->data);
    $this->assertIsInt($result->data);
  }

  public function test_parseType_WithIntModifier_ShouldRejectFloat(): void {
    $schema = (new NumberSchema())->int();

    $result = $schema->safeParse(3.14);

    $this->assertFalse($result->success);
    $this->assertSame('invalid_type', $result->issues[0]->code);
  }

  public function test_parseType_WithIntModifier_ShouldAcceptInteger(): void {
    $schema = (new NumberSchema())->int();

    $result = $schema->safeParse(42);

    $this->assertTrue($result->success);
    $this->assertSame(42, $result->data);
    $this->assertIsInt($result->data);
  }

  public function test_parseType_WithCoerceAndNumericString_ShouldCoerceToFloat(): void {
    $schema = (new NumberSchema())->coerce();

    $result = $schema->safeParse('3.14');

    $this->assertTrue($result->success);
    $this->assertSame(3.14, $result->data);
    $this->assertIsFloat($result->data);
  }

  public function test_parseType_WithCoerceAndIntModifier_ShouldCoerceToInt(): void {
    $schema = (new NumberSchema())
      ->coerce()
      ->int();

    $result = $schema->safeParse('42');

    $this->assertTrue($result->success);
    $this->assertSame(42, $result->data);
    $this->assertIsInt($result->data);
  }

  public function test_parseType_WithCoerceAndBooleanTrue_ShouldCoerceToOne(): void {
    $schema = (new NumberSchema())->coerce();

    $result = $schema->safeParse(true);

    $this->assertTrue($result->success);
    $this->assertSame(1, $result->data);
  }

  public function test_parseType_WithCoerceAndBooleanFalse_ShouldCoerceToZero(): void {
    $schema = (new NumberSchema())->coerce();

    $result = $schema->safeParse(false);

    $this->assertTrue($result->success);
    $this->assertSame(0, $result->data);
  }

  public function test_parseType_WithCoerceAndNonNumericString_ShouldFail(): void {
    $schema = (new NumberSchema())->coerce();

    $result = $schema->safeParse('abc');

    $this->assertFalse($result->success);
    $this->assertSame('invalid_type', $result->issues[0]->code);
  }

  public function test_parseType_WithoutCoerceAndNumericString_ShouldFail(): void {
    $schema = new NumberSchema();

    $result = $schema->safeParse('123');

    $this->assertFalse($result->success);
    $this->assertSame('invalid_type', $result->issues[0]->code);
  }

  public function test_parseType_WithArray_ShouldFail(): void {
    $schema = new NumberSchema();

    $result = $schema->safeParse([42]);

    $this->assertFalse($result->success);
    $this->assertSame('invalid_type', $result->issues[0]->code);
  }

  public function test_parseType_WithObject_ShouldFail(): void {
    $schema = new NumberSchema();

    $result = $schema->safeParse(new \stdClass());

    $this->assertFalse($result->success);
    $this->assertSame('invalid_type', $result->issues[0]->code);
  }

  public function test_parseType_WithZero_ShouldAccept(): void {
    $schema = new NumberSchema();

    $result = $schema->safeParse(0);

    $this->assertTrue($result->success);
    $this->assertSame(0, $result->data);
  }

  public function test_parseType_WithNegativeNumber_ShouldAccept(): void {
    $schema = new NumberSchema();

    $result = $schema->safeParse(-42.5);

    $this->assertTrue($result->success);
    $this->assertSame(-42.5, $result->data);
  }

  public function test_parseType_WithLargeNumber_ShouldAccept(): void {
    $schema = new NumberSchema();

    $result = $schema->safeParse(PHP_INT_MAX);

    $this->assertTrue($result->success);
    $this->assertSame(PHP_INT_MAX, $result->data);
  }

  public function test_parseType_WithInfinity_ShouldAccept(): void {
    $schema = new NumberSchema();

    $result = $schema->safeParse(INF);

    $this->assertTrue($result->success);
    $this->assertTrue(is_infinite($result->data));
  }

  public function test_parseType_WithNaN_ShouldAccept(): void {
    $schema = new NumberSchema();

    $result = $schema->safeParse(NAN);

    $this->assertTrue($result->success);
    $this->assertTrue(is_nan($result->data));
  }

  public function test_gt_WithValueGreaterThanMin_ShouldPass(): void {
    $schema = (new NumberSchema())->gt(10);

    $result = $schema->safeParse(11);

    $this->assertTrue($result->success);
    $this->assertSame(11, $result->data);
  }

  public function test_gt_WithValueEqualToMin_ShouldFail(): void {
    $schema = (new NumberSchema())->gt(10);

    $result = $schema->safeParse(10);

    $this->assertFalse($result->success);
    $this->assertSame('too_small', $result->issues[0]->code);
  }

  public function test_gt_WithValueLessThanMin_ShouldFail(): void {
    $schema = (new NumberSchema())->gt(10);

    $result = $schema->safeParse(9);

    $this->assertFalse($result->success);
    $this->assertSame('too_small', $result->issues[0]->code);
  }

  public function test_gt_WithCustomMessage_ShouldUseCustomMessage(): void {
    $customMessage = 'Value must be greater than 10';
    $schema = (new NumberSchema())->gt(10, $customMessage);

    $result = $schema->safeParse(5);

    $this->assertFalse($result->success);
    $this->assertSame($customMessage, $result->issues[0]->message);
  }

  public function test_gt_WithClosureMessage_ShouldUseDynamicMessage(): void {
    $schema = (new NumberSchema())->gt(10, function (mixed $value, array $params): string {
      return "Value $value must be greater than {$params['min']}";
    });

    $result = $schema->safeParse(5);

    $this->assertFalse($result->success);
    $this->assertStringContainsString('Value 5', $result->issues[0]->message);
  }

  public function test_gt_WithNegativeThreshold_ShouldWork(): void {
    $schema = (new NumberSchema())->gt(-10);

    $result = $schema->safeParse(-5);

    $this->assertTrue($result->success);
  }

  public function test_gt_WithFloatThreshold_ShouldWork(): void {
    $schema = (new NumberSchema())->gt(3.5);

    $result = $schema->safeParse(3.6);

    $this->assertTrue($result->success);
  }

  public function test_gte_WithValueGreaterThanMin_ShouldPass(): void {
    $schema = (new NumberSchema())->gte(10);

    $result = $schema->safeParse(11);

    $this->assertTrue($result->success);
  }

  public function test_gte_WithValueEqualToMin_ShouldPass(): void {
    $schema = (new NumberSchema())->gte(10);

    $result = $schema->safeParse(10);

    $this->assertTrue($result->success);
    $this->assertSame(10, $result->data);
  }

  public function test_gte_WithValueLessThanMin_ShouldFail(): void {
    $schema = (new NumberSchema())->gte(10);

    $result = $schema->safeParse(9);

    $this->assertFalse($result->success);
    $this->assertSame('too_small', $result->issues[0]->code);
  }

  public function test_gte_WithCustomMessage_ShouldUseCustomMessage(): void {
    $customMessage = 'Value must be >= 10';
    $schema = (new NumberSchema())->gte(10, $customMessage);

    $result = $schema->safeParse(5);

    $this->assertFalse($result->success);
    $this->assertSame($customMessage, $result->issues[0]->message);
  }

  public function test_min_ShouldWorkLikeGte(): void {
    $schema = (new NumberSchema())->min(10);

    $result = $schema->safeParse(10);

    $this->assertTrue($result->success);
  }

  public function test_lt_WithValueLessThanMax_ShouldPass(): void {
    $schema = (new NumberSchema())->lt(10);

    $result = $schema->safeParse(9);

    $this->assertTrue($result->success);
  }

  public function test_lt_WithValueEqualToMax_ShouldFail(): void {
    $schema = (new NumberSchema())->lt(10);

    $result = $schema->safeParse(10);

    $this->assertFalse($result->success);
    $this->assertSame('too_big', $result->issues[0]->code);
  }

  public function test_lt_WithValueGreaterThanMax_ShouldFail(): void {
    $schema = (new NumberSchema())->lt(10);

    $result = $schema->safeParse(11);

    $this->assertFalse($result->success);
    $this->assertSame('too_big', $result->issues[0]->code);
  }

  public function test_lt_WithCustomMessage_ShouldUseCustomMessage(): void {
    $customMessage = 'Value must be less than 10';
    $schema = (new NumberSchema())->lt(10, $customMessage);

    $result = $schema->safeParse(15);

    $this->assertFalse($result->success);
    $this->assertSame($customMessage, $result->issues[0]->message);
  }

  public function test_lte_WithValueLessThanMax_ShouldPass(): void {
    $schema = (new NumberSchema())->lte(10);

    $result = $schema->safeParse(9);

    $this->assertTrue($result->success);
  }

  public function test_lte_WithValueEqualToMax_ShouldPass(): void {
    $schema = (new NumberSchema())->lte(10);

    $result = $schema->safeParse(10);

    $this->assertTrue($result->success);
    $this->assertSame(10, $result->data);
  }

  public function test_lte_WithValueGreaterThanMax_ShouldFail(): void {
    $schema = (new NumberSchema())->lte(10);

    $result = $schema->safeParse(11);

    $this->assertFalse($result->success);
    $this->assertSame('too_big', $result->issues[0]->code);
  }

  public function test_max_ShouldWorkLikeLte(): void {
    $schema = (new NumberSchema())->max(10);

    $result = $schema->safeParse(10);

    $this->assertTrue($result->success);
  }

  public function test_between_WithValueWithinRange_ShouldPass(): void {
    $schema = (new NumberSchema())->between(10, 20);

    $result = $schema->safeParse(15);

    $this->assertTrue($result->success);
  }

  public function test_between_WithValueAtMinBoundary_ShouldPass(): void {
    $schema = (new NumberSchema())->between(10, 20);

    $result = $schema->safeParse(10);

    $this->assertTrue($result->success);
  }

  public function test_between_WithValueAtMaxBoundary_ShouldPass(): void {
    $schema = (new NumberSchema())->between(10, 20);

    $result = $schema->safeParse(20);

    $this->assertTrue($result->success);
  }

  public function test_between_WithValueBelowMin_ShouldFail(): void {
    $schema = (new NumberSchema())->between(10, 20);

    $result = $schema->safeParse(5);

    $this->assertFalse($result->success);
    $this->assertSame('out_of_range', $result->issues[0]->code);
  }

  public function test_between_WithValueAboveMax_ShouldFail(): void {
    $schema = (new NumberSchema())->between(10, 20);

    $result = $schema->safeParse(25);

    $this->assertFalse($result->success);
    $this->assertSame('out_of_range', $result->issues[0]->code);
  }

  public function test_between_WithCustomMessage_ShouldUseCustomMessage(): void {
    $customMessage = 'Value must be between 10 and 20';
    $schema = (new NumberSchema())->between(10, 20, $customMessage);

    $result = $schema->safeParse(5);

    $this->assertFalse($result->success);
    $this->assertSame($customMessage, $result->issues[0]->message);
  }

  public function test_between_WithNegativeRange_ShouldWork(): void {
    $schema = (new NumberSchema())->between(-20, -10);

    $result = $schema->safeParse(-15);

    $this->assertTrue($result->success);
  }

  public function test_between_WithFloatBoundaries_ShouldWork(): void {
    $schema = (new NumberSchema())->between(3.5, 7.5);

    $result = $schema->safeParse(5.5);

    $this->assertTrue($result->success);
  }

  public function test_multipleOf_WithMultipleValue_ShouldPass(): void {
    $schema = (new NumberSchema())->multipleOf(5);

    $result = $schema->safeParse(15);

    $this->assertTrue($result->success);
  }

  public function test_multipleOf_WithZero_ShouldPass(): void {
    $schema = (new NumberSchema())->multipleOf(5);

    $result = $schema->safeParse(0);

    $this->assertTrue($result->success);
  }

  public function test_multipleOf_WithNonMultipleValue_ShouldFail(): void {
    $schema = (new NumberSchema())->multipleOf(5);

    $result = $schema->safeParse(16);

    $this->assertFalse($result->success);
    $this->assertSame('not_multiple', $result->issues[0]->code);
  }

  public function test_multipleOf_WithCustomMessage_ShouldUseCustomMessage(): void {
    $customMessage = 'Value must be multiple of 5';
    $schema = (new NumberSchema())->multipleOf(5, message: $customMessage);

    $result = $schema->safeParse(16);

    $this->assertFalse($result->success);
    $this->assertSame($customMessage, $result->issues[0]->message);
  }

  public function test_multipleOf_WithNegativeValue_ShouldWork(): void {
    $schema = (new NumberSchema())->multipleOf(5);

    $result = $schema->safeParse(-15);

    $this->assertTrue($result->success);
  }

  public function test_multipleOf_WithFloatDivisor_ShouldWork(): void {
    $schema = (new NumberSchema())->multipleOf(0.5);

    $result = $schema->safeParse(2.5);

    $this->assertTrue($result->success);
  }

  public function test_positive_WithPositiveValue_ShouldPass(): void {
    $schema = (new NumberSchema())->positive();

    $result = $schema->safeParse(10);

    $this->assertTrue($result->success);
  }

  public function test_positive_WithZero_ShouldPass(): void {
    $schema = (new NumberSchema())->positive();

    $result = $schema->safeParse(0);

    $this->assertTrue($result->success);
  }

  public function test_positive_WithNegativeValue_ShouldFail(): void {
    $schema = (new NumberSchema())->positive();

    $result = $schema->safeParse(-5);

    $this->assertFalse($result->success);
  }

  public function test_positive_WithCustomMessage_ShouldUseCustomMessage(): void {
    $customMessage = 'Value must be positive';
    $schema = (new NumberSchema())->positive($customMessage);

    $result = $schema->safeParse(-5);

    $this->assertFalse($result->success);
    $this->assertSame($customMessage, $result->issues[0]->message);
  }

  public function test_negative_WithNegativeValue_ShouldPass(): void {
    $schema = (new NumberSchema())->negative();

    $result = $schema->safeParse(-10);

    $this->assertTrue($result->success);
  }

  public function test_negative_WithZero_ShouldPass(): void {
    $schema = (new NumberSchema())->negative();

    $result = $schema->safeParse(0);

    $this->assertTrue($result->success);
  }

  public function test_negative_WithPositiveValue_ShouldFail(): void {
    $schema = (new NumberSchema())->negative();

    $result = $schema->safeParse(5);

    $this->assertFalse($result->success);
  }

  public function test_nonnegative_WithPositiveValue_ShouldPass(): void {
    $schema = (new NumberSchema())->nonnegative();

    $result = $schema->safeParse(10);

    $this->assertTrue($result->success);
  }

  public function test_nonnegative_WithZero_ShouldFail(): void {
    $schema = (new NumberSchema())->nonnegative();

    $result = $schema->safeParse(0);

    $this->assertFalse($result->success);
  }

  public function test_nonnegative_WithNegativeValue_ShouldFail(): void {
    $schema = (new NumberSchema())->nonnegative();

    $result = $schema->safeParse(-5);

    $this->assertFalse($result->success);
  }

  public function test_nonpositive_WithNegativeValue_ShouldPass(): void {
    $schema = (new NumberSchema())->nonpositive();

    $result = $schema->safeParse(-10);

    $this->assertTrue($result->success);
  }

  public function test_nonpositive_WithZero_ShouldFail(): void {
    $schema = (new NumberSchema())->nonpositive();

    $result = $schema->safeParse(0);

    $this->assertFalse($result->success);
  }

  public function test_nonpositive_WithPositiveValue_ShouldFail(): void {
    $schema = (new NumberSchema())->nonpositive();

    $result = $schema->safeParse(5);

    $this->assertFalse($result->success);
  }

  public function test_combined_GtAndLt_ShouldEnforceExclusiveRange(): void {
    $schema = (new NumberSchema())
      ->gt(10)
      ->lt(20);

    $this->assertFalse($schema->safeParse(10)->success);
    $this->assertFalse($schema->safeParse(20)->success);

    $this->assertTrue($schema->safeParse(15)->success);
    $this->assertTrue($schema->safeParse(10.001)->success);
    $this->assertTrue($schema->safeParse(19.999)->success);
  }

  public function test_combined_GteAndLte_ShouldEnforceInclusiveRange(): void {
    $schema = (new NumberSchema())
      ->gte(10)
      ->lte(20);

    $this->assertTrue($schema->safeParse(10)->success);
    $this->assertTrue($schema->safeParse(15)->success);
    $this->assertTrue($schema->safeParse(20)->success);
    $this->assertFalse($schema->safeParse(9)->success);
    $this->assertFalse($schema->safeParse(21)->success);
  }

  public function test_combined_BetweenAndMultipleOf_ShouldEnforceBoth(): void {
    $schema = (new NumberSchema())
      ->between(0, 50)
      ->multipleOf(10);

    $this->assertTrue($schema->safeParse(0)->success);
    $this->assertTrue($schema->safeParse(10)->success);
    $this->assertTrue($schema->safeParse(30)->success);
    $this->assertTrue($schema->safeParse(50)->success);

    $this->assertFalse($schema->safeParse(5)->success);
    $this->assertFalse($schema->safeParse(55)->success);
  }

  public function test_combined_PositiveAndMultipleOf_ShouldEnforceBoth(): void {
    $schema = (new NumberSchema())
      ->positive()
      ->multipleOf(3);

    $this->assertTrue($schema->safeParse(3)->success);
    $this->assertTrue($schema->safeParse(9)->success);
    $this->assertTrue($schema->safeParse(300)->success);
    $this->assertTrue($schema->safeParse(0)->success);

    $this->assertFalse($schema->safeParse(4)->success);
    $this->assertFalse($schema->safeParse(-3)->success);
  }

  public function test_combined_VeryTightRange_ShouldOnlyAcceptSpecificValues(): void {
    $schema = (new NumberSchema())->between(5.1, 5.9);

    $this->assertTrue($schema->safeParse(5.5)->success);
    $this->assertFalse($schema->safeParse(5.0)->success);
    $this->assertFalse($schema->safeParse(6.0)->success);
  }

  public function test_combined_IntPositiveMultiple_ExtremeSituation(): void {
    $schema = (new NumberSchema())
      ->int()
      ->positive()
      ->multipleOf(100);

    $this->assertTrue($schema->safeParse(100)->success);
    $this->assertTrue($schema->safeParse(1000)->success);
    $this->assertTrue($schema->safeParse(0)->success);

    $this->assertFalse($schema->safeParse(50)->success);
    $this->assertFalse($schema->safeParse(100.5)->success);
  }

  public function test_combined_RulesWithRefine_ShouldEnforceAll(): void {
    $schema = (new NumberSchema())
      ->between(1, 100)
      ->multipleOf(5)
      ->refine(
        fn(mixed $value, array $path): bool => $value !== 50,
        'Value cannot be exactly 50'
      );

    $this->assertTrue($schema->safeParse(5)->success);
    $this->assertTrue($schema->safeParse(45)->success);
    $this->assertFalse($schema->safeParse(50)->success);
    $this->assertFalse($schema->safeParse(3)->success);
  }

  public function test_cloneWithRules_ShouldCreateIndependentCopy(): void {
    $schema1 = (new NumberSchema())->gt(10);
    $schema2 = clone $schema1;
    $schema2 = $schema2->lt(20);

    $value = 15;

    $this->assertTrue($schema1->safeParse($value)->success);
    $this->assertTrue($schema2->safeParse($value)->success);

    $this->assertTrue($schema1->safeParse(25)->success);
    $this->assertFalse($schema2->safeParse(25)->success);
  }

  public function test_chainedOperations_ShouldReturnValidSchema(): void {
    $schema = (new NumberSchema())
      ->coerce()
      ->int()
      ->gt(0)
      ->lt(100)
      ->positive()
      ->multipleOf(2);

    $result = $schema->safeParse('42');

    $this->assertTrue($result->success);
    $this->assertSame(42, $result->data);
    $this->assertIsInt($result->data);
  }

  #[DataProvider('provideAllInputTypes')]
  public function test_parseType_WithAllInputTypes_ShouldHandleCorrectly(mixed $value, string $typeName): void {
    $schema = new NumberSchema();

    $result = $schema->safeParse($value);

    $shouldBeValid = is_int($value) || is_float($value);

    if ($shouldBeValid) {
      $this->assertTrue($result->success, "Type $typeName should be valid");
    } else {
      $this->assertFalse($result->success, "Type $typeName should be invalid");

      if ($value === null) {
        $this->assertSame('required', $result->issues[0]->code);
      } else {
        $this->assertSame('invalid_type', $result->issues[0]->code);
      }
    }
  }

  #[DataProvider('provideAllInputTypes')]
  public function test_parseType_WithCoercionAndAllInputTypes_ShouldHandleCorrectly(mixed $value, string $typeName): void {
    $schema = (new NumberSchema())->coerce();

    $result = $schema->safeParse($value);

    $shouldCoerce = is_int($value) || is_float($value) || is_bool($value) ||
      (is_string($value) && is_numeric($value));

    if ($shouldCoerce) {
      $this->assertTrue($result->success, "Type $typeName should coerce successfully");
      $this->assertTrue(is_int($result->data) || is_float($result->data));
    } else {
      $this->assertFalse($result->success, "Type $typeName should not coerce");
    }
  }

  public function test_boundary_WithPHPIntMax_ShouldAccept(): void {
    $schema = new NumberSchema();

    $result = $schema->safeParse(PHP_INT_MAX);

    $this->assertTrue($result->success);
    $this->assertSame(PHP_INT_MAX, $result->data);
  }

  public function test_boundary_WithPHPIntMin_ShouldAccept(): void {
    $schema = new NumberSchema();

    $result = $schema->safeParse(PHP_INT_MIN);

    $this->assertTrue($result->success);
    $this->assertSame(PHP_INT_MIN, $result->data);
  }

  public function test_boundary_WithVerySmallPositiveFloat_ShouldAccept(): void {
    $schema = new NumberSchema();

    $result = $schema->safeParse(1e-300);

    $this->assertTrue($result->success);
    $this->assertIsFloat($result->data);
  }

  public function test_boundary_WithVeryLargeFloat_ShouldAccept(): void {
    $schema = new NumberSchema();

    $result = $schema->safeParse(1e300);

    $this->assertTrue($result->success);
    $this->assertIsFloat($result->data);
  }

  public function test_boundary_WithNegativeZero_ShouldAccept(): void {
    $schema = new NumberSchema();

    $result = $schema->safeParse(-0.0);

    $this->assertTrue($result->success);
  }

  public function test_intModifier_WithPHPIntMax_ShouldAccept(): void {
    $schema = (new NumberSchema())->int();

    $result = $schema->safeParse(PHP_INT_MAX);

    $this->assertTrue($result->success);
    $this->assertSame(PHP_INT_MAX, $result->data);
  }

  public function test_refineComplex_WithMultipleConditions_ShouldEnforceAll(): void {
    $schema = (new NumberSchema())->refine(
      fn(mixed $v, array $p): bool => $v > 0 && $v < 100 && fmod($v, 2) === 0.0,
      'Must be positive, less than 100, and even'
    );

    $this->assertTrue($schema->safeParse(2)->success);
    $this->assertTrue($schema->safeParse(50)->success);
    $this->assertFalse($schema->safeParse(1)->success);
    $this->assertFalse($schema->safeParse(101)->success);
    $this->assertFalse($schema->safeParse(-2)->success);
  }

  public function test_optional_WithGtRule_ShouldAllowNull(): void {
    $schema = (new NumberSchema())
      ->gt(10)
      ->optional();

    $result = $schema->safeParse(null);

    $this->assertTrue($result->success);
    $this->assertNull($result->data);
  }

  public function test_default_WithGtRule_ShouldApplyDefault(): void {
    $schema = (new NumberSchema())
      ->gt(0)
      ->_default(100);

    $result = $schema->safeParse(null);

    $this->assertTrue($result->success);
    $this->assertSame(100, $result->data);
  }

  public function test_validateThenTransform_ShouldTransformValueValidated(): void {
    $schema = (new NumberSchema())
      ->transform(fn(mixed $v) => $v * 2)
      ->gt(10);

    $result = $schema->safeParse(6);

    $this->assertFalse($result->success);

    $result2 = $schema->safeParse(12);

    $this->assertTrue($result2->success);
    $this->assertSame(24, $result2->data);
  }
}
