<?php

namespace Tests\Unit\Actions;

use App\Actions\LinnworksStockAction;
use App\Models\Scan;
use App\Models\User;

describe('LinnworksStockAction', function () {

    test('it decreases stock when action is decrease', function () {
        $user = User::factory()->create();
        $scan = Scan::factory()->create([
            'quantity' => 5,
            'action' => 'decrease',
            'user_id' => $user->id,
        ]);

        $action = new LinnworksStockAction($scan, 100);
        $newStock = $action->handle();

        expect($newStock)->toBe(95);
    });

    test('it decreases stock when action is null', function () {
        $user = User::factory()->create();
        $scan = Scan::factory()->create([
            'quantity' => 10,
            'action' => null,
            'user_id' => $user->id,
        ]);

        $action = new LinnworksStockAction($scan, 50);
        $newStock = $action->handle();

        expect($newStock)->toBe(40);
    });

    test('it increases stock when action is increase', function () {
        $user = User::factory()->create();
        $scan = Scan::factory()->create([
            'quantity' => 15,
            'action' => 'increase',
            'user_id' => $user->id,
        ]);

        $action = new LinnworksStockAction($scan, 30);
        $newStock = $action->handle();

        expect($newStock)->toBe(45);
    });

    test('it prevents negative stock when decreasing', function () {
        $user = User::factory()->create();
        $scan = Scan::factory()->create([
            'quantity' => 10,
            'action' => 'decrease',
            'user_id' => $user->id,
        ]);

        $action = new LinnworksStockAction($scan, 5); // Current stock is 5
        $newStock = $action->handle();

        expect($newStock)->toBe(0); // Should be 0, not -5
    });

    test('it handles zero current stock', function () {
        $user = User::factory()->create();
        $scan = Scan::factory()->create([
            'quantity' => 5,
            'action' => 'decrease',
            'user_id' => $user->id,
        ]);

        $action = new LinnworksStockAction($scan, 0);
        $newStock = $action->handle();

        expect($newStock)->toBe(0);
    });

    test('it can increase from zero stock', function () {
        $user = User::factory()->create();
        $scan = Scan::factory()->create([
            'quantity' => 20,
            'action' => 'increase',
            'user_id' => $user->id,
        ]);

        $action = new LinnworksStockAction($scan, 0);
        $newStock = $action->handle();

        expect($newStock)->toBe(20);
    });
});
