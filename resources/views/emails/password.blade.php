@extends('emails.template')

@section('content')
<h1>Hi {{$user->name}},</h1>
<p>You recently requested to reset your password for your WingLib account. Click the button below to reset it.</p>
<!-- Action -->
<table class="body-action" align="center" width="100%" cellpadding="0" cellspacing="0">
  <tr>
    <td align="center">
      <div>
        <!--[if mso]><v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ url('password/reset/'.$token) }}" style="height:45px;v-text-anchor:middle;width:200px;" arcsize="7%" stroke="f" fill="t">
          <v:fill type="tile" color="#dc4d2f" />
          <w:anchorlock/>
          <center style="color:#ffffff;font-family:sans-serif;font-size:15px;">Reset your password</center>
        </v:roundrect><![endif]-->
        <a href="{{ url('password/reset/'.$token) }}" class="button button--red">Reset your password</a>
      </div>
    </td>
  </tr>
</table>
<p>If you did not request a password reset, please ignore this email or reply to let us know. This password reset is only valid for the next 120 minutes.</p>
<p>Thanks,<br>Sergio Bruder and the WingLib Team</p>
<p><strong>P.S.</strong> We also love hearing from you and helping you with any issues you have. Please reply to this email if you want to ask a question or just say hi.</p>
<!-- Sub copy -->
<table class="body-sub">
  <tr>
    <td>
      <p class="sub">If you’re having trouble clicking the password reset button, copy and paste the URL below into your web browser.</p>
      <p class="sub"><a href="{{ url('password/reset/'.$token) }}">{{ url('password/reset/'.$token) }}</a></p>
    </td>
  </tr>
</table>
@stop
