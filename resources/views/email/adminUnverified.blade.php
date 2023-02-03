<div>
  <img src="https://ci4.googleusercontent.com/proxy/55_HP2fNns_bR04vxPF6Uz7H3usr7v6roxxA5c68cflexKAyvBo8g0G6nOaXJqsGk7QwN-kkoH92DwTusoZ6KkIAj3BbJluApshXAAQnfqOGfOIkyMgU=s0-d-e1-ft#https://www.remotestaff.com.au/wp-content/uploads/2020/09/235x74.png" class="CToWUd" data-bit="iit">
</div>
<div>
  <table cellspacing="1" style="border-collapse:collapse;margin:25px 0;font-size:0.9em;font-family:sans-serif;min-width:400px">
    <thead>
      <tr style="background-color:#009879;color:#ffffff;text-align:left">
        <th style="padding:12px 15px">Jobseeker ID</th>
        <th style="padding:12px 15px">Personal Email</th>
        <th style="padding:12px 15px;width:115px">Date Registered</th>
        <th style="padding:12px 15px;width:115px">Expiry Date</th>
      </tr>
    </thead>
    <tbody>
      @foreach($expired_emails as $expired)
        <tr style="border-bottom:1px solid #dddddd;background-color:#ffffff">
          <td style="padding:12px 15px">{{ $expired['id'] }}</td>
          <td style="padding:12px 15px">{{ $expired['email'] }}</td>
          <td style="padding:12px 15px">{{ $expired['date_submitted'] }}</td>
          <td style="padding:12px 15px">{{ $expired['date_expired'] }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
  <p>System Generated Unverified Jobseeker</p>
  <p>Date Generated: {{$date}}</p>
</div>