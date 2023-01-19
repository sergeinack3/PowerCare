{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{assign var=select_name value="valeur"}}

{{if $valeurs|@count}}
  {{if $protocole_item && $protocole_item->_id && $protocole_settings}}
    <form name="gestePrecisionValeur{{$geste->_id}}" action="?" target="#" method="post"
    onsubmit="return onSubmitFormAjax(this);">
    {{mb_key   object=$protocole_item}}
    {{mb_class object=$protocole_item}}

    {{assign var=select_name value="precision_valeur_id"}}
  {{/if}}

  <select id="valeur_{{$geste->_id}}" class="select_{{$geste->_id}}" name="{{$select_name}}"
          {{if !$checked_item && !$protocole_settings}}disabled{{/if}}
          onchange="{{if $protocole_item && $protocole_item->_id && $protocole_settings}}
            this.form.onsubmit();
            {{else}}
            GestePerop.bindElementValeur(this.down('option:selected'), '{{$geste->_id}}', '{{$geste->libelle|smarty:nodefaults|JSAttribute}}', '{{$precision->_id}}', '{{$precision->libelle|smarty:nodefaults|JSAttribute}}');
            {{/if}}"
          style="width: 250px;">
    <option value="">&mdash; {{tr}}None|f{{/tr}}</option>
    {{foreach from=$valeurs item=_valeur}}
      <option id="valeur_{{$_valeur->_id}}" value="{{$_valeur->_id}}"
              {{if $_valeur->_id == $protocole_item->precision_valeur_id}}selected{{/if}}>
        {{$_valeur->_view}}
      </option>
    {{/foreach}}
  </select>

  {{if $protocole_item && $protocole_item->_id && $protocole_settings}}
    </form>
  {{/if}}
{{else}}
  <div class="empty" style="width: 250px;">
    &mdash; {{tr}}CPrecisionValeur.none{{/tr}}
  </div>
{{/if}}

