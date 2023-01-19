{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<h2>Check list "pose d'un catheter veineux central (CVC) ou autre dispositif vasculaire (DV)"</h2>
<hr />
<h3>{{$patient->_view}} ({{$patient->_age}}
{{if $patient->_annees != "??"}}- {{mb_value object=$patient field="naissance"}}{{/if}})
</h3>

<h3>
{{tr}}CSejour{{/tr}} 
du {{mb_value object=$sejour field=entree}}
au {{mb_value object=$sejour field=sortie_prevue}}
</h3>

<table class="main tbl">
  <tr>
    <td>
      <strong>{{mb_label object=$pose field=date}}</strong>: {{mb_value object=$pose field=date}}<br />
      <strong>{{mb_label object=$pose field=lieu}}</strong>: {{mb_value object=$pose field=lieu}}<br />
      <strong>{{mb_label object=$pose field=urgence}}</strong>: {{mb_value object=$pose field=urgence}}<br />
    </td>
    <td>
      <strong>{{mb_label object=$pose field=operateur_id}}</strong>: {{mb_value object=$pose field=operateur_id}}<br />
      <strong>{{mb_label object=$pose field=encadrant_id}}</strong>: {{mb_value object=$pose field=encadrant_id}}<br />
    </td>
    <td>
      <strong>{{mb_label object=$pose field=type_materiel}}</strong>: {{mb_value object=$pose field=type_materiel}}<br />
      <strong>{{mb_label object=$pose field=voie_abord_vasc}}</strong>: {{mb_value object=$pose field=voie_abord_vasc}}<br />
    </td>
  </tr>
</table>

{{mb_include module=salleOp template=inc_vw_check_lists object=$pose}}