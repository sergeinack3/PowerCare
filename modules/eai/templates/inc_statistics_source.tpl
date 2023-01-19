{{if $statistics }}
  <fieldset>
    <legend>{{tr}}CSourceFTP-legend-Informations{{/tr}}</legend>
    <table class="main form">
      <tr>
        <th>{{mb_label object=$statistics field="nb_call"}}</th>
        <td>{{mb_field object=$statistics field="nb_call" readonly="readonly"}}</td>
      </tr>
      <tr>
        <th>{{mb_label object=$statistics field="failures"}}</th>
        <td>{{mb_field object=$statistics field="failures" readonly="readonly"}}</td>
      </tr>
      <tr>
        <th>{{mb_label object=$statistics field="last_response_time"}}</th>
        <td>{{mb_field object=$statistics field="last_response_time" readonly="readonly"}}</td>
      </tr>

      <tr>
        <th>{{mb_label object=$statistics field="last_status"}}</th>
        <td>{{mb_field object=$statistics field="last_status" readonly="readonly"}}</td>
      </tr>

      <tr>
        <th>{{mb_label object=$statistics field="last_connexion_date"}}</th>
        <td>{{mb_field object=$statistics field="last_connexion_date" readonly="readonly"}}</td>
      </tr>

      <tr>
        <th>{{mb_label object=$statistics field="last_verification_date"}}</th>
        <td>{{mb_field object=$statistics field="last_verification_date" readonly="readonly"}}</td>
      </tr>
    </table>
  </fieldset>
{{/if}}
