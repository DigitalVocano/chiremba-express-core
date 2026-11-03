@extends('errors.layout')

@section('code', '403')
@section('title', __('Access Forbidden'))
@section('status-badge', '403 • Restricted Area')
@section('badge-dot-color', 'bg-rose-500')
@section('gradient', 'from-rose-300 via-rose-500 to-red-700')

@section('message', __('You do not have administrative or authorized permissions to view this healthcare resource or portal area. If you believe this is in error, please sign in with appropriate provider or user credentials.'))
