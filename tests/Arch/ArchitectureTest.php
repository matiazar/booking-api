<?php

/*
|--------------------------------------------------------------------------
| Architecture rules
|--------------------------------------------------------------------------
|
| The conventions in the README are enforced here, so they survive new
| contributors (human or AI) and don't depend on code review alone.
|
*/

use Illuminate\Foundation\Http\FormRequest;

arch('controllers stay thin: no validation, no queries')
    ->expect('App\Http\Controllers')
    ->not->toUse([
        'Illuminate\Support\Facades\DB',
        'Illuminate\Support\Facades\Validator',
        'Illuminate\Http\Request',
    ]);

arch('services do not depend on the HTTP layer')
    ->expect('App\Services')
    ->not->toUse([
        'Illuminate\Http',
        'App\Http',
        'request',
        'response',
    ]);

arch('validation lives in form requests')
    ->expect('App\Http\Requests')
    ->toExtend(FormRequest::class)
    ->toHaveSuffix('Request')
    ->toHaveMethod('rules');

arch('data objects are immutable')
    ->expect('App\Data')
    ->toBeReadonly()
    ->toBeFinal();

arch('value objects are immutable')
    ->expect('App\ValueObjects')
    ->toBeReadonly()
    ->toBeFinal();

arch('domain exceptions extend the booking base exception')
    ->expect('App\Exceptions')
    ->toExtend('App\Exceptions\BookingException')
    ->ignoring('App\Exceptions\BookingException');

arch('no debugging leftovers')
    ->expect(['dd', 'dump', 'ray', 'var_dump'])
    ->not->toBeUsed();

arch()->preset()->php();

arch()->preset()->security();

arch()->preset()->laravel();
