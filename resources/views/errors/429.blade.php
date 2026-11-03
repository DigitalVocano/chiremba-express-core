@extends('errors.layout')

@section('code', '429')
@section('title', __('Too Many Requests'))
@section('status-badge', '429 • Rate Limit Protection')
@section('badge-dot-color', 'bg-cyan-500')
@section('gradient', 'from-cyan-300 via-teal-400 to-blue-600')

@section('message', __('You have sent too many requests in a short period of time. Our security firewall temporarily throttled additional actions to protect platform integrity. Please pause for a moment before retrying.'))
