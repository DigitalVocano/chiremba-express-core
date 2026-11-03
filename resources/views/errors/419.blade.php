@extends('errors.layout')

@section('code', '419')
@section('title', __('Page / Session Expired'))
@section('status-badge', '419 • Security Session Timed Out')
@section('badge-dot-color', 'bg-amber-500')
@section('gradient', 'from-amber-300 via-yellow-400 to-orange-500')

@section('message', __('For patient privacy and account security, form sessions expire after periods of inactivity. Please refresh your browser or log in again to continue where you left off.'))
