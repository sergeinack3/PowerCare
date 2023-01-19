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
    <th>{{tr}}CSejour-type-court{{/tr}}</th>
    <th>{{tr}}CSejour-entree-court{{/tr}}</th>
    <th>{{tr}}CSejour-sortie-court{{/tr}}</th>
    <th>{{tr}}CSejour-_NDA{{/tr}}</th>
    <th>OID</th>
    <th class="narrow"></th>
  </tr>

  <tr>
    <th class="section" colspan="100">{{$objects|@count}} résultats </th>
  </tr>

  {{foreach from=$objects item=_sejour}}
    <tr>
      <td>
        {{$_sejour->_ref_patient->_view}} ({{$_sejour->_ref_patient->naissance}})
      </td>
      <td>
        {{mb_value object=$_sejour field="type"}}
      </td>
      <td>
        {{mb_value object=$_sejour field="entree"}}
      </td>
      <td>
        {{mb_value object=$_sejour field="sortie"}}
      </td>
      <td> {{$_sejour->_NDA|nl2br}} </td>
      <td> {{$_sejour->_OID|nl2br}} </td>
      <td>
        <a class="button search notext" href="#" title="Afficher le dossier complet" style="margin: -1px;">
          {{tr}}Show{{/tr}}
        </a>
      </td>
    </tr>
  {{foreachelse}}
    <tr>
      <td colspan="100" class="empty">{{tr}}No result{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>