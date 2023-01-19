{{*
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=edit value=0}}
{{mb_default var=view value=""}}
{{mb_default var=with_button value=1}}
{{mb_default var=with_print value=0}}
{{mb_default var=only_souhait value=0}}
{{mb_default var=realise value=0}}

{{if "dPhospi prestations systeme_prestations"|gconf == "standard"}}
  {{if !$edit}}
    {{if $sejour->chambre_seule}}
      <div>{{mb_label object=$sejour field=chambre_seule}}</div>
    {{/if}}
    {{mb_value object=$sejour field=prestation_id tooltip=1}}
    {{mb_return}}
  {{/if}}
  
  <div>
    <form name="Chambre-{{$sejour->_guid}}" method="post" class="prepared" onsubmit="return onSubmitFormAjax(this);">
      {{mb_class object=$sejour}}
      {{mb_key   object=$sejour}}
      {{mb_field object=$sejour field=chambre_seule typeEnum=checkbox onchange="this.form.onsubmit();"}}
      {{mb_label object=$sejour field=chambre_seule typeEnum=checkbox}}
    </form>
  </div>
    
  {{if isset($prestations|smarty:nodefaults)}}
  <div>
    <form name="Prestations-{{$sejour->_guid}}" method="post" class="prepared" onsubmit="return onSubmitFormAjax(this);">
      {{mb_class object=$sejour}}
      {{mb_key   object=$sejour}}
      {{mb_field object=$sejour field=prestation_id choose=CPrestation options=$prestations onchange="this.form.onsubmit();"}}
    </form>
  </div>
  {{/if}}
{{/if}}

{{if "dPhospi prestations systeme_prestations"|gconf == "expert"}}
  {{if !$edit}}
    Prestations
    {{mb_return}}
  {{/if}}
  
  {{assign var=opacity value=""}}
  {{assign var=class   value=help}}
  {{if array_key_exists("items_liaisons", $sejour->_count)}}
    {{assign var=class value=search}}
    {{if !$sejour->_count.items_liaisons}}
      {{assign var=opacity value=opacity-60}}
    {{/if}}
  {{/if}}

  {{if $view != "compact" && $with_button}}
    <button type="button" class="{{$class}} {{$opacity}} me-small" onclick="Prestations.edit('{{$sejour->_id}}')">
      Prestations
    </button>
  {{/if}}

  {{if $with_print}}
    <button type="button" class="print notext me-tertiary me-small" onclick="Prestations.print('{{$sejour->_id}}', '0');">{{tr}}Print{{/tr}}</button>
  {{/if}}

  {{if array_key_exists("items_liaisons", $sejour->_back) && $sejour->_back.items_liaisons|@count}}
    {{foreach from=$sejour->_back.items_liaisons item=_liaison}}
      {{assign var=_item value=$_liaison->_ref_item}}

      {{if $realise && $_item->object_class === "CPrestationJournaliere"}}
        {{assign var=_item value=$_liaison->_ref_item_realise}}
      {{/if}}

      {{if $_item->_id && ($_item->object_class === "CPrestationJournaliere" || $_item->_ref_object->show_admission)}}

        {{if $_liaison->sous_item_id && $_liaison->_ref_sous_item->item_prestation_id === $_item->_id}}
          {{assign var=nom_item value=$_liaison->_ref_sous_item->nom}}
          {{assign var=title value=$_liaison->_ref_sous_item->nom}}
        {{else}}
            {{assign var=title value=$_item->nom}}
            {{if $_item->nom_court}}
                {{assign var=nom_item value=$_item->nom_court}}
            {{else}}
                {{assign var=nom_item value=$_item->nom|truncate:20}}
            {{/if}}
        {{/if}}
        <div title="{{$title}}" style="border-left: 2px solid #{{$_item->color}};
          {{if $view == "compact"}}
            {{if !$_liaison->sous_item_id}}
            background-color: #{{$_item->color}};
            {{/if}}
          width:15px;height:15px;text-align: center;margin-left: 20px;
          {{/if}}">
          {{if $view == "compact"}}
            {{$nom_item|truncate:1:""|capitalize}}
          {{else}}
            {{$nom_item}}
          {{/if}}
        </div>
      {{/if}}
    {{/foreach}}
  {{/if}}
{{/if}}
