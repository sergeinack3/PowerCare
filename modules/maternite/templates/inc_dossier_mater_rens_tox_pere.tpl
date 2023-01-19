{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="Tox-pere-{{$dossier->_guid}}" method="post"
      onsubmit="return onSubmitFormAjax(this);">
  {{mb_class object=$dossier}}
  {{mb_key   object=$dossier}}
  <input type="hidden" name="_count_changes" value="0" />
  <input type="hidden" name="grossesse_id" value="{{$grossesse->_id}}" />
  <table class="form me-no-align me-no-box-shadow me-small-form">
    <tr>
      <th class="title" colspan="2">Père</th>
    </tr>
    {{if $grossesse->pere_id}}
      <tr>
        <th class="halfPane">{{mb_label object=$dossier field=tabac_pere}}</th>
        <td>{{mb_field object=$dossier field=tabac_pere typeEnum=checkbox}}</td>
      </tr>
      <tr>
        <th><span class="compact">{{mb_label object=$dossier field=coexp_pere}}</span></th>
        <td>{{mb_field object=$dossier field=coexp_pere}}</td>
      </tr>
      <tr>
        <th>{{mb_label object=$dossier field=alcool_pere}}</th>
        <td>{{mb_field object=$dossier field=alcool_pere typeEnum=checkbox}}</td>
      </tr>
      <tr>
        <th>{{mb_label object=$dossier field=toxico_pere}}</th>
        <td>{{mb_field object=$dossier field=toxico_pere typeEnum=checkbox}}</td>
      </tr>
    {{else}}
      <tr>
        <td colspan="2" class="empty">Père non renseigné</td>
      </tr>
    {{/if}}
  </table>
</form>