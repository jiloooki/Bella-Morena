@extends('layouts.auth')
@section('content')
    <div class="max-w-lg mx-auto w-full px-4 my-auto">
        <div class="text-center">
            <h1 class="block text-2xl lg:text-3xl font-semibold text-gray-800 dark:text-white">{{ __('Verify your email') }}</h1>
            <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
            </p>
        </div>

        <div class="mt-10 space-y-6">
            @if (session('status') == 'verification-link-sent')
                <div class="rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700 dark:border-green-900/60 dark:bg-green-950/40 dark:text-green-300">
                    {{ __('A new verification link has been sent to the email address you provided during registration.') }}
                </div>
            @endif

            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <x-form.primary class="w-full">
                    {{ __('Resend Verification Email') }}
                </x-form.primary>
            </form>

            <form method="POST" action="{{ route('logout') }}" class="text-center">
                @csrf
                <button type="submit" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:focus:ring-offset-gray-800">
                    {{ __('Log Out') }}
                </button>
            </form>
        </div>
    </div>
@endsection
