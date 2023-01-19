{{*
 * @package Mediboard\soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  lockCodagesOp = function(praticien_id, codable_class, codable_id, date, export_acts) {
    {{if "dPccam codage lock_codage_ccam"|gconf == 'password'}}
      var url = new Url('ccam', 'checkLockCodage');
    {{else}}
      var url = new Url('ccam', 'lockCodage');
    {{/if}}
    url.addParam('praticien_id', praticien_id);
    url.addParam('codable_class', codable_class);
    url.addParam('codable_id', codable_id);
    url.addParam('lock', 1);
    url.addParam('export', export_acts);

    {{if "dPccam codage lock_codage_ccam"|gconf == 'password'}}
      url.requestModal(null, null, {onClose: loadCodagesCCAM.curry('{{$subject->_id}}', date)});
    {{else}}
      url.requestUpdate('systemMsg', {
        onComplete: loadCodagesCCAM.curry('{{$subject->_id}}', date),
        method: 'post',
        getParameters: {m: 'ccam',a: 'lockCodage'}
      });
    {{/if}}
  };

  unlockCodagesOp = function(praticien_id, codable_class, codable_id, date) {
    {{if "dPccam codage lock_codage_ccam"|gconf == 'password'}}
      var url = new Url('ccam', 'checkLockCodage');
    {{else}}
      var url = new Url('ccam', 'lockCodage');
    {{/if}}
    url.addParam('praticien_id', praticien_id);
    url.addParam('codable_class', codable_class);
    url.addParam('codable_id', codable_id);
    url.addParam('lock', 0);
    {{if "dPccam codage lock_codage_ccam"|gconf == 'password'}}
      url.requestModal(null, null, {onClose: loadCodagesCCAM.curry('{{$subject->_id}}', date)});
    {{else}}
      url.requestUpdate('systemMsg', {
        onComplete: loadCodagesCCAM.curry('{{$subject->_id}}', date),
        method: 'post',
        getParameters: {m: 'ccam',a: 'lockCodage'}
      });
    {{/if}}
  };
</script>

{{math assign=colspan equation="x+1" x=$days|@count}}
<tbody id="operations_codages">
  {{foreach from=$operations item=_operation}}
    <tr>
      <th class="category" colspan="{{$colspan}}" style="font-weight: bold; text-align: center;">
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_operation->_guid}}');">{{$_operation}}</span>
      </th>
    </tr>
    {{if !$_operation->_coded}}
      {{foreach from=$_operation->_ref_codages_ccam key=_praticien_id item=_codages_by_prat name=codages_op}}
        <tr{{if !$smarty.foreach.codages_op.last}} style="border-bottom: 1pt dotted #93917e;"{{/if}}>
          <td>
            {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$praticiens[$_praticien_id]}}
          </td>

         {{foreach from=$days item=_day}}
           {{if $_operation->date == $_day}}
            {{assign var=total value=0}}
            {{assign var=count_actes value=0}}
            <td style="text-align: center;">
              {{foreach from=$_codages_by_prat item=_codage name=codages_op_by_prat}}
                {{assign var=codage_locked value=$_codage->locked}}
                {{math assign=count_actes equation="x+y" x=$count_actes y=$_codage->_ref_actes_ccam|@count}}
                {{math assign=total equation="x+y" x=$total y=$_codage->_total}}

                <form name="formCodage-{{$_praticien_id}}-{{$_operation->_guid}}" data-date="{{$_codage->date}}" data-praticien_id="{{$_codage->praticien_id}}" action="?" method="post"
                      onsubmit="return onSubmitFormAjax(this{{if $smarty.foreach.codages_op_by_prat.first}}, {onComplete: loadCodagesCCAM.curry({{$subject->_id}},'{{$_codage->date}}')}{{/if}});">
                  {{mb_class object=$_codage}}
                  {{mb_key object=$_codage}}
                  <input type="hidden" name="del" value="0" />
                  <input type="hidden" name="locked" value="{{$_codage->locked}}"/>
                  {{if $_codage->_ref_actes_ccam|@count != 0}}
                    <div style="position: relative; min-height: 22px; vertical-align: middle;{{if $smarty.foreach.codages_op_by_prat.first && !$smarty.foreach.codages_op_by_prat.last}}border-bottom: 1pt dotted #93917e;{{/if}}">
                      <span onclick="editCodages('{{$_operation->_class}}', {{$_operation->_id}}, {{$_codage->praticien_id}}, '{{$_day}}');"
                            onmouseover="ObjectTooltip.createEx(this, '{{$_codage->_guid}}');" style="font-size: 0.85em;"
                          {{if $codage_rights == 'self' && ($user->_id != $_codage->praticien_id && (!@$modules.dPpmsi->_can->edit && $user->_is_professionnel_sante))}} disabled{{/if}}>
                        {{foreach from=$_codage->_ref_actes_ccam item=_act name=codages}}
                          {{if !$smarty.foreach.codages_op.first || !$smarty.foreach.codages_op_by_prat.first}}
                            <br/>
                          {{/if}}
                          {{$_act->code_acte}} <span class="circled ok">{{$_act->code_activite}}-{{$_act->code_phase}}</span>
                        {{/foreach}}
                        {{if $smarty.foreach.codages_op_by_prat.last && $count_actes == 0}}
                          {{tr}}CActeCCAM.none{{/tr}}
                        {{/if}}
                      </span>
                    </div>
                  {{/if}}
                </form>
              {{/foreach}}

              {{if $total != 0}}
                <div style="font-size: 0.85em;">
                  Total : {{$total|number_format:2:',':' '}} {{$conf.currency_symbol|html_entity_decode}}
                </div>
              {{/if}}

              <div{{if $count_actes !=0}} style="border-top: 1pt dotted #93917e;"{{/if}}>
                {{if !$codage_locked}}
                  <button type="button" class="notext edit" onclick="editCodages('{{$_operation->_class}}', {{$_operation->_id}}, {{$_praticien_id}}, '{{$_day}}')"
                          title="{{tr}}Edit{{/tr}}"
                          {{if $codage_rights == 'self' && ($user->_id != $_codage->praticien_id && (!@$modules.dPpmsi->_can->edit && $user->_is_professionnel_sante))}} disabled{{/if}}>
                    {{tr}}Edit{{/tr}}
                  </button>
                {{/if}}

                {{if $codage_locked}}
                  <button type="button" class="notext cancel"
                          onclick="unlockCodagesOp({{$_codage->praticien_id}}, '{{$_codage->codable_class}}', {{$_codage->codable_id}}, '{{$_day}}')"
                          {{if $codage_rights == 'self' && ($user->_id != $_codage->praticien_id && (!@$modules.dPpmsi->_can->edit && $user->_is_professionnel_sante))}} disabled{{/if}}>
                    {{tr}}Unlock{{/tr}}
                  </button>
                {{else}}
                  <button type="button" class="notext tick" {{if $count_actes == 0}}disabled="disabled"{{/if}}
                          onclick="lockCodagesOp({{$_codage->praticien_id}}, '{{$_codage->codable_class}}', {{$_codage->codable_id}}, '{{$_day}}', '{{'dPccam codage export_on_codage_lock'|gconf}}')"
                          {{if $codage_rights == 'self' && ($user->_id != $_codage->praticien_id && (!@$modules.dPpmsi->_can->edit && $user->_is_professionnel_sante))}} disabled{{/if}}>
                    {{tr}}Lock{{/tr}}
                  </button>
                {{/if}}

                {{if $count_actes == 0}}
                  <button type="button" class="notext trash"
                          onclick="deleteCodages({{$_praticien_id}}, '{{$_day}}')"
                          {{if $codage_rights == 'self' && ($user->_id != $_codage->praticien_id && (!@$modules.dPpmsi->_can->edit && $user->_is_professionnel_sante))}} disabled{{/if}}>
                    {{tr}}Delete{{/tr}}
                  </button>
                {{/if}}
              </div>
            </td>
           {{else}}
             <td class="empty"></td>
           {{/if}}
         {{/foreach}}
        </tr>
      {{foreachelse}}
        <tr>
          {{foreach from=$days item=_day}}
            <td class="empty">
              {{if $_operation->date == $_day}}
                {{tr}}CCodageCCAM.none{{/tr}}
              {{/if}}
            </td>
          {{/foreach}}
        </tr>
      {{/foreach}}
    {{else}}
      {{mb_script module=pmsi script=PMSI ajax=true}}
      {{mb_script module=ccam script=CCodageCCAM ajax=true}}
      <tr>
        <td colspan="{{$colspan}}">
          {{mb_include module=pmsi template=inc_codage_actes show_ngap=false read_only=true sbject=$_operation}}
        </td>
      </tr>
    {{/if}}
  {{/foreach}}
</tbody>
