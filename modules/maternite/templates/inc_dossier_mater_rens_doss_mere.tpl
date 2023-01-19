{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="Mere-{{$dossier->_guid}}" method="post"
      onsubmit="return onSubmitFormAjax(this);">
  {{mb_class object=$dossier}}
  {{mb_key   object=$dossier}}
  <input type="hidden" name="_count_changes" value="0" />
  <input type="hidden" name="grossesse_id" value="{{$grossesse->_id}}" />
  <table class="form me-no-align me-no-box-shadow me-small-form">
    <tr>
      <th class="halfPane">{{mb_label object=$dossier field=activite_pro}}</th>
      <td>
        {{mb_field object=$dossier field=activite_pro
        style="width: 12em;" emptyLabel="CDossierPerinat.activite_pro."}}
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$dossier field=fatigue_travail}}</th>
      <td>{{mb_field object=$dossier field=fatigue_travail default=""}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$dossier field=travail_hebdo}}</th>
      <td>{{mb_field object=$dossier field=travail_hebdo}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$dossier field=transport_jour}}</th>
      <td>{{mb_field object=$dossier field=transport_jour}}</td>
    </tr>
  </table>
</form>