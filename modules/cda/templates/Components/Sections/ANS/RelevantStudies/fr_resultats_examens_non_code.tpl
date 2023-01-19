{{assign var=points_attention value="Aucun point de vigilance pour le patient"}}

{{if $dossier_medical->_id && $dossier_medical->points_attention}}
    {{assign var=points_attention value=$dossier_medical->points_attention}}
{{/if}}

<table>
  <tbody>
  <tr>
    <td>{{$points_attention|smarty:nodefaults|purify}}</td>
  </tr>
  </tbody>
</table>
