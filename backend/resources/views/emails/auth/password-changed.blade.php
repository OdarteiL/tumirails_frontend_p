@extends('emails.layout')

@section('title', 'Password Changed Successfully')

@section('content')
    <p>Hi {{ $user->first_name }},</p>
    <p>This is a confirmation that the password for your Tumi Solar Configurator account (<strong>{{ $user->email }}</strong>) was successfully changed.</p>
    <p>If you made this change, no further action is needed.</p>
    <hr class="divider">
    <p class="note">If you did not change your password, please contact our support team immediately, as your account may have been compromised.</p>
@endsection
