@extends('onboarding.layout')

@section('step-content')
    <form method="POST" action="{{ route('onboarding.step.store', ['step' => $step]) }}" id="onboarding-step-form"
        class="space-y-6">
        @csrf

        @yield('form-fields')
    </form>
@endsection