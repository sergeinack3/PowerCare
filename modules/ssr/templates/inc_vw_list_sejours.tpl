{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
Main.add(function() {
  var sejours_count = {{$sejours_count|json}};
  var link = $('tabs-replacement').down('a[href=#{{$type}}s]');
  link.down('small').update('('+sejours_count+')');
  link.setClassName('wrong', sejours_count != 0);
})
</script>

<table class="tbl">
  {{foreach from=$sejours key=plage_conge_id item=_sejours}}
    <tr>
      <th colspan="5" class="title text">
        {{assign var=plage_conge value=$plages_conge.$plage_conge_id}}
        {{assign var=user value=$plage_conge->_ref_user}}
        {{if !$plage_conge->_activite}}
          {{tr}}ssr-sejour_conge{{/tr}}
          {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$user}}
          <br />
          {{mb_value object=$plage_conge field=libelle}}
          &mdash;
          {{mb_include module=system template=inc_interval_date from=$plage_conge->date_debut to=$plage_conge->date_fin}}
        {{elseif $plage_conge->_activite == "deb"}}
          {{tr}}ssr-sejour_before_activity{{/tr}}
          {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$user}}
          <br />
          Le {{mb_value object=$user field=deb_activite}}
          <br/>
        {{elseif $plage_conge->_activite == "fin"}}
          {{tr}}ssr-sejour_after_activity{{/tr}}
          {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$user}}
          <br />
          Le {{mb_value object=$user field=fin_activite}}
          <br/>
        {{/if}}
        
        {{if $type == "kine"}} 
        <button class="right" style="float: right;" onclick="Planification.refreshReplacement(null,'{{$plage_conge_id}}','{{$type}}'); this.up('tr').addUniqueClassName('selected');" >
           {{tr}}All{{/tr}} 
        </button>
        {{/if}}
      </th>
    </tr>
    <tr>
      <th colspan="2">{{mb_title class=CSejour field=patient_id}}</th>
      <th>{{mb_title class=CSejour field=entree}}</th>
      <th>{{mb_title class=CSejour field=sortie}}</th>
      <th>
        {{if $type == "kine"}}
          {{mb_title class=CReplacement field=replacer_id}}
        {{else}}
          {{tr}}CEvenementSSR-court|pl{{/tr}}
        {{/if}}
      </th>
    </tr>
    
    {{foreach from=$_sejours item=_sejour}}
      {{assign var=sejour_id value=$_sejour->_id}}
      {{assign var=key value="$plage_conge_id-$sejour_id"}}
      {{assign var=replacement value=$_sejour->_ref_replacement}}
      
      <tr id="replacement-{{$type}}-{{$_sejour->_id}}">
        {{assign var=arrete value=""}}
        {{if $replacement->_id && $type == "kine"}} 
        {{assign var=arrete value="arretee"}}
        {{/if}}
        <td colspan="2" class="text {{$arrete}}">
          {{assign var=patient value=$_sejour->_ref_patient}}
          <big class="CPatient-view" style=""
            onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}')"
            onclick="Planification.refreshReplacement('{{$_sejour->_id}}','{{$plage_conge_id}}','{{$type}}'); this.up('tr').addUniqueClassName('selected');" >
            {{$patient}}
          </big> 
          <br />
          {{mb_include module=patients template=inc_vw_ipp ipp=$patient->_IPP}}
          {{$patient->_age}}
        </td>
        <td class="{{$arrete}}" style="text-align: center;">
          {{mb_value object=$_sejour field=entree format=$conf.date}}
          <div class="opacity-60" style="text-align: left;">{{$_sejour->_entree_relative}}j</div>
        </td>
        <td class="{{$arrete}}" style="text-align: center;">
          {{mb_value object=$_sejour field=sortie format=$conf.date}}
          <div class="opacity-60" style="text-align: right;">{{$_sejour->_sortie_relative}}j</div>
        </td>
        {{if $type == "kine"}}
        <td class="{{$arrete}}" style="text-align: left;">
          {{if $replacement->_id}} 
            <strong>{{mb_include module=mediusers template=inc_vw_mediuser mediuser=$replacement->_ref_replacer}}</strong>
          {{else}}
            {{foreach from=$replacement->_ref_guessed_replacers item=_guess}}
            <div>{{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_guess}}</div>
            {{/foreach}}
          {{/if}}
        </td>
        {{else}}
        <td style="text-align: center;">
          {{$count_evts.$key}}
        </td>
        {{/if}}
      </tr>
      
    {{/foreach}}
  {{foreachelse}}
  <tr>
    <th class="title">{{tr}}CSejour|pl{{/tr}}</th>
  </tr>
  <tr>
    <td colspan="10" class="empty">{{tr}}CSejour.none{{/tr}}</td>
  </tr>
  {{/foreach}}
</table>