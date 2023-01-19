{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">

choosePatient = function() {
  var from = getForm("editConsult");
  
  if ($V(from.prat_id) == '') {
    alert($T('common-Practitioner.choose_select'));
    return;
  }
  
  var to = getForm("Create-Reconvocation");

  // Les nouvelles valeurs sont mises dans le formulaire
  $V(to.motif    , $V(from.motif));
  $V(to._datetime, $V(from._datetime));
  $V(to._prat_id , $V(from.prat_id));
  
  Control.Modal.close();
  PatSelector.init();
}
</script>
<form name="editConsult" method="get" action="?">
  <table class="form">
    <tr>
      <th colspan="2" class="title">{{tr}}CRPU.urprov.RC{{/tr}}</th>
    </tr>
    <tr>
      <th>{{tr}}CConsultation-_prat_id{{/tr}}</th>
      <td>
        <select name="prat_id" class="ref notNull">
          <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
          {{mb_include module=mediusers template=inc_options_mediuser list=$praticiens}}
        </select>
      </td>
    </tr>
    <tr>
      <th>
        {{tr}}CConsultation-_date{{/tr}}
      </th>
      <td>
        {{mb_field object=$consult field=_datetime register=true form=editConsult}}
      </td>
    </tr>
    <tr>
      <th>
        {{tr}}CConsultation-motif{{/tr}}
      </th>
      <td>
        {{mb_field object=$consult field=motif form="editConsult"
          autocomplete="timestamp: '`$conf.dPcompteRendu.CCompteRendu.timestamp`', validateOnBlur: 0"}}
      </td>
    </tr>
    <tr>
      <td colspan="2" style="text-align: center;">
        <button type="button" class="tick" onclick="choosePatient()">{{tr}}CPatient.select{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>