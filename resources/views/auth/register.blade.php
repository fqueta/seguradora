{{-- @extends('adminlte::auth.register') --}}
@php
header("Location: " . URL::to('/user/create'), true, 302);
exit();
@endphp
