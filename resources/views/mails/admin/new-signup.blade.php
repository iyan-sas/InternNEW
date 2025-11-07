@component('mail::message')
# New Admin Signup Needs Approval

A new admin has registered and is awaiting approval.

**Name:** {{ $user->name }}  
**Email:** {{ $user->email }}

@component('mail::button', ['url' => $pendingUrl])
Review Pending Admins
@endcomponent

Thanks,  
{{ config('app.name') }}
@endcomponent
