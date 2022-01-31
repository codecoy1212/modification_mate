@component('mail::message')

<h2 style="color: rgb(37, 43, 59)">Hello There</h2>

<div style="color: rgb(37, 43, 59); margin-bottom:30px">
Your password is changed to <b style="color: black"> {{$vbl->password}} </b>. Use this password to login to the system.

<h2 style="color: rgb(37, 43, 59); margin-top:30px">
Thanks,<br>
{{ config('app.name') }}
</h2>

@endcomponent
