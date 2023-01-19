{{*
 * @package Mediboard\Sip
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function(){
    var form = getForm("find_candidates");
    form.select(".transaction").each(function(button) {
      button.disabled = '{{$pointer}}' ? "" : "disabled";
    });

    $V(form.pointer,   '{{$pointer}}');
    $V(form.query_tag, '{{$query_tag}}');
  });
</script>

<table class="tbl">
  <tr>
    <th>{{tr}}CPatient{{/tr}}</th>
    <th class="narrow">{{tr}}CPatient-naissance-court{{/tr}}</th>
    <th>{{tr}}CPatient-sexe{{/tr}}</th>
    <th>{{tr}}CPatient-adresse{{/tr}}</th>
    <th>{{tr}}CPatient-_IPP{{/tr}}</th>
    <th>OID</th>
    <th class="narrow"></th>
  </tr>

  <tr>
    <th class="section" colspan="100">{{$objects|@count}} résultats </th>
  </tr>

  {{foreach from=$objects item=_patient}}
    <tr>
      <td>
        <div class="text noted">
        {{mb_value object=$_patient field="_view"}}
        </div>
      </td>
      <td>
        {{mb_value object=$_patient field="naissance"}}
      </td>
      <td>
        {{mb_value object=$_patient field="sexe"}}
      </td>
      <td class="text compact">
        <span style="white-space: nowrap;">{{$_patient->adresse|spancate:30}}</span>
        <span style="white-space: nowrap;">{{$_patient->cp}} {{$_patient->ville|spancate:20}}</span>
      </td>
      <td> {{$_patient->_IPP|nl2br}} </td>
      <td> {{$_patient->_OID|nl2br}} </td>
      <td>
        <a class="button search notext" href="#" title="Afficher le dossier complet" style="margin: -1px;">
          {{tr}}Show{{/tr}}
        </a>
      </td>
    </tr>
  {{foreachelse}}
    <tr>
      <td colspan="100" class="empty">{{tr}}dPpatients-CPatient-no-exact-results{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>