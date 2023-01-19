{{assign var=occupational_risk_factor value="Aucun facteur de risque pour le patient"}}

{{if $dossier_medical->_id && $dossier_medical->occupational_risk_factor}}
    {{assign var=occupational_risk_factor value=$dossier_medical->occupational_risk_factor}}
{{/if}}

<table>
  <tbody>
  <tr>
    <td>{{$occupational_risk_factor|smarty:nodefaults|purify}}</td>
  </tr>
  </tbody>
</table>
