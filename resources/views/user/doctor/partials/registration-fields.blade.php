<div class="rounded-md shadow-sm space-y-4">
    <div>
        <label for="{{ $prefix }}_name" class="sr-only">{{__('Name')}}</label>
        <input type="text" id="{{ $prefix }}_name" name="name" placeholder="{{__('Your name')}}" required value="{{old('name')}}"
               class="appearance-none rounded-md relative block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 placeholder-gray-500 dark:placeholder-gray-400 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-700 focus:outline-none focus:ring-primary-600 focus:border-primary-600 focus:z-10 sm:text-sm"/>
        @if ($errors->has('name'))
            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $errors->first('name') }}</p>
        @endif
    </div>
    <div>
        <label for="{{ $prefix }}_phone" class="sr-only">{{__('Phone')}}</label>
        <input type="text" name="phone" id="{{ $prefix }}_phone" placeholder="{{__('Enter Your Phone number')}}" required value="{{old('phone')}}"
               class="appearance-none rounded-md relative block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 placeholder-gray-500 dark:placeholder-gray-400 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-700 focus:outline-none focus:ring-primary-600 focus:border-primary-600 focus:z-10 sm:text-sm"/>
        @if ($errors->has('phone'))
            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $errors->first('phone') }}</p>
        @endif
    </div>
    <div>
        <label for="{{ $prefix }}_email" class="sr-only">{{__('Email (Optional)')}}</label>
        <input type="email" name="email" id="{{ $prefix }}_email" placeholder="{{__('Your email (Optional)')}}" value="{{old('email')}}"
               class="appearance-none rounded-md relative block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 placeholder-gray-500 dark:placeholder-gray-400 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-700 focus:outline-none focus:ring-primary-600 focus:border-primary-600 focus:z-10 sm:text-sm"/>
        @if ($errors->has('email'))
            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $errors->first('email') }}</p>
        @endif
    </div>
    <div>
        <label for="{{ $prefix }}_pwd" class="sr-only">{{__('Password')}}</label>
        <input type="password" name="password" id="{{ $prefix }}_pwd" placeholder="{{__('Enter password')}}" required
               class="appearance-none rounded-md relative block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 placeholder-gray-500 dark:placeholder-gray-400 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-700 focus:outline-none focus:ring-primary-600 focus:border-primary-600 focus:z-10 sm:text-sm"/>
        @if ($errors->has('password'))
            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $errors->first('password') }}</p>
        @endif
    </div>
    <div>
        <label for="{{ $prefix }}_cpwd" class="sr-only">{{__('Confirm password')}}</label>
        <input type="password" name="password_confirmation" id="{{ $prefix }}_cpwd" placeholder="{{__('Enter Confirm password')}}" required
               class="appearance-none rounded-md relative block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 placeholder-gray-500 dark:placeholder-gray-400 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-700 focus:outline-none focus:ring-primary-600 focus:border-primary-600 focus:z-10 sm:text-sm"/>
        <span id="{{ $prefix }}_cpwd_error" class="text-xs text-red-600 dark:text-red-400 mt-1"></span>
        @if ($errors->has('password_confirmation'))
            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $errors->first('password_confirmation') }}</p>
        @endif
    </div>
    <div>
        <label for="{{ $prefix }}_referred_by_code" class="sr-only">{{__('Referral Code (Optional)')}}</label>
        <input type="text" name="referred_by_code" id="{{ $prefix }}_referred_by_code" placeholder="{{__('Referral Code (Optional)')}}" value="{{old('referred_by_code')}}"
               class="appearance-none rounded-md relative block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 placeholder-gray-500 dark:placeholder-gray-400 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-700 focus:outline-none focus:ring-primary-600 focus:border-primary-600 focus:z-10 sm:text-sm"/>
        @if ($errors->has('referred_by_code'))
            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $errors->first('referred_by_code') }}</p>
        @endif
    </div>
</div>

<div class="flex items-center mt-6">
    <input id="{{ $prefix }}_agree" name="agree" type="checkbox" value="1" required
           class="h-4 w-4 text-primary-600 focus:ring-primary-600 border-gray-300 dark:border-gray-600 rounded dark:bg-gray-700 dark:checked:bg-primary-600 dark:focus:ring-offset-gray-800">
    <label for="{{ $prefix }}_agree" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">
        {{__('I accept')}} <a href="{{url('/')}}" class="font-medium text-primary-600 hover:text-primary-600">{{__('terms')}}</a> {{__('and')}} <a href="{{url('/')}}" class="font-medium text-primary-600 hover:text-primary-600">{{__('conditions')}}</a> {{__('and general policy')}}
    </label>
</div>
@if ($errors->has('agree'))
    <p class="text-sm text-red-600 dark:text-red-400">{{ $errors->first('agree') }}</p>
@endif