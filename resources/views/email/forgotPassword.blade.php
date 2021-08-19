@component('mail::message')
# Reset YouGo Password

Click on the button below to reset your YouGo password.

@component('mail::button', ['url' => {{ env('APP_URL').'/api/auth/user/reset-password/'.$token }}])
Reset Password
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
