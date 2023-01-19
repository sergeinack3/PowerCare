{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=callback_cs value="DossierMater.onSubmitConsultPostNatale(this.form, 0);"}}

{{if $callback_cs == ""}}
  {{mb_include module=maternite template=inc_dossier_mater_header with_buttons=false grossesse=$dossier->_ref_grossesse
  patient=$dossier->_ref_grossesse->_ref_parturiente}}
{{/if}}
<form name="Consult-postnatale-{{$consult_post_natale->_guid}}" method="post" onsubmit="return onSubmitFormAjax(this);">
  {{mb_class object=$consult_post_natale}}
  {{mb_key   object=$consult_post_natale}}
  <input type="hidden" name="_count_changes" value="1" />
  <input type="hidden" name="dossier_perinat_id" value="{{$dossier->_id}}" />
  <table class="main">
    <tr>
      <td>
        <fieldset class="me-margin-0 me-small">
          <legend>{{tr}}common-Context{{/tr}}</legend>
          <table class="form me-no-align me-no-box-shadow">
            <tr>
              <th class="quarterPane">{{mb_label object=$consult_post_natale field=date}}</th>
              <td class="quarterPane">
                {{mb_field object=$consult_post_natale field=date form=Consult-postnatale-`$consult_post_natale->_guid` register=true
                onchange=$callback_cs}}
              </td>
              <th class="quarterPane">{{mb_label object=$consult_post_natale field=consultant_id}}</th>
              <td class="quarterPane">
                {{mb_field object=$consult_post_natale field=consultant_id style="width: 12em;" options=$listConsultants
                onchange=$callback_cs}}
              </td>
            </tr>
          </table>
        </fieldset>
      </td>
    </tr>
    {{if $callback_cs == ""}}
      <tr>
        <td class="button" colspan="4">
          <button type="button" class="submit" onclick="DossierMater.onSubmitConsultPostNatale(this.form, 1);">
            {{tr}}Save{{/tr}}
          </button>
          <button type="button" class="cancel" onclick="Control.Modal.close();">{{tr}}Close{{/tr}}</button>
        </td>
      </tr>
    {{/if}}
  </table>
</form>