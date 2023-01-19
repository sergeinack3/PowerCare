{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=edit value=0}}

<script>
  Main.add(function () {
    $$("td#atcd_alle button.noAlle").invoke("hide");
  });
</script>

<div style="float:right;">
  {{if "soins Other ignore_allergies"|gconf && !$antecedents|@count && $type == "alle"}}
    {{assign var=ignore_allergies value="soins Other ignore_allergies"|gconf}}
    {{assign var=ignore_allergies_one value="|"|explode:$ignore_allergies}}
    <form name="save_aucun_atcd" action="?" method="post" onsubmit="return onSubmitFormAjax(this, function() {
      DossierMater.refreshAtcd('{{$patient->_id}}', 'alle', 1, 'list_{{$type}}');
      });" style="float: right;" class="not-printable">
      <input type="hidden" name="m" value="patients" />
      <input type="hidden" name="del" value="0" />
      <input type="hidden" name="dosql" value="do_antecedent_aed" />
      <input type="hidden" name="type" value="alle" />
      <input type="hidden" name="rques" value="{{$ignore_allergies_one[0]}}" />
      <input type="hidden" name="absence" value="1" />
      <input type="hidden" name="_patient_id" value="{{$patient->_id}}" />
      <button type="button" class="tick noAlle" onclick="this.form.onsubmit();">{{tr}}Allergie.none{{/tr}}</button>
    </form>
  {{/if}}
</div>


<ul>
  {{foreach from=$antecedents item=_antecedent}}
    <li>
      {{if $edit && $_antecedent->owner_id == $app->user_id}}
        <form name="delAtcd{{$_antecedent->_id}}" method="post">
          {{mb_class object=$_antecedent}}
          {{mb_key object=$_antecedent}}
          <button type="button" class="trash notext" title="{{tr}}Delete{{/tr}}"
                  onclick="confirmDeletion(this.form, {
                    typeName: 'l\'antécédent',
                    objName: '{{$_antecedent->rques|JSAttribute}}'},
                    DossierMater.refreshAtcd.curry('{{$patient->_id}}', '{{$type}}', {{$edit}}, 'list_{{$type}}'));"></button>
        </form>
      {{/if}}

      {{if $_antecedent->date}}
        {{mb_value object=$_antecedent field=date}} :
      {{/if}}
      {{$_antecedent->rques|nl2br}}
      {{foreachelse}}
    <li class="empty">{{if $type == "alle"}}{{tr}}Allergie.none{{/tr}}{{else}}{{tr}}CAntecedent.unknown{{/tr}}{{/if}}</li>
  {{/foreach}}
</ul>
