@php
    // This view is now redundant as all subscription payments should go through the wallet.
    // If this page is accessed, it means a direct payment was attempted, which is no longer supported.
    // Redirect to the doctor dashboard with an error 
    Session::flash('message', __('Direct payment for subscriptions is not allowed. Please top up your wallet and purchase the plan from there.'));
    Session::flash('alert-class', 'alert-danger');
@endphp
<script>
    window.location.href = "{{ url('doctordashboard') }}";
</script>
<noscript>
    <p>Direct payment for subscriptions is not allowed. Please top up your wallet and purchase the plan from there.</p>
    <a href="{{ url('doctordashboard') }}">Go to Dashboard</a>
</noscript>
