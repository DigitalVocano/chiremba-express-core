@extends('errors.layout')

@section('code', '503')
@section('title', __('System Under Maintenance'))
@section('status-badge', '503 • Scheduled Medical Upgrade')
@section('badge-dot-color', 'bg-emerald-400')
@section('gradient', 'from-emerald-300 via-teal-400 to-cyan-500')

@section('message', __('Chiremba Express is currently undergoing routine maintenance and database optimization to improve clinical performance. Services will be back online shortly. For critical emergency services, please contact your nearest local hospital.'))
