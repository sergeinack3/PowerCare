{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{mb_default var=consult value=false}}

{{if !$consult}}
  {{assign var=consult_anesth value=$operation->_ref_consult_anesth}}
{{/if}}

{{if $consult_anesth->_ref_operation && $consult_anesth->_ref_operation->_id}}
  {{assign var=operation value=$consult_anesth->_ref_operation}}
{{/if}}

<form name="editOpFrm" action="?m=cabinet" method="post" onsubmit="return onSubmitFormAjax(this);" style="float: right;">
  <input type="hidden" name="del" value="0" />
  {{mb_key object=$operation}}
  {{mb_class object=$operation}}
  <fieldset class="me-padding-left-8 me-padding-right-8 me-padding-bottom-8 me-small" style="margin-top: 0;">
    <legend style="padding-bottom: 0.5em;">
      <label title="{{tr}}COperation-overpayment-desc{{/tr}}">
        {{tr}}COperation-overpayment-court{{/tr}}
      </label>
    </legend>
    <table class="layout main">
      {{if $operation->_id}}
        {{assign var=show_depassement_chir value='Ox\Mediboard\Ccam\CCodageCCAM::getVisibiliteDepassement'|static_call:$operation->_ref_chir}}
        {{if $show_depassement_chir}}
          <tr>
            <th class="me-text-align-right" style="vertical-align: middle;">{{tr}}COperation-depassement{{/tr}}</th>
            <td>
              {{mb_value object=$operation field=depassement}}
            </td>
          </tr>
        {{/if}}
        <tr class="me-small-fields">
          <th class="me-text-align-right" style="vertical-align: middle;">{{mb_label object=$operation field="depassement_anesth"}}</th>
          <td class="me-small-fields">
            {{mb_field object=$operation field="depassement_anesth" onchange="this.form.onsubmit();" size=3 style="width: 30px;"}}

            <button type="button" class="{{if $operation->commentaire_depassement_anesth}}edit{{else}}add{{/if}} notext me-small"
                    onclick="Modal.open('com_dep_anesth-{{$operation->_id}}', {width: 500, showClose: true, title: $T('common-Comment')});"
                    title="{{tr}}COperation-commentaire_depassement_anesth-desc{{/tr}}">
            </button>

            <table id="com_dep_anesth-{{$operation->_id}}" class="tbl" style="width: 350px; display: none">
              <tr>
                <th class="title">{{mb_label object=$operation field=commentaire_depassement_anesth}}</th>
              </tr>
              <tr>
                <td>{{mb_field object=$operation field=commentaire_depassement_anesth}}</td>
              </tr>
              <tr>
                <td class="button">
                  <button class="save" onclick="Control.Modal.close();">{{tr}}Save{{/tr}}</button>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr  class="me-small-fields">
          <th class="me-valign-middle me-text-align-right">{{mb_title object=$operation field=reglement_dh_anesth}}</th>
          <td>{{mb_field object=$operation field=reglement_dh_anesth onchange="this.form.onsubmit();"}}</td>
        </tr>
      {{else}}
        {{if $consult_anesth->depassement_anesth}}
          <tr>
            <th class="me-valign-middle me-text-align-right">{{mb_title object=$consult_anesth field=depassement_anesth}}</th>
            <td>{{mb_value object=$consult_anesth field=depassement_anesth}}</td>
          </tr>
        {{/if}}
      {{/if}}
    </table>
  </fieldset>
  {{if $conf.dPplanningOp.COperation.verif_cote && ($operation->cote == "droit" || $operation->cote == "gauche")}}
    <fieldset class="me-small-fields">
      <legend>{{tr}}COperation-cote_consult_anesth{{/tr}}</legend>
      {{mb_field object=$operation field=cote_consult_anesth emptyLabel="COperation.cote_consult_anesth." onchange="this.form.onsubmit();"}}
    </fieldset>
  {{/if}}
</form>