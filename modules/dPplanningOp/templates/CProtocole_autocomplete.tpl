{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{$match->loadRefsFwd()}}

<script>
  if (window.aProtocoles) {
    aProtocoles[{{$match->protocole_id}}] = {
      {{mb_include module=planningOp template=inc_js_protocole protocole=$match nodebug=true}}
    };
  }
</script>

{{assign var=libelle value="CProtocole-No label"}}

{{if !$match->for_sejour}}
  {{if $match->libelle}}
    {{assign var=libelle value=$match->libelle}}
  {{/if}}
{{else}}
  {{if $match->libelle_sejour}}
    {{assign var=libelle value=$match->libelle_sejour}}
  {{/if}}
{{/if}}

<span id="{{$match->protocole_id}}" class="view text" style="float: left;">
  <strong>{{tr}}{{$libelle|smarty:nodefaults}}{{/tr}}</strong>
</span>


<div style="color: #666; font-size: 0.8em; padding-left: 0.5em; clear: both;">
  {{if $match->duree_hospi}}
    {{$match->duree_hospi}} nuits en
  {{/if}}

  {{mb_value object=$match field=type}}
  {{if $match->chir_id}}
    - Dr {{$match->_ref_chir->_view}}
  {{elseif $match->function_id}}
    - {{$match->_ref_function->_view}} -
    <span {{if !"dPplanningOp CProtocole use_protocole_current_etab"|gconf && $match->_ref_function->group_id == $current_group->_id}}style="background-color: #ffa;"{{/if}}>
      {{$match->_ref_function->_ref_group->_view}}
    </span>
  {{elseif $match->group_id}}
    - {{$match->_ref_group->_view}}
  {{/if}}
  <br />

  {{if $match->_ext_code_cim->code}}
    {{$match->_ext_code_cim->code}}
  {{/if}}

  {{foreach from=$match->_ext_codes_ccam item=_code}}
    {{$_code->code}}
    <br />
  {{/foreach}}
</div>