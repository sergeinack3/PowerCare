{{*
 * @package Mediboard\Hprimxml
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editConfig-treatment" method="post" onsubmit="return onSubmitFormAjax(this);">
  {{mb_configure module=$m}}

  <table class="form">
    {{mb_include module=system template=inc_config_str var=functionPratImport}}

    {{mb_include module=system template=inc_config_str var=medecinIndetermine}}

    {{mb_include module=system template=inc_config_bool var=medecinActif}}

    {{assign var=user_types value='Ox\Mediboard\Admin\CUser'|static:types}}

    {{assign var=var  value="user_type"}}
    {{assign var=field  value="$m[$var]"}}
    {{assign var=value  value=$conf.$m.$var}}
    {{assign var=locale value=config-$m-$var}}

    <tr>
      <th>
        <label for="{{$field}}" title="{{tr}}{{$locale}}-desc{{/tr}}">
          {{tr}}{{$locale}}{{/tr}}
        </label>
      </th>

      <td>
        <select class="str" name="{{$field}}">
          {{foreach from=$user_types key=_key item=_value}}
            <option value="{{$_key}}" {{if $value == $_key}}selected{{/if}}>
              {{$_value}}
            </option>
          {{/foreach}}
        </select>
      </td>
    </tr>

    {{mb_include module=system template=inc_config_bool var=strictSejourMatch}}
    
    {{mb_include module=system template=inc_config_bool var=notifier_sortie_reelle}}
    
    {{mb_include module=system template=inc_config_bool var=notifier_entree_reelle}}
    
    {{mb_include module=system template=inc_config_bool var=trash_numdos_sejour_cancel}}
    
    {{mb_include module=system template=inc_config_enum var=code_transmitter_sender values=mb_id|finess}}
    
    {{mb_include module=system template=inc_config_enum var=code_receiver_sender values=dest|finess}}
    
    {{mb_include module=system template=inc_config_enum var=date_heure_acte values=operation|execution}}
   
    <tr>
      <td class="button" colspan="2">
        <button class="modify">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>