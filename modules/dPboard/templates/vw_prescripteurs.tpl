{{*
 * @package Mediboard\Board
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  changePagePrescripteur = function(page) {
    new Url('board', 'vw_stats', 'tab')
      .addParam('start_prescripteur', page)
      .redirect();
  }
</script>

{{mb_include module=system template=inc_pagination change_page="changePagePrescripteur" total=$total_prescripteurs current=$start_prescripteurs step=$step_prescripteurs}}

<table class="tbl">
  <tr>
    <th class="title" colspan="4">
      Médecins traitants les plus prescripteurs
    </th>
  </tr>

  <tr>
    <th colspan="3">{{mb_label class=CPatient field=medecin_traitant}}</th>
    <th>Nombre de patients</th>
  </tr>

  {{foreach from=$prescripteurs key=medecin_id item=nb_patients}}
  <tr>
    <td>
      {{assign var=medecin value=$medecins.$medecin_id}}
      <span onmouseover="ObjectTooltip.createEx(this, '{{$medecin->_guid}}')">
      {{$medecin}}
      </span>
    </td>
    <td class="text">{{$medecin->adresse}}, {{$medecin->cp}} {{$medecin->ville}}</td>
    <td>{{mb_value object=$medecin field=tel}}</td>
    <td class="button">{{$nb_patients}}</td>
  </tr>
  {{foreachelse}}
  <tr>
    <td colspan="2" class="empty">{{tr}}None{{/tr}}</td>
  </tr>

  {{/foreach}}
</table>

