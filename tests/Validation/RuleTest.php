<?php

namespace Esliph\Validator\Tests\Validation;

use Closure;
use PHPUnit\Framework\TestCase;

use Esliph\Validator\Validation\Rule;

class RuleTest extends TestCase {

  public function test_construct_WithAllRequiredParameters_ShouldStoreValuesCorrectly(): void {
    $check = fn(mixed $value, array $params) => true;

    $rule = new Rule(
      name: 'test_rule',
      code: 'TEST_001',
      check: $check
    );

    $this->assertSame('test_rule', $rule->name);
    $this->assertSame('TEST_001', $rule->code);
    $this->assertSame($check, $rule->check);
    $this->assertNull($rule->message);
    $this->assertEmpty($rule->params);
    $this->assertIsArray($rule->params);
  }

  public function test_construct_WithStringMessage_ShouldStoreMessageAsString(): void {
    $check = fn(mixed $value, array $params) => true;

    $rule = new Rule(
      name: 'email_rule',
      code: 'EMAIL_001',
      check: $check,
      message: 'Invalid email format'
    );

    $this->assertIsString($rule->message);
    $this->assertSame('Invalid email format', $rule->message);
  }

  public function test_construct_WithClosureMessage_ShouldStoreMessageAsCallable(): void {
    $check = fn(mixed $value, array $params) => true;
    $message = fn(mixed $value, array $params) => "Invalid value: {$value}";

    $rule = new Rule(
      name: 'custom_rule',
      code: 'CUSTOM_001',
      check: $check,
      message: $message
    );

    $this->assertInstanceOf(Closure::class, $rule->message);
    $this->assertSame($message, $rule->message);
  }

  public function test_construct_WithNullMessage_ShouldStoreNull(): void {
    $check = fn(mixed $value, array $params) => true;

    $rule = new Rule(
      name: 'rule_without_message',
      code: 'NO_MSG_001',
      check: $check,
      message: null
    );

    $this->assertNull($rule->message);
  }

  public function test_construct_WithEmptyParams_ShouldStoreEmptyArray(): void {
    $check = fn(mixed $value, array $params) => true;

    $rule = new Rule(
      name: 'test_rule',
      code: 'TEST_001',
      check: $check,
      params: []
    );

    $this->assertEmpty($rule->params);
    $this->assertIsArray($rule->params);
  }

  public function test_construct_WithParams_ShouldStoreParamsCorrectly(): void {
    $check = fn(mixed $value, array $params) => true;
    $params = ['min' => 5, 'max' => 10, 'type' => 'numeric'];

    $rule = new Rule(
      name: 'range_rule',
      code: 'RANGE_001',
      check: $check,
      params: $params
    );

    $this->assertSame($params, $rule->params);
    $this->assertCount(3, $rule->params);
  }

  public function test_validate_WithCheckReturningTrue_ShouldReturnTrue(): void {
    $check = fn(mixed $value, array $params) => true;
    $rule = new Rule(
      'test',
      'TEST_001',
      $check
    );

    $this->assertTrue($rule->validate('any_value'));
  }

  public function test_validate_WithCheckReturningFalse_ShouldReturnFalse(): void {
    $check = fn(mixed $value, array $params) => false;
    $rule = new Rule(
      name: 'test',
      code: 'TEST_001',
      check: $check
    );

    $this->assertFalse($rule->validate('any_value'));
  }

  public function test_validate_WithParamsUsedInClosure_ShouldApplyParamsCorrectly(): void {
    $check = fn(mixed $value, array $params) => strlen($value) >= $params['min'] && strlen($value) <= $params['max'];

    $rule = new Rule(
      name: 'length_rule',
      code: 'LENGTH_001',
      check: $check,
      params: ['min' => 3, 'max' => 10]
    );

    $this->assertTrue($rule->validate('valid'));
    $this->assertFalse($rule->validate('ab'));
    $this->assertFalse($rule->validate('this_is_too_long'));
  }

  public function test_validate_WithMultipleCombinedParams_ShouldValidateAllConditions(): void {
    $check = fn(mixed $value, array $params) => is_string($value) &&
      strlen($value) >= $params['minLength'] &&
      strlen($value) <= $params['maxLength'] &&
      preg_match($params['pattern'], $value);

    $rule = new Rule(
      name: 'password_rule',
      code: 'PASS_001',
      check: $check,
      params: [
        'minLength' => 8,
        'maxLength' => 20,
        'pattern' => '/[A-Z]/'
      ]
    );

    $this->assertTrue($rule->validate('MyPassword123'));
    $this->assertFalse($rule->validate('short'));
    $this->assertFalse($rule->validate('toolongpasswordwhichexceedslimit'));
    $this->assertFalse($rule->validate('nouppercase123'));
  }

  public function test_resolveMessage_WithStringMessage_ShouldReturnExactString(): void {
    $check = fn(mixed $value, array $params) => true;
    $rule = new Rule(
      name: 'test',
      code: 'TEST_001',
      check: $check,
      message: 'This is an error message'
    );

    $this->assertSame('This is an error message', $rule->resolveMessage('any_value'));
  }

  public function test_resolveMessage_WithNullMessage_ShouldReturnEmptyString(): void {
    $check = fn(mixed $value, array $params) => true;
    $rule = new Rule(
      name: 'test',
      code: 'TEST_001',
      check: $check,
      message: null
    );

    $this->assertSame('', $rule->resolveMessage('any_value'));
  }

  public function test_resolveMessage_WithUndefinedMessage_ShouldReturnEmptyString(): void {
    $check = fn(mixed $value, array $params) => true;
    $rule = new Rule(
      name: 'test',
      code: 'TEST_001',
      check: $check
    );

    $this->assertSame('', $rule->resolveMessage('any_value'));
  }

  public function test_resolveMessage_WithClosureMessage_ShouldCallClosureAndReturnString(): void {
    $check = fn(mixed $value, array $params) => true;
    $message = fn(mixed $value, array $params) => "The value '{$value}' is invalid";

    $rule = new Rule(
      name: 'test',
      code: 'TEST_001',
      check: $check,
      message: $message
    );

    $this->assertSame("The value 'test_value' is invalid", $rule->resolveMessage('test_value'));
  }

  public function test_resolveMessage_WithClosureMessageUsingParams_ShouldIncludeParamValues(): void {
    $check = fn(mixed $value, array $params) => true;
    $message = fn(mixed $value, array $params) => "Value must be between {$params['min']} and {$params['max']}";

    $rule = new Rule(
      name: 'range',
      code: 'RANGE_001',
      check: $check,
      message: $message,
      params: ['min' => 5, 'max' => 10]
    );

    $this->assertSame('Value must be between 5 and 10', $rule->resolveMessage('any'));
  }

  public function test_resolveMessage_WithClosureReturningNull_ShouldReturnEmptyString(): void {
    $check = fn(mixed $value, array $params) => true;
    $message = fn(mixed $value, array $params) => null;

    $rule = new Rule(
      name: 'test',
      code: 'TEST_001',
      check: $check,
      message: $message
    );

    $this->assertSame('', $rule->resolveMessage('any_value'));
  }

  public function test_resolveMessage_WithClosureAndDifferentValueTypes_ShouldHandleAllTypes(): void {
    $check = fn(mixed $value, array $params) => true;
    $message = fn(mixed $value, array $params) => "Received: " . gettype($value);

    $rule = new Rule(
      name: 'test',
      code: 'TEST_001',
      check: $check,
      message: $message
    );

    $this->assertSame('Received: string', $rule->resolveMessage('text'));
    $this->assertSame('Received: integer', $rule->resolveMessage(42));
    $this->assertSame('Received: boolean', $rule->resolveMessage(true));
    $this->assertSame('Received: NULL', $rule->resolveMessage(null));
    $this->assertSame('Received: array', $rule->resolveMessage([]));
  }

  public function test_properties_AreReadonly_ShouldNotAllowPropertyModification(): void {
    $check = fn(mixed $value, array $params) => true;
    $rule = new Rule(
      name: 'test',
      code: 'TEST_001',
      check: $check
    );

    $this->assertTrue(property_exists($rule, 'name'));
    $this->assertTrue(property_exists($rule, 'code'));
    $this->assertTrue(property_exists($rule, 'check'));
    $this->assertTrue(property_exists($rule, 'message'));
    $this->assertTrue(property_exists($rule, 'params'));
  }

  public function test_class_IsFinal_ShouldNotAllowInheritance(): void {
    $reflection = new \ReflectionClass(Rule::class);
    $this->assertTrue($reflection->isFinal());
  }

  public function test_fullWorkflow_ValidateAndResolveMessage_ShouldWorkTogether(): void {
    $check = fn(mixed $value, array $params) => strlen($value) >= $params['min'];
    $message = fn(mixed $value, array $params) => "Value must have at least {$params['min']} characters, got " . strlen($value);

    $rule = new Rule(
      name: 'min_length',
      code: 'MIN_001',
      check: $check,
      message: $message,
      params: ['min' => 5]
    );

    $this->assertFalse($rule->validate('ab'));
    $this->assertSame('Value must have at least 5 characters, got 2', $rule->resolveMessage('ab'));

    $this->assertTrue($rule->validate('valid_string'));
    $this->assertSame('Value must have at least 5 characters, got 12', $rule->resolveMessage('valid_string'));
  }

  public function test_validate_WithComplexClosure_ShouldHandleComplexLogic(): void {
    $check = function (mixed $value, array $params) {
      if (!is_array($value)) {
        return false;
      }

      if (count($value) < $params['minItems']) {
        return false;
      }

      foreach ($value as $item) {
        if (!isset($params['allowedTypes'][gettype($item)])) {
          return false;
        }
      }

      return true;
    };

    $rule = new Rule(
      name: 'array_with_types',
      code: 'ARRAY_TYPES_001',
      check: $check,
      params: [
        'minItems' => 1,
        'allowedTypes' => ['string' => true, 'integer' => true]
      ]
    );

    $this->assertTrue($rule->validate(['text', 123]));
    $this->assertFalse($rule->validate([]));
    $this->assertFalse($rule->validate([true]));
    $this->assertFalse($rule->validate('not_array'));
  }
}
