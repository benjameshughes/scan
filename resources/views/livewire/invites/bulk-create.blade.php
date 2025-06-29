<div>
    <div class="max-w-4xl mx-auto">
        <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg border border-zinc-200 dark:border-zinc-700">
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Bulk Invite Users</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Send invitations to multiple users at once</p>
            </div>

            <form class="p-6 space-y-6">
                <!-- Instructions -->
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h4 class="text-sm font-medium text-blue-800 dark:text-blue-200 mb-1">
                                How to format email addresses
                            </h4>
                            <p class="text-sm text-blue-700 dark:text-blue-300">
                                Enter email addresses separated by commas, semicolons, or new lines.
                                You can also use the format: <code class="bg-blue-100 dark:bg-blue-800 px-1 rounded text-xs">Name &lt;email@example.com&gt;</code>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Email Input -->
                <div class="space-y-2">
                    <flux:label for="emails" class="text-sm font-medium text-gray-700 dark:text-gray-200">
                        Email Addresses <span class="text-red-500">*</span>
                    </flux:label>
                    <flux:textarea 
                        wire:model="emails" 
                        name="emails"
                        id="emails" 
                        rows="6"
                        placeholder="John Doe <john@example.com>
jane@example.com
Mike Smith <mike@example.com>, sarah@example.com"
                        class="w-full resize-vertical"
                        required
                    />
                    <flux:error name="emails"/>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Enter one email per line or separate multiple emails with commas
                    </p>
                </div>

                <!-- Parse Button -->
                <div class="flex justify-start">
                    <flux:button wire:click="parseEmails" variant="ghost" type="button">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Parse & Validate Emails
                    </flux:button>
                </div>

                <!-- Errors -->
                @if(count($errors) > 0)
                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                                    Validation Errors
                                </h3>
                                <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                                    <ul class="list-disc list-inside">
                                        @foreach($errors as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Success Messages -->
                @if(count($successes) > 0)
                    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-green-800 dark:text-green-200">
                                    Invitations Sent Successfully
                                </h3>
                                <div class="mt-2 text-sm text-green-700 dark:text-green-300">
                                    <ul class="list-disc list-inside">
                                        @foreach($successes as $success)
                                            <li>{{ $success }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Parsed Emails Preview -->
                @if(count($parsedEmails) > 0)
                    <div>
                        <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3">
                            Ready to Send ({{ count($parsedEmails) }} {{ Str::plural('invitation', count($parsedEmails)) }})
                        </h4>
                        <div class="space-y-2">
                            @foreach($parsedEmails as $index => $userData)
                                <div class="flex items-center justify-between bg-zinc-50 dark:bg-zinc-700 rounded-lg px-4 py-3">
                                    <div>
                                        <span class="font-medium text-gray-900 dark:text-gray-100">{{ $userData['name'] }}</span>
                                        <span class="text-gray-600 dark:text-gray-400 ml-2">&lt;{{ $userData['email'] }}&gt;</span>
                                    </div>
                                    <flux:button 
                                        wire:click="removeEmail({{ $index }})" 
                                        variant="ghost" 
                                        size="sm"
                                    >
                                        Remove
                                    </flux:button>
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="flex items-center justify-between pt-6 mt-6 border-t border-zinc-200 dark:border-zinc-700">
                            <flux:button wire:click="$set('parsedEmails', [])" variant="ghost" type="button">
                                Clear All
                            </flux:button>
                            
                            <flux:button wire:click="sendInvites" variant="primary" type="button" class="ml-3">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                Send {{ count($parsedEmails) }} {{ Str::plural('Invitation', count($parsedEmails)) }}
                            </flux:button>
                        </div>
                    </div>
                @endif
            </form>
        </div>
    </div>
</div>