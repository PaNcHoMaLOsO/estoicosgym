<?php
// PhpStorm Meta file for Laravel framework and packages
// This file is created to ensure proper IDE support for Laravel facades and helpers

namespace PHPSTORM_META {
    // Laravel Facades
    override(\view(), type('@'));
    override(\redirect(), type('@'));
    override(\response(), type('@'));
    override(\app(), type('@'));
    override(\now(), type('\Carbon\Carbon'));
    
    // Eloquent methods
    override(\Illuminate\Database\Eloquent\Builder::get(), type('@[]'));
    override(\Illuminate\Database\Eloquent\Builder::first(), type('@'));
    override(\Illuminate\Database\Eloquent\Builder::find(), type('@'));
    override(\Illuminate\Database\Eloquent\Builder::findOrFail(), type('@'));
    override(\Illuminate\Database\Eloquent\Builder::create(), type('@'));
    override(\Illuminate\Database\Eloquent\Builder::update(), type('@'));
    override(\Illuminate\Database\Eloquent\Builder::delete(), type('@'));
    
    // Carbon methods
    override(\Carbon\Carbon::parse(), type('\Carbon\Carbon'));
    override(\Carbon\Carbon::createFromFormat(), type('\Carbon\Carbon'));
}
