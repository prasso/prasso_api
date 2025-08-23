<p>Hi,</p>

<p>{{$user_email}} has registered for site {{ $site_name }} as a user! <br>
<a href="https://applink.faxt.com/team/{{$user_current_team_id}}/messages?user={{$user_email}}">Send greetings and introductions with the app</a></p>
<p><a href="https://faxt.com/team/{{$user_current_team_id}}/messages?user={{$user_email}}">Send greetings and introductions from the web</a></p>

<br>
Thanks,<br>
{{config('constants.ADMIN_EMAIL_SIGNATURE')}}