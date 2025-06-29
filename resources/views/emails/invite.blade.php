<x-mail::message>
# You're Invited to Join {{ $appName }}

Hello {{ $name }},

You have been invited to join {{ $appName }}. To accept this invitation and set up your account, please click the button below.

<x-mail::button :url="$url">
Set Up Your Account
</x-mail::button>

This invitation link will expire in {{ $expiresIn }} {{ Str::plural('hour', $expiresIn) }}.

If you're unable to click the button above, you can copy and paste the following link into your browser:

<x-mail::panel>
{{ $url }}
</x-mail::panel>

If you didn't expect this invitation, you can safely ignore this email.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
