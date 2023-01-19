{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=set_teeth_callback value='syncDentField'}}

<div class="ccam-dents">
  <img src="modules/dPccam/images/dents.png" style="width: 407px; height: 389px;"/>
  {{assign var=_dent_coords value='Ox\Mediboard\Patients\CEtatDent'|static:"_dents"}}
  {{assign var=count_sectors value=0}}

  {{foreach from=$liste_dents item=_dent}}
    {{assign var=dent_ok value=true}}
    {{assign var=dent_localisation value=$_dent->localisation}}
    {{foreach from=$phase->dents_incomp item=_incomp}}
      {{if $dent_localisation == $_incomp->localisation}}
        {{assign var=dent_ok value=false}}
      {{/if}}
    {{/foreach}}

    {{if array_key_exists($dent_localisation,$_dent_coords)}}
      {{assign var=_coords value=$_dent_coords.$dent_localisation}}

      {{if $dent_ok}}
        <input class="schema" type="checkbox" name="dent_{{$_dent->localisation}}" data-localisation="{{$_dent->localisation}}"
               {{if in_array($_dent->localisation, $acte->_dents)}}checked{{/if}} onchange="{{$set_teeth_callback}}(this);" />
      {{/if}}
      <label class="schema" for="dent_{{$_dent->localisation}}"
             style="{{if !$dent_ok}} cursor: no-drop; {{/if}} left: {{$_coords.0-$_coords.2}}px; top: {{$_coords.1-$_coords.2}}px; width: {{$_coords.2+$_coords.2}}px; height: {{$_coords.2+$_coords.2}}px;"></label>
    {{else}}
      {{if $dent_ok}}
        {{math assign=count_sectors equation="x+1" x=$count_sectors}}
        <label for="dent_{{$_dent->localisation}}" title="Localisation : {{$_dent->localisation}}" class="schema-out">
          <input type="checkbox" name="dent_{{$_dent->localisation}}" data-localisation="{{$_dent->localisation}}"
                 {{if in_array($_dent->localisation, $acte->_dents)}}checked{{/if}} onchange="{{$set_teeth_callback}}(this);" />
          {{$_dent->_libelle}}
        </label>
        {{if $count_sectors == 4}}
          <br/>
          {{assign var=count_sectors value=0}}
        {{/if}}
      {{else}}
        <span style="display: none;">
          {{$_dent->_libelle}}
        </span>
      {{/if}}
    {{/if}}
  {{/foreach}}
</div>

{{mb_field object=$acte field=position_dentaire hidden=true}}
<input type="hidden" name="count_teeth_checked" value="{{$teeth_checked}}">
