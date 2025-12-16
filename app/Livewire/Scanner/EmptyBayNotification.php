<?php

namespace App\Livewire\Scanner;

use App\Actions\Scanner\HandleEmailRefillAction;
use App\Models\Product;
use Livewire\Component;

class EmptyBayNotification extends Component
{
    public ?string $barcode = null;

    public ?Product $product = null;

    public bool $isProcessing = false;

    public string $errorMessage = '';

    public function mount(
        ?string $barcode = null,
        ?Product $product = null,
    ) {
        $this->barcode = $barcode;
        $this->product = $product;
    }

    /**
     * Submit empty bay notification
     */
    public function submitNotification(): void
    {
        $this->isProcessing = true;
        $this->errorMessage = '';

        try {
            $handleEmailRefillAction = app(HandleEmailRefillAction::class);
            $result = $handleEmailRefillAction->handle(
                barcode: $this->barcode,
                product: $this->product,
                user: auth()->user()
            );

            if ($result['success']) {
                // Notify parent and immediately close - no success message needed
                $this->dispatch('empty-bay-submitted', [
                    'barcode' => $this->barcode,
                ]);
                $this->closeNotification();

                return;
            }

            $this->errorMessage = $result['error'];
        } catch (\Exception $e) {
            $this->errorMessage = 'Failed to submit notification: '.$e->getMessage();
        }

        $this->isProcessing = false;
    }

    /**
     * Close the notification and return to scanner
     */
    public function closeNotification(): void
    {
        $this->dispatch('empty-bay-closed');
    }

    /**
     * Clear error message
     */
    public function clearError(): void
    {
        $this->errorMessage = '';
    }

    public function render()
    {
        return view('livewire.scanner.empty-bay-notification');
    }
}
