@extends('errors.layout')

@section('code', '500')
@section('title', __('Internal Server Error'))
@section('status-badge', '500 • Medical System Alert')
@section('badge-dot-color', 'bg-red-500')
@section('gradient', 'from-amber-200 via-orange-400 to-rose-600')

@section('message', __('Our server encountered an unexpected issue while processing your medical request. Our technical staff has been automatically alerted and is diagnosing the situation. Please try again in a few moments.'))
