<?php

namespace App\Livewire\Invites;

use App\Models\Invite;
use App\Models\User;
use App\Notifications\InviteNotification;
use Illuminate\Support\Str;
use Livewire\Component;

class BulkCreate extends Component
{
    public string $emails = '';

    public array $parsedEmails = [];

    public array $errors = [];

    public array $successes = [];

    protected $rules = [
        'emails' => 'required|string',
    ];

    public function parseEmails()
    {
        $this->validate();
        $this->errors = [];
        $this->successes = [];
        $this->parsedEmails = [];

        // Parse emails from the textarea (support comma, semicolon, newline, or space separation)
        $rawEmails = preg_split('/[,;\n\s]+/', $this->emails, -1, PREG_SPLIT_NO_EMPTY);

        foreach ($rawEmails as $email) {
            $email = trim($email);

            // Extract name if provided in format "Name <email@example.com>"
            if (preg_match('/^(.+?)\s*<(.+?)>$/', $email, $matches)) {
                $name = trim($matches[1]);
                $emailAddress = trim($matches[2]);
            } else {
                $emailAddress = $email;
                $name = explode('@', $emailAddress)[0]; // Use part before @ as name
            }

            // Validate email format
            if (! filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
                $this->errors[] = "Invalid email format: $email";

                continue;
            }

            // Check if email already exists
            if (User::where('email', $emailAddress)->exists()) {
                $this->errors[] = "Email already registered: $emailAddress";

                continue;
            }

            // Check if invite already exists
            if (Invite::where('email', $emailAddress)->whereNull('accepted_at')->exists()) {
                $this->errors[] = "Invite already sent to: $emailAddress";

                continue;
            }

            $this->parsedEmails[] = [
                'name' => $name,
                'email' => $emailAddress,
            ];
        }
    }

    public function sendInvites()
    {
        $this->errors = [];
        $this->successes = [];

        foreach ($this->parsedEmails as $userData) {
            try {
                // Create user with random password
                $user = User::create([
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'password' => bcrypt(Str::random(32)),
                    'status' => false,
                ]);

                // Create invite
                $invite = Invite::create([
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'user_id' => $user->id,
                    'invited_by' => auth()->id(),
                    'token' => Str::random(64),
                    'accepted_at' => null,
                    'expires_at' => now()->addHours(24),
                ]);

                // Send notification
                $invite->notify(new InviteNotification($invite));

                $this->successes[] = "Invite sent to: {$userData['email']}";
            } catch (\Exception $e) {
                $this->errors[] = "Failed to send invite to {$userData['email']}: ".$e->getMessage();
            }
        }

        // Clear parsed emails after sending
        if (count($this->successes) > 0) {
            $this->parsedEmails = [];
            $this->emails = '';
            $this->dispatch('invites-sent');
        }
    }

    public function removeEmail($index)
    {
        unset($this->parsedEmails[$index]);
        $this->parsedEmails = array_values($this->parsedEmails);
    }

    public function render()
    {
        return view('livewire.invites.bulk-create');
    }
}
