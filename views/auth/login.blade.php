@extends('twill::auth.layout', [
    'route' => route('admin.login'),
    'screenTitle' => twillTrans('twill::lang.auth.login-title')
])
@push('extra_js_head')
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
@endpush
@section('form')
    <fieldset class="login__fieldset">
        <label class="login__label" for="email">{{ twillTrans('twill::lang.auth.email') }}</label>
        <input type="email" name="email" id="email" class="login__input" required autofocus tabindex="1" value="{{ old('email') }}" />
    </fieldset>

    <fieldset class="login__fieldset">
        <label class="login__label" for="password">{{ twillTrans('twill::lang.auth.password') }}</label>
        <a href="{{ route('admin.password.reset.link') }}" class="login__help f--small" tabindex="5"><span>{{ twillTrans('twill::lang.auth.forgot-password') }}</span></a>
        <input type="password" name="password" id="password" class="login__input" required tabindex="2" />
    </fieldset>

    @if(config('services.recaptcha.key'))
        <button class="login__button g-recaptcha"
               data-sitekey="{{ config('services.recaptcha.key') }}"
               data-callback="onLoginSubmit"
               data-action="submit" tabindex="3">
            {{ twillTrans('twill::lang.auth.login') }}
        </button>
    @else
        <input class="login__button" type="submit" value="{{ twillTrans('twill::lang.auth.login') }}" tabindex="3">
    @endif

    @if (config('twill.enabled.users-oauth', false))
        @foreach(config('twill.oauth.providers', []) as $index => $provider)
            <a href="{!! route('admin.login.redirect', $provider) !!}" class="login__socialite login__{{$provider}}" tabindex="{{ 4 + $index }}">
                @includeIf('twill::auth.icons.' . $provider)
                <span>Sign in with {{ ucfirst($provider)}}</span>
            </a>
        @endforeach
    @endif

@stop
