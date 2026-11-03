@extends('admin.layout')
@section('title')
    {{("Edit Referral Reward")}}
@stop
@section('content')
<div class="container mx-auto px-4 sm:px-8">
    <div class="py-8">
        <div>
            <h2 class="text-2xl font-semibold leading-tight">{{("Edit Referral Reward")}}</h2>
        </div>
        <div class="mt-5 bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
            <form action="{{ route('admin.referrals.rewards.update', $reward) }}" method="POST" x-data="{ type: '{{ $reward->type }}' }">
                @csrf
                @method('PUT')
                @include('admin.referrals.rewards._form')
                <div class="mt-6">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        {{("Update Reward")}}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop
