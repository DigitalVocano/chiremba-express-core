@extends('errors.layout')

@section('code', '404')
@section('title', __('Page Not Found'))
@section('status-badge', '404 • Lost in the Clinic')
@section('badge-dot-color', 'bg-amber-400')
@section('gradient', 'from-teal-200 via-teal-400 to-emerald-600')

@section('message', __('The page or healthcare service you are looking for might have been moved, renamed, or is temporarily unavailable. Check the URL or explore our certified doctors, pharmacies, and developer APIs below.'))
