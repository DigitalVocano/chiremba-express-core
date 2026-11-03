@extends('errors.layout')

@section('code', $exception->getStatusCode() ?? 'Error')
@section('title', __($exception->getMessage() ?: 'Unexpected Error'))
@section('status-badge', ($exception->getStatusCode() ?? 'System') . ' • Notice')
@section('badge-dot-color', 'bg-teal-400')
@section('gradient', 'from-teal-200 via-teal-400 to-emerald-600')

@section('message', __($exception->getMessage() ?: 'An unexpected response was returned by the system. Please return to the homepage or search for doctors and medical services below.'))
