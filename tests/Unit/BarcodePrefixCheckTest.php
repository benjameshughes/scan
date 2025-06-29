<?php

use App\Rules\BarcodePrefixCheck;
use Illuminate\Support\Facades\Validator;

describe('BarcodePrefixCheck Validation Rule', function () {

    test('it passes validation when barcode starts with default prefix', function () {
        $rule = new BarcodePrefixCheck;
        $validator = Validator::make(
            ['barcode' => '5059031234567'],
            ['barcode' => $rule]
        );

        expect($validator->passes())->toBeTrue();
    });

    test('it fails validation when barcode does not start with default prefix', function () {
        $rule = new BarcodePrefixCheck;
        $validator = Validator::make(
            ['barcode' => '1234567890123'],
            ['barcode' => $rule]
        );

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->first('barcode'))->toBe('The barcode must start with 505903.');
    });

    test('it passes validation when barcode starts with custom prefix', function () {
        $rule = new BarcodePrefixCheck('123456');
        $validator = Validator::make(
            ['barcode' => '1234567890123'],
            ['barcode' => $rule]
        );

        expect($validator->passes())->toBeTrue();
    });

    test('it fails validation when barcode does not start with custom prefix', function () {
        $rule = new BarcodePrefixCheck('123456');
        $validator = Validator::make(
            ['barcode' => '7890123456789'],
            ['barcode' => $rule]
        );

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->first('barcode'))->toBe('The barcode must start with 123456.');
    });

    test('it handles integer barcodes correctly', function () {
        $rule = new BarcodePrefixCheck;
        $validator = Validator::make(
            ['barcode' => 5059031234567],
            ['barcode' => $rule]
        );

        expect($validator->passes())->toBeTrue();
    });

    test('it handles string barcodes correctly', function () {
        $rule = new BarcodePrefixCheck;
        $validator = Validator::make(
            ['barcode' => '5059031234567'],
            ['barcode' => $rule]
        );

        expect($validator->passes())->toBeTrue();
    });

    test('it fails for integer barcode without correct prefix', function () {
        $rule = new BarcodePrefixCheck;
        $validator = Validator::make(
            ['barcode' => 1234567890123],
            ['barcode' => $rule]
        );

        expect($validator->fails())->toBeTrue();
    });

    test('it handles empty string correctly', function () {
        $rule = new BarcodePrefixCheck;
        $validator = Validator::make(
            ['barcode' => ''],
            ['barcode' => $rule]
        );

        // Empty string should pass (rule skips validation for empty values)
        expect($validator->fails())->toBeFalse();
    });

    test('it handles null values correctly', function () {
        $rule = new BarcodePrefixCheck;
        $validator = Validator::make(
            ['barcode' => null],
            ['barcode' => $rule]
        );

        // Null values should pass (rule skips validation for null values)
        expect($validator->fails())->toBeFalse();
    });

    test('it handles zero correctly', function () {
        $rule = new BarcodePrefixCheck;
        $validator = Validator::make(
            ['barcode' => 0],
            ['barcode' => $rule]
        );

        // Zero should pass (rule skips validation for zero)
        expect($validator->fails())->toBeFalse();
    });

    test('it handles boolean values correctly', function () {
        $rule = new BarcodePrefixCheck;
        $validator = Validator::make(
            ['barcode' => true],
            ['barcode' => $rule]
        );

        expect($validator->fails())->toBeTrue();
    });

    test('it works with exact prefix match', function () {
        $rule = new BarcodePrefixCheck('505903');
        $validator = Validator::make(
            ['barcode' => '505903'],
            ['barcode' => $rule]
        );

        expect($validator->passes())->toBeTrue();
    });

    test('it handles very long barcodes correctly', function () {
        $rule = new BarcodePrefixCheck;
        $longBarcode = '505903'.str_repeat('1', 100);
        $validator = Validator::make(
            ['barcode' => $longBarcode],
            ['barcode' => $rule]
        );

        expect($validator->passes())->toBeTrue();
    });

    test('it handles unicode characters correctly', function () {
        $rule = new BarcodePrefixCheck('αβγ');
        $validator = Validator::make(
            ['barcode' => 'αβγδεζ'],
            ['barcode' => $rule]
        );

        expect($validator->passes())->toBeTrue();
    });

    test('it is case sensitive', function () {
        $rule = new BarcodePrefixCheck('ABC');
        $validator = Validator::make(
            ['barcode' => 'abc123'],
            ['barcode' => $rule]
        );

        expect($validator->fails())->toBeTrue();
    });

    test('it validates different attribute names correctly', function () {
        $rule = new BarcodePrefixCheck;
        $validator = Validator::make(
            ['product_code' => '1234567890123'],
            ['product_code' => $rule]
        );

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->first('product_code'))->toBe('The product_code must start with 505903.');
    });

    test('it constructs with default prefix when no argument provided', function () {
        $rule = new BarcodePrefixCheck;

        // Test through validation behavior
        $validator = Validator::make(
            ['barcode' => '5059031234567'],
            ['barcode' => $rule]
        );

        expect($validator->passes())->toBeTrue();

        $validator2 = Validator::make(
            ['barcode' => '1234567890123'],
            ['barcode' => $rule]
        );

        expect($validator2->fails())->toBeTrue();
        expect($validator2->errors()->first('barcode'))->toBe('The barcode must start with 505903.');
    });

    test('it works with array input data', function () {
        $rule = new BarcodePrefixCheck;
        $validator = Validator::make(
            ['barcodes' => ['5059031234567', '5059039876543']],
            ['barcodes.*' => $rule]
        );

        expect($validator->passes())->toBeTrue();
    });

    test('it fails with array input containing invalid barcode', function () {
        $rule = new BarcodePrefixCheck;
        $validator = Validator::make(
            ['barcodes' => ['5059031234567', '1234567890123']],
            ['barcodes.*' => $rule]
        );

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->first('barcodes.1'))->toBe('The barcodes.1 must start with 505903.');
    });
});
