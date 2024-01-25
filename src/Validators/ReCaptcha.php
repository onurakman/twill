<?php

namespace A17\Twill\Validators;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;

class ReCaptcha implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $response = Http::asForm()->post(
            'https://www.google.com/recaptcha/api/siteverify',
            [
                'secret' => config('services.recaptcha.secret'),
                'response' => $value,
            ]
        )->object();

        if (! $response->success || $response->score < 0.6) {
            $fail('Invalid recaptcha token');
        }
    }
}

