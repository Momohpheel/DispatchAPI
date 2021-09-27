@component('mail::message')
# Reset YouGo Password

Click on the button below to reset your YouGo password.

@component('mail::button', ['url' => env('APP_URL').'/partner/reset-password?token='.$token ])
Reset Password
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
