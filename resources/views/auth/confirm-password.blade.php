@extends('layouts.auth')
@section('content')
    <div class="max-w-lg mx-auto w-full px-4 my-auto">
        <div class="text-center">
            <h1 class="block text-2xl lg:text-3xl font-semibold text-gray-800 dark:text-white">{{ __('Confirm your password') }}</h1>
            <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
            </p>
        </div>

        <div class="mt-10">
            <form method="POST" action="{{ route('password.confirm') }}">
                @csrf

                <div>
                    <x-form.label for="password" :value="__('Password')"/>
                    <x-form.input id="password" class="block mt-1 w-full"
                                  type="password"
                                  name="password"
                                  required autocomplete="current-password" placeholder="{{ __('Password') }}"/>
                    <x-form.error :messages="$errors->get('password')" class="mt-2"/>
                </div>

                <div class="mt-5">
                    <x-form.primary class="w-full">
                        {{ __('Confirm') }}
                    </x-form.primary>
                </div>
            </form>
        </div>
    </div>
@endsection
