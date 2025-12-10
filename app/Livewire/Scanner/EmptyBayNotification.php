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

    public string $successMessage = '';

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
        $this->successMessage = '';

        try {
            $handleEmailRefillAction = app(HandleEmailRefillAction::class);
            $result = $handleEmailRefillAction->handle(
                barcode: $this->barcode,
                product: $this->product,
                user: auth()->user()
            );

            if ($result['success']) {
                $this->successMessage = $result['message'];

                // Notify parent component
                $this->dispatch('empty-bay-submitted', [
                    'message' => $result['message'],
                    'barcode' => $this->barcode,
                ]);

                // Auto-close after success
                $this->js('setTimeout(() => { $wire.closeNotification() }, 3000)');
            } else {
                $this->errorMessage = $result['error'];
            }

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

    /**
     * Clear success message
     */
    public function clearSuccess(): void
    {
        $this->successMessage = '';
    }

    public function render()
    {
        return view('livewire.scanner.empty-bay-notification');
    }
}
