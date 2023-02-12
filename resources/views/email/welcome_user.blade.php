<div style="max-width: 800px;background:#fefefe;padding:20px;">
@if ($site->logo_image)
<img src="{{ $site->logo_image }}" style="max-width:100px;margin:auto;text-align:center;margin-bottom:20px;" alt="{{ $site->site_name }}"/>
@else
<img src="{{ config('constants.cloudfront_asset_url').'/prasso/prasso_logo.png' }}" style="max-width:100px;margin:auto;text-align:center;margin-bottom:20px;" alt="Prasso"/>
@endif
<p>Hi,</p>

<p>Welcome to {{ $site->site_name }}!</p>
<p>We're so happy you are here!</p>

<p>We'll continue to check in with you and give you some great tips.</p>


<br>
Thanks,<br>
{{config('constants.ADMIN_EMAIL_SIGNATURE')}}
<br><br>
<div style="font-size:.9em;color:#999999;float:right;margin-right:10px;">
<a href="https://prasso.io/unsubscribe?email={{$user_email}}">Unsubscribe from our emails</a>
</div>
</div>


