{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl form">
  {{mb_include module=system template=inc_form_table_header object=$consult_anesth}}
  <tr>
    <td rowspan="2">
      {{if $consult_anesth->_id}}
        {{if !$consult_anesth->operation_id}}
          <div class="big-info">
            Une consultation préanesthésique a été effectuée pour le séjour de ce patient
            le <strong>{{$consult_anesth->_date_consult|date_format:$conf.date}}</strong>
            par {{if $consult_anesth->_ref_chir->isPraticien()}}le <strong>Dr{{else}}
              <strong>{{/if}} {{$consult_anesth->_ref_chir->_view}}</strong>.
              {{tr}}CAnesthPerop-msg-You must associate this anesthesia file with the current procedure if you wish to access the Cormack score{{/tr}}
          </div>
        {{else}}
          <form name="editFormCormack" method="post" onsubmit="return onSubmitFormAjax(this)">
            <input type="hidden" name="m" value="cabinet" />
            <input type="hidden" name="del" value="0" />
            <input type="hidden" name="dosql" value="do_consult_anesth_aed" />
            {{mb_key object=$consult_anesth}}
            <table class="form">
              <tr>
                <td>
                  <table class="form">
                    <tr>
                      {{foreach from=$consult_anesth->_specs.cormack->_locales key=key_cormack item=item_cormack}}
                        <td class="button">
                          <label for="cormack_{{$key_cormack}}" title="Cormack de {{$item_cormack}}">
                            <input type="radio" name="cormack" value="{{$key_cormack}}"
                                   {{if $consult_anesth->cormack == $key_cormack}}checked{{/if}} onclick="this.form.onsubmit();" />
                            {{$item_cormack}}
                          </label>
                        </td>
                      {{/foreach}}
                    </tr>
                  </table>
                  <input type="radio" style="display: none;" name="cormack" value="" {{if !$consult_anesth->cormack}}checked{{/if}}
                         onclick="this.form.onsubmit();" />
                </td>
              </tr>
              <tr>
                <td colspan="4">
                  {{mb_label object=$consult_anesth field="com_cormack"}}
                </td>
              </tr>
              <tr>
                <td colspan="4">
                  {{mb_field object=$consult_anesth field="com_cormack" rows="8" onchange="this.form.onsubmit()" form="editFormCormack" aidesaisie="validateOnBlur: 0"}}
                </td>
              </tr>
            </table>
          </form>
        {{/if}}
      {{else}}
        <div class="big-info">
          {{tr}}CAnesthPerop-msg-No anesthesia record was associated with this intervention or stay{{/tr}}
          <br />
          {{tr}}CAnesthPerop-msg-You can either: associate an anesthesia file with a past consultation, or create a new anesthesia file{{/tr}}
        </div>
      {{/if}}
    </td>
  </tr>
</table>
