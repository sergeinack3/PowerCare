<table>
  <thead>
  <tr>
    <th colspan="2">Refus de vaccination</th>
  </tr>
  </thead>
  <tbody>
  <tr>
    <td>{{mb_value object=$injection field=injection_date}}</td>
    <td>
      <content ID="{{$injection->_guid}}">{{$vaccination_name}}</content>
    </td>
  </tr>
  </tbody>
</table>
