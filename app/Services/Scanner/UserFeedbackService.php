<?php

namespace App\Services\Scanner;

use App\Enums\VibrationPattern;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class UserFeedbackService
{
    /**
     * Determine and trigger appropriate feedback for barcode scan
     */
    public function triggerScanFeedback(?Product $product, User $user): array
    {
        $userSettings = $user->settings ?? [];
        $feedbackState = [];

        // Sound feedback
        $feedbackState['playSuccessSound'] = $this->shouldPlaySound($product, $userSettings);

        // Vibration feedback
        $feedbackState['triggerVibration'] = $this->shouldTriggerVibration($product, $userSettings);

        if ($feedbackState['playSuccessSound'] || $feedbackState['triggerVibration']) {
            Log::debug('Triggering user feedback', [
                'product_found' => (bool) $product,
                'sound' => $feedbackState['playSuccessSound'],
                'vibration' => $feedbackState['triggerVibration'],
                'user_id' => $user->id,
            ]);
        }

        return $feedbackState;
    }

    /**
     * Get vibration pattern data for JavaScript
     */
    public function getVibrationPatternData(User $user): array
    {
        $userSettings = $user->settings ?? [];
        $vibrationPattern = VibrationPattern::fromValue($userSettings['vibration_pattern'] ?? 'medium');

        return [
            'pattern' => $vibrationPattern->pattern(),
            'label' => $vibrationPattern->label(),
            'enabled' => $vibrationPattern->isEnabled(),
        ];
    }

    /**
     * Reset feedback flags (called after feedback is triggered)
     */
    public function resetFeedbackFlags(): array
    {
        return [
            'playSuccessSound' => false,
            'triggerVibration' => false,
        ];
    }

    /**
     * Check if sound should be played for this scan
     */
    private function shouldPlaySound(?Product $product, array $userSettings): bool
    {
        // Only play sound if product was found and user has sound enabled
        $soundEnabled = $userSettings['scan_sound'] ?? true;

        return $soundEnabled && (bool) $product;
    }

    /**
     * Check if vibration should be triggered for this scan
     */
    private function shouldTriggerVibration(?Product $product, array $userSettings): bool
    {
        // Only vibrate if product was found and user has vibration enabled
        $vibrationPattern = VibrationPattern::fromValue($userSettings['vibration_pattern'] ?? 'medium');

        return $vibrationPattern->isEnabled() && (bool) $product;
    }

    /**
     * Get user settings for feedback configuration
     */
    public function getUserFeedbackSettings(User $user): array
    {
        $userSettings = $user->settings ?? [];

        return [
            'sound_enabled' => $userSettings['scan_sound'] ?? true,
            'vibration_pattern' => $userSettings['vibration_pattern'] ?? 'medium',
            'auto_submit_enabled' => $userSettings['auto_submit'] ?? false,
        ];
    }

    /**
     * Trigger feedback for manual barcode entry
     */
    public function triggerManualEntryFeedback(?Product $product, User $user): array
    {
        // Same logic as scan feedback for consistency
        return $this->triggerScanFeedback($product, $user);
    }

    /**
     * Trigger feedback for successful form submission
     */
    public function triggerSubmissionFeedback(User $user): array
    {
        // Different feedback for form submission - could be enhanced later
        $userSettings = $user->settings ?? [];

        Log::debug('Triggering submission feedback', ['user_id' => $user->id]);

        return [
            'playSuccessSound' => $userSettings['scan_sound'] ?? true,
            'triggerVibration' => false, // No vibration for form submission
        ];
    }
}
