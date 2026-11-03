@extends('errors.layout')

@section('code', '401')
@section('title', __('Unauthorized Access'))
@section('status-badge', '401 • Authentication Required')
@section('badge-dot-color', 'bg-amber-400')
@section('gradient', 'from-amber-200 via-orange-400 to-rose-500')

@section('message', __('Authentication credentials were not provided or have expired. Please log in with your patient or doctor account to access this section of Chiremba Express.'))
