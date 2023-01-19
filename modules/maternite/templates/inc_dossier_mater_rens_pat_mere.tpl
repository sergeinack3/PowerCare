{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="Mere-{{$patient->_guid}}" method="post"
      onsubmit="return onSubmitFormAjax(this);">
  {{mb_class object=$patient}}
  {{mb_key   object=$patient}}
  <input type="hidden" name="_count_changes" value="0" />
  <table class="form me-no-box-shadow me-no-align me-small-form">
    <tr>
      <th class="halfPane">{{mb_label object=$patient field=situation_famille}}</th>
      <td>
        {{mb_field object=$patient field=situation_famille
        style="width: 12em;" emptyLabel="CPatient.situation_famille."}}
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$patient field=mdv_familiale}}</th>
      <td>
        {{mb_field object=$patient field=mdv_familiale
        style="width: 12em;" emptyLabel="CPatient.mdv_familiale."}}
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$patient field=pays}}</th>
      <td>{{mb_field object=$patient field=pays}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$patient field=_pays_naissance_insee}}</th>
      <td>{{mb_field object=$patient field=_pays_naissance_insee}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$patient field=niveau_etudes}}</th>
      <td>
        {{mb_field object=$patient field=niveau_etudes
        style="width: 12em;" emptyLabel="CPatient.niveau_etudes."}}
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$patient field=profession}}</th>
      <td>{{mb_field object=$patient field=profession style="width: 10em;"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$patient field=csp}}</th>
      <td>{{mb_field object=$patient field=csp}}</td>
    </tr>
  </table>
</form>