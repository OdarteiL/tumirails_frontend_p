@extends('emails.layout')

@section('title', 'Reset Your Password')

@section('content')
    <p>Hi {{ $user->first_name }},</p>
    <p>We received a request to reset the password for your Tumi Solar Configurator account.</p>
    <p>Click the button below to choose a new password. This link will expire in <strong>{{ $expiryMinutes }} minutes</strong>.</p>
    <p style="text-align: center;">
        <a href="{{ $resetUrl }}" class="btn">Reset Password</a>
    </p>
    <hr class="divider">
    <p class="note">If the button doesn't work, copy and paste this link into your browser:</p>
    <p class="note" style="word-break: break-all;">{{ $resetUrl }}</p>
    <p class="note">If you did not request a password reset, no action is needed — your password will remain unchanged.</p>
@endsection
