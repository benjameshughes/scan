<?php

use App\Rules\BarcodeOrSkuCheck;
use Illuminate\Support\Facades\Validator;

describe('BarcodeOrSkuCheck Validation Rule', function () {

    test('it passes for valid barcode with default prefix', function () {
        $validator = Validator::make(
            ['barcode' => '5059031234567'],
            ['barcode' => new BarcodeOrSkuCheck]
        );

        expect($validator->passes())->toBeTrue();
    });

    test('it fails for barcode with wrong prefix', function () {
        $validator = Validator::make(
            ['barcode' => '1234567890123'],
            ['barcode' => new BarcodeOrSkuCheck]
        );

        expect($validator->fails())->toBeTrue();
    });

    test('it fails for barcode with wrong length', function () {
        $validator = Validator::make(
            ['barcode' => '50590312345'],
            ['barcode' => new BarcodeOrSkuCheck]
        );

        expect($validator->fails())->toBeTrue();
    });

    test('it passes for valid SKU format', function () {
        $validator = Validator::make(
            ['barcode' => '123-456'],
            ['barcode' => new BarcodeOrSkuCheck]
        );

        expect($validator->passes())->toBeTrue();
    });

    test('it fails for invalid SKU format', function () {
        $validator = Validator::make(
            ['barcode' => '12-456'],
            ['barcode' => new BarcodeOrSkuCheck]
        );

        expect($validator->fails())->toBeTrue();
    });

    test('it passes for short alpha code like NDF', function () {
        $validator = Validator::make(
            ['barcode' => 'NDF'],
            ['barcode' => new BarcodeOrSkuCheck]
        );

        expect($validator->passes())->toBeTrue();
    });

    test('it passes for lowercase alpha code', function () {
        $validator = Validator::make(
            ['barcode' => 'ndf'],
            ['barcode' => new BarcodeOrSkuCheck]
        );

        expect($validator->passes())->toBeTrue();
    });

    test('it passes for mixed case alpha code', function () {
        $validator = Validator::make(
            ['barcode' => 'Ndf'],
            ['barcode' => new BarcodeOrSkuCheck]
        );

        expect($validator->passes())->toBeTrue();
    });

    test('it passes for two character alpha code', function () {
        $validator = Validator::make(
            ['barcode' => 'AB'],
            ['barcode' => new BarcodeOrSkuCheck]
        );

        expect($validator->passes())->toBeTrue();
    });

    test('it fails for single character alpha code', function () {
        $validator = Validator::make(
            ['barcode' => 'A'],
            ['barcode' => new BarcodeOrSkuCheck]
        );

        expect($validator->fails())->toBeTrue();
    });

    test('it fails for alpha code exceeding 10 characters', function () {
        $validator = Validator::make(
            ['barcode' => 'ABCDEFGHIJK'],
            ['barcode' => new BarcodeOrSkuCheck]
        );

        expect($validator->fails())->toBeTrue();
    });

    test('it fails for alpha code with numbers mixed in', function () {
        $validator = Validator::make(
            ['barcode' => 'NDF1'],
            ['barcode' => new BarcodeOrSkuCheck]
        );

        expect($validator->fails())->toBeTrue();
    });

    test('it fails for alpha code with spaces', function () {
        $validator = Validator::make(
            ['barcode' => 'ND F'],
            ['barcode' => new BarcodeOrSkuCheck]
        );

        expect($validator->fails())->toBeTrue();
    });

    test('it passes for null value', function () {
        $validator = Validator::make(
            ['barcode' => null],
            ['barcode' => new BarcodeOrSkuCheck]
        );

        expect($validator->passes())->toBeTrue();
    });

    test('it passes for empty string', function () {
        $validator = Validator::make(
            ['barcode' => ''],
            ['barcode' => new BarcodeOrSkuCheck]
        );

        expect($validator->passes())->toBeTrue();
    });

    test('it passes for zero', function () {
        $validator = Validator::make(
            ['barcode' => 0],
            ['barcode' => new BarcodeOrSkuCheck]
        );

        expect($validator->passes())->toBeTrue();
    });

    test('it includes all formats in error message', function () {
        $validator = Validator::make(
            ['barcode' => '!!!'],
            ['barcode' => new BarcodeOrSkuCheck]
        );

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->first('barcode'))->toContain('short text code');
    });
});
