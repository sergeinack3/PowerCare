{{*
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=auto_refresh_frequency value='dPadmissions automatic_reload auto_refresh_frequency_identito'|gconf}}

<script>
  IdentitoVigilance.guesses = {{$guesses|@json}};
  Main.add(function() {
    var tab = $$("a[href=#identito_vigilance]")[0];
    if (tab) {
      tab.down("small").update("({{$mergeables_count}})");
      tab.setClassName("wrong", "{{$mergeables_count}}" != 0);
    }
  });
</script>

<form name="Merger" action="?" method="get">
  Voir les séjour :
  <input name="see_mergeable" type="checkbox" {{if $see_mergeable}}checked{{/if}} onclick="IdentitoVigilance.start(0, '{{$auto_refresh_frequency}}');" />
  <label for="see_mergeable">Seulement les suspects</label>
  <input name="see_yesterday" type="checkbox" {{if $see_yesterday}}checked{{/if}} onclick="IdentitoVigilance.start(0, '{{$auto_refresh_frequency}}');" />
  <label for="see_yesterday">Egalement ceux de la veille</label>
  <input name="see_cancelled" type="checkbox" {{if $see_cancelled}}checked{{/if}} onclick="IdentitoVigilance.start(0, '{{$auto_refresh_frequency}}');" />
  <label for="see_cancelled">Egalement les annulées</label>
  
  <table class="tbl">
    <tr>
      <th colspan="6" class="title">{{tr}}CPatient{{/tr}}</th>
      <th colspan="3" class="title">{{tr}}CSejour{{/tr}}</th>
      {{if ($module == "dPurgences")}}
        <th rowspan="2" class="title">RPU</th>
      {{/if}}
      <th rowspan="2" class="title">{{tr}}COperation{{/tr}}</th>
    </tr>
  
    <tr>
      <th colspan="2">{{mb_label class=CPatient field=nom}}</th>
      <th>{{mb_title class=CPatient field=_IPP}}</th>
      <th>{{mb_title class=CPatient field=naissance}}</th>
      <th>{{mb_title class=CPatient field=_age}}</th>
      <th>{{mb_label class=CPatient field=adresse}}</th>
      <th colspan="2">{{mb_label class=CSejour field=entree}}</th>
      <th>{{mb_title class=CSejour field=_NDA}}</th>
    </tr>
  
    {{foreach from=$patients item=_patient}}
      {{assign var=patient_id value=$_patient->_id}}
      {{assign var=phonings value=$guesses.$patient_id.phonings}}
      {{assign var=siblings value=$guesses.$patient_id.siblings}}
      {{assign var=mergeable value=$guesses.$patient_id.mergeable}}
      
      {{if $mergeable || !$see_mergeable}}
      <tbody class="hoverable CPatient {{if $mergeable}}mergeable{{/if}}">
        {{foreach from=$_patient->_ref_sejours item=_sejour name=sejour}}
          {{assign var=count_sejour value=$_patient->_ref_sejours|@count}}
          <tr>
      
          {{if $smarty.foreach.sejour.first}} 
            <td rowspan="{{$count_sejour}}" class="narrow">
              <input name="{{$_patient->_class}}-first" type="checkbox" value="{{$_patient->_id}}" onclick="IdentitoVigilance.highlite(this);" />
              <input name="{{$_patient->_class}}-second" type="radio" value="{{$_patient->_id}}" style="visibility: hidden;" onclick="IdentitoVigilance.merge(this);"/>
            </td>
            <td rowspan="{{$count_sejour}}">
              <div class="text" id="{{$_patient->_guid}}">
                <big onmouseover="ObjectTooltip.createEx(this, '{{$_patient->_guid}}')">{{$_patient}}</big>
              </div>
            </td>
            <td rowspan="{{$count_sejour}}" style="text-align: center">
              <strong>{{mb_include module=patients template=inc_vw_ipp ipp=$_patient->_IPP}}</strong>
            </td>
            <td rowspan="{{$count_sejour}}">
              <big>{{mb_value object=$_patient field=naissance}}</big> 
            </td>
            <td rowspan="{{$count_sejour}}">
              {{mb_value object=$_patient field=_age}}
            </td>
            <td rowspan="{{$count_sejour}}">
              {{mb_value object=$_patient field=adresse}}
              {{mb_value object=$_patient field=cp}}
              {{mb_value object=$_patient field=ville}}
            </td>
          {{/if}}
             
          <td class="narrow">
            {{if $count_sejour > 1 && $allow_merge}}
              <input name="{{$_sejour->_class}}-first" type="checkbox" value="{{$_sejour->_id}}" onclick="IdentitoVigilance.highlite(this);" />
              <input name="{{$_sejour->_class}}-second" type="radio" value="{{$_sejour->_id}}" style="visibility: hidden;" onclick="IdentitoVigilance.merge(this);"/>
            {{/if}}
          </td>
          <td id="{{$_sejour->_guid}}" >
            <big onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_guid}}')">
              {{mb_value object=$_sejour field=entree date=$date}}
            </big>
          </td>
          <td {{if ($_sejour->annule == 1)}}class="cancelled"{{/if}}>
            {{if !$_sejour->_NDA}} 
             <div class="warning">
                {{tr}}None{{/tr}}
             </div>
            {{else}}
            <strong>{{mb_include module=planningOp template=inc_vw_numdos nda_obj=$_sejour}}</strong>
            {{/if}}
          </td>
          
          {{if ($module == "dPurgences")}}
          <td {{if ($_sejour->annule == 1)}}class="cancelled"{{/if}}>
            {{foreach from=$_sejour->_back.rpu key=rpu_id item=_rpu}}
            <div>
              {{if count($_sejour->_back.rpu) > 1}}
              <input name="{{$_rpu->_class}}-first" type="checkbox" value="{{$_rpu->_id}}" onclick="IdentitoVigilance.highlite(this);" />
              <input name="{{$_rpu->_class}}-second" type="radio" value="{{$_rpu->_id}}" style="visibility: hidden;" onclick="IdentitoVigilance.merge(this);"/>
              {{/if}}
              <span onmouseover="ObjectTooltip.createEx(this, '{{$_rpu->_guid}}')">
                {{tr}}CRPU-msg-create{{/tr}}
              </span>
            </div>
            {{foreachelse}}
            <div class="warning">
              {{tr}}CRPU-msg-absent{{/tr}}
            </div>
            {{/foreach}}
          </td>
          {{/if}}
          
          {{assign var=count_operation value=$_sejour->_ref_operations|@count}}
          <td class="text {{if ($_sejour->annule == 1)}}cancelled{{/if}}">
            {{foreach from=$_sejour->_ref_operations item=_operation}}
            <div>
              {{if count($_sejour->_ref_operations) > 1}}
                <input name="{{$_operation->_class}}-first" type="checkbox" value="{{$_operation->_id}}" onclick="IdentitoVigilance.highlite(this);" />
                <input name="{{$_operation->_class}}-second" type="radio" value="{{$_operation->_id}}" style="visibility: hidden;" onclick="IdentitoVigilance.merge(this);"/>
              {{/if}}
              <span onmouseover="ObjectTooltip.createEx(this, '{{$_operation->_guid}}')">
                {{if !$_operation->plageop_id}}[HP]{{/if}} {{$_operation->_datetime|date_format:$conf.date}}
              </span>
            </div>
            {{/foreach}}
          </td>
        {{/foreach}}
      </tbody>
      {{/if}}
    {{foreachelse}}
      <tr> <td colspan="12" class="empty">{{tr}}None{{/tr}}</td> </tr>
    {{/foreach}}
  </table>
</form>
