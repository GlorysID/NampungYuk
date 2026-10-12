<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('admin:promote {user : Username or email of the user to promote}', function (string $user) {
    $target = User::where('username', $user)
        ->orWhere('email', $user)
        ->first();

    if (! $target) {
        $this->error("Pengguna dengan username/email '{$user}' tidak ditemukan!");

        return 1;
    }

    $target->is_admin = true;
    $target->save();

    $this->info("Berhasil! Pengguna {$target->name} (@{$target->username}) sekarang resmi menjadi Admin NampungYuk.");

    return 0;
})->purpose('Promosikan pengguna menjadi Administrator');

Artisan::command('admin:demote {user : Username or email of the user to demote}', function (string $user) {
    $target = User::where('username', $user)
        ->orWhere('email', $user)
        ->first();

    if (! $target) {
        $this->error("Pengguna dengan username/email '{$user}' tidak ditemukan!");

        return 1;
    }

    $target->is_admin = false;
    $target->save();

    $this->info("Pengguna {$target->name} (@{$target->username}) telah dicabut status Admin-nya.");

    return 0;
})->purpose('Cabut status Administrator dari pengguna');
