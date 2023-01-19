{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=maternite script=grossesse ajax=true}}

{{mb_default var=grossesse value=null}}

{{if $object->_class == "CPatient"}}
  {{assign var=grossesse value=$object->_ref_last_grossesse}}
{{elseif ($object->_class == "CSejour") && $object->type == "urg" && !$object->grossesse_id}}
    {{assign var=patient   value=$object->_ref_patient}}
{{else}}
  {{assign var=grossesse value=$object->_ref_grossesse}}
{{/if}}
{{mb_default var=submit value=0}}
{{mb_default var=large_icon value=0}}
{{mb_default var=modify_grossesse value=1}}
{{mb_default var=show_empty value=1}}
{{mb_default var=is_edit_consultation value=0}}

{{if !$grossesse}}
  {{mb_return}}
{{/if}}

<script>
  Main.add(function () {
    Grossesse.parturiente_id = '{{$patient->_id}}';
    Grossesse.submit = '{{$submit}}';
    Grossesse.large_icon = '{{$large_icon}}';
    Grossesse.modify_grossesse = '{{$modify_grossesse}}';
    Grossesse.formTo = $('grossesse_id').form;
    Grossesse.duree_sejour = '{{"maternite general duree_sejour"|gconf}}';
    
    {{if $submit}}
    Grossesse.submit = {{$submit}};
    {{/if}}

    {{if $is_edit_consultation}}
      Grossesse.is_edit_consultation = {{$is_edit_consultation}};
    {{/if}}
  });
</script>

<input type="hidden" name="_grossesse_id" value="{{$grossesse->_id}}" />
<input type="hidden" name="grossesse_id" value="{{$grossesse->_id}}" id="grossesse_id"
       onchange="$V(this.form._grossesse_id, this.value);" />
<input type="hidden" name="_patient_sexe" value="{{if $patient->_id}}{{$patient->sexe}}{{/if}}"
       onchange="Grossesse.toggleGrossesse(this.value, this.form)" />
<input type="hidden" name="_large_icon" value="{{$large_icon}}" />

<span id="view_grossesse" style="font-size: 0.8em;" class="me-icon-grossesse">
  {{if $grossesse->_id}}
    <img onmouseover="ObjectTooltip.createEx(this, '{{$grossesse->_guid}}')" {{if !$grossesse->active}}class="opacity-50"{{/if}}
      {{if $modify_grossesse}}
        onclick="Grossesse.tdbGrossesse('{{$grossesse->_id}}', '{{$patient->_id}}');"
      {{/if}}
           src="style/mediboard_ext/images/icons/grossesse.png"
         style="{{if $large_icon}}width: 30px;{{/if}} background-color: rgb(255, 215, 247);" />

  {{elseif $modify_grossesse && $show_empty && (!$patient->_id || $patient->sexe == "f")}}
    <div class="empty" style="display:inline">{{tr}}CGrossesse.none_linked{{/tr}}</div>
  {{/if}}
</span>

{{if $modify_grossesse && (!$patient->_id || $patient->sexe == "f")}}
  <button id="button_grossesse" type="button" class="edit notext button_grossesse me-tertiary"
          {{if !$patient->_id || $patient->_annees < 12}}disabled{{/if}}
          onclick="Grossesse.viewGrossesses('{{$patient->_id}}', '{{$object->_guid}}', this.form)"></button>
{{/if}}
