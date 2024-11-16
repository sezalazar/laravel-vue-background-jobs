@extends('_layouts.email')

@section('mailTitle', 'Background Job Failed')

@section('mailContent')
<div style="text-align: center;">
    <img src="{{ asset('images/easypeasy_logo.jpeg') }}" alt="Logo" style="width: 150px; height: auto;">

    <h1>Background Job Failed</h1>

    <p>Dear Support Team,</p>
    <p>An error ocurred while executing a background job. Find below the details:</p>

    <div style="text-align: left; margin: 0 auto; max-width: 600px;">
        <strong>Job Details:</strong>
        <ul>
            <li><strong>Class:</strong> {{ $className }}</li>
            <li><strong>Method:</strong> {{ $methodName }}</li>
            <li><strong>Attempts:</strong> {{ $attempts }}</li>
            <li><strong>Error Message:</strong> {{ $errorMessage }}</li>
        </ul>
    </div>

    <p>Regards,</p>
    <p><br>{{ config('app.company_name') }}</br></p>
</div>
@endsection