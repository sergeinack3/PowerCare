{{*
 * @package Mediboard\Sip
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=mod value=dPpatients}}
{{assign var=class value=CPatient}}
{{assign var="var" value="tag_ipp"}}
<tr>
  <th>
    <label for="{{$mod}}[{{$class}}][{{$var}}]" title="{{tr}}config-{{$mod}}-{{$class}}-{{$var}}-desc{{/tr}}">
      {{tr}}config-{{$mod}}-{{$class}}-{{$var}}{{/tr}}
    </label>  
  </th>
  <td>
    <input class="str" name="{{$mod}}[{{$class}}][{{$var}}]" value="{{$conf.$mod.$class.$var}}" />
    {{if $conf.$mod.$class.$var != $pat}}
    <div class="small-warning">
      Le tag IPP pour l'utilisation de ce module dans cet établissement devrait être : '{{$pat}}' <br />
      <button type="submit" class="change" onclick="this.form.elements['{{$mod}}[{{$class}}][{{$var}}]'].value = '{{$pat}}'">
        {{tr}}Restore{{/tr}} le bon tag
      </button>
    </div>
    {{else}}  
    <div class="small-success">
      Le tag IPP est compatible avec l'utilisation de ce module dans cet établissement.
    </div>
    {{/if}}
  </td>
</tr>

{{assign var=mod value=dPplanningOp}}
{{assign var=class value=CSejour}}
{{assign var="var" value="tag_dossier"}}
<tr>
  <th>
    <label for="{{$mod}}[{{$class}}][{{$var}}]" title="{{tr}}config-{{$mod}}-{{$class}}-{{$var}}-desc{{/tr}}">
      {{tr}}config-{{$mod}}-{{$class}}-{{$var}}{{/tr}}
    </label>  
  </th>
  <td>
    <input class="str" name="{{$mod}}[{{$class}}][{{$var}}]" value="{{$conf.$mod.$class.$var}}" />
    {{if $conf.$mod.$class.$var != $sej}}
    <div class="small-warning">
      Le tag 'Numéro de dossier' pour l'utilisation de ce module dans cet établissement devrait être : '{{$sej}}'
      <br />
      <button type="submit" class="change" onclick="this.form.elements['{{$mod}}[{{$class}}][{{$var}}]'].value = '{{$sej}}'">
        {{tr}}Restore{{/tr}} le bon tag
      </button>
    </div>
    {{else}}  
    <div class="small-success">
      Le tag 'Numéro de dossier' est compatible avec l'utilisation de ce module dans cet établissement.
    </div>
    {{/if}}
  </td>
</tr>