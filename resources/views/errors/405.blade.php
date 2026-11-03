@extends('errors.layout')

@section('code', '405')
@section('title', __('Method Not Allowed'))
@section('status-badge', '405 • Unsupported HTTP Method')
@section('badge-dot-color', 'bg-violet-500')
@section('gradient', 'from-violet-300 via-purple-400 to-indigo-600')

@section('message', __('The request method used (e.g. GET / POST / PUT) is not permitted for this specific endpoint or route. If you are developing an API integration or webhook, verify endpoint documentation in our Developer Portal.'))
