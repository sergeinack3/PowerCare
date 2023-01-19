{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table id="inc_list_tarifs_table_{{$mode}}" class="tbl">
  <tr>
    <th id="inc_list_tarifs_th_{{$mode}}" colspan="10" class="title">{{tr}}{{$mode}}-back-tarifs{{/tr}}</th>
  </tr>
  <tr>
    <th colspan="10">
      {{if ($mode == "CMediusers" && $prat->_id)
      || ($mode == "CFunctions" && $prat->function_id)}}
          {{if $mode == 'CMediusers'}}
            {{assign var=target value='CFunctions'}}
            {{assign var=direction value='right'}}
            {{assign var=word value='cabinet'}}
            <button onclick="Tarif.edit(0, '{{$prat->_id}}')" class="new me-primary" id="btn_new_tarif" style="float: left">
              {{tr}}CTarif-title-create{{/tr}}
            </button>
          {{elseif $mode == 'CFunctions'}}
            {{assign var=target value='CMediusers'}}
            {{assign var=word value='praticien'}}
            {{assign var=direction value='left'}}
          {{/if}}
        <button type="button" style="float: {{if $mode === "CFunctions"}}left{{else}}right{{/if}};" onclick="Tarif.switchOwner('{{$mode}}', '{{$target}}', '{{$prat->_id}}');"
                title="Basculer les tarifs sélectionnés vers le {{$word}}" class="{{$direction}} notext">
        </button>
        <form name="recalculTarifs{{$mode}}" method="post" action="?" style="float: right;"
              onsubmit="return onSubmitFormAjax(this, {onComplete:  function() {Tarif.reloadListTarifs('{{$prat->_id}}', '{{$mode}}');} });">
          <input type="hidden" name="m" value="{{$m}}" />
          <input type="hidden" name="dosql" value="do_tarif_aed" />
          <input type="hidden" name="reloadAlltarifs" value="1" />
          {{if $mode == "CMediusers"}}
            <input type="hidden" name="praticien_id" value="{{$prat->_id}}" />
          {{else}}
            <input type="hidden" name="function_id" value="{{$prat->function_id}}" />
          {{/if}}
          <button class="reboot me-tertiary" type="submit">{{tr}}CTarif._update_montants.all{{/tr}}</button>
        </form>
      {{/if}}
      {{if $mode == "CMediusers" && $prat->_id}}
        {{$prat}}
      {{elseif $mode == "CFunctions" && $prat->_ref_function->_id}}
        {{$prat->_ref_function}}
      {{elseif $mode == "CGroups" && $prat->_ref_function->_ref_group->_id}}
        {{$prat->_ref_function->_ref_group}}
      {{else}}
        {{tr}}CTarif.choose_contexte{{/tr}}
      {{/if}}
    </th>
  </tr>
  {{if $prat->_id}}
    <tr>
      {{if $mode == 'CMediusers' || $mode == 'CFunctions'}}
        <th class="narrow">
          <input type="checkbox" name="move_all_tarifs-{{$mode}}" onclick="Tarif.toggleTarifs(this, '{{$mode}}');">
        </th>
      {{/if}}
      <th>{{mb_title class=CTarif field=description}}</th>
      {{if $conf.ref_pays == "1"}}
        <th class="narrow">{{mb_title class=CTarif field=_has_mto}}</th>
        <th class="narrow">{{mb_title class=CTarif field=secteur1}}</th>
        <th class="narrow">{{mb_title class=CTarif field=secteur2}}</th>
        <th class="narrow">{{mb_title class=CTarif field=secteur3}}</th>
        <th class="narrow">{{mb_title class=CTarif field=_du_tva}}</th>
      {{/if}}
      <th class="narrow">{{mb_title class=CTarif field=_somme}}</th>
    </tr>

    {{foreach from=$tarifs item=_tarif}}
      <tr {{if $_tarif->_id == $tarif->_id}} class="selected"{{/if}}>
        {{if $mode == 'CMediusers' || $mode == 'CFunctions'}}
          <td class="narrow">
            <input type="checkbox" name="move_tarifs-{{$mode}}" data-tarif_id="{{$_tarif->_id}}"/>
          </td>
        {{/if}}
        <td {{if $_tarif->_precode_ready}} class="checked"{{/if}}>
          <a href="#"  onclick="Tarif.edit('{{$_tarif->_id}}', '{{$prat->_id}}')">
            {{mb_value object=$_tarif field=description}}
          </a>
        </td>
        {{if $conf.ref_pays == "1"}}
          <td>{{mb_value object=$_tarif field=_has_mto}}</td>
          <td {{if !$_tarif->_secteur1_uptodate}} class="warned"{{/if}} style="text-align: right">
            {{mb_value object=$_tarif field=secteur1}}
          </td>
          <td style="text-align: right">{{mb_value object=$_tarif field=secteur2}}</td>
          <td style="text-align: right">{{mb_value object=$_tarif field=secteur3}}</td>
          <td style="text-align: right">{{mb_value object=$_tarif field=_du_tva}}</td>
        {{/if}}
        <td style="text-align: right"><strong>{{mb_value object=$_tarif field=_somme}}</strong></td>
      </tr>
      {{foreachelse}}
      <tr>
        <td class="empty" colspan="8">{{tr}}CTarif.none{{/tr}}</td>
      </tr>
    {{/foreach}}
  {{/if}}
</table>
