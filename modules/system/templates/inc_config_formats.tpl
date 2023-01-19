{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  ElementChecker.check.match = function() {
    this.assertMultipleArgs("match");
    if (!this.sValue.match(new RegExp(this.oProperties["match"]))) {
      this.addError("match", "Doit contenir seulement des espaces et des chiffres 9");
    }
  }.bind(ElementChecker);
</script>

<form name="editConfig-formats" method="post" onsubmit="return onSubmitFormAjax(this);">
  {{mb_configure module=$m}}

  <table class="form" style="table-layout: fixed;">
    {{mb_include module=system template=inc_config_date_format var=date}}
    
    {{mb_include module=system template=inc_config_date_format var=time}}
    
    {{mb_include module=system template=inc_config_date_format var=longdate}}
    
    {{mb_include module=system template=inc_config_date_format var=longtime}}
    
    {{mb_include module=system template=inc_config_date_format var=datetime}}

    {{assign var="var" value="timezone"}}
    <tr>
      <th>
        <label for="{{$var}}" title="{{tr}}config-{{$var}}-desc{{/tr}}">{{tr}}config-{{$var}}{{/tr}}</label>
      </th>
      <td>
        <select name="{{$var}}">
          {{foreach from=$timezones item=timezone_group key=title_group}}
            <optgroup label="{{$title_group}}">
            {{foreach from=$timezone_group item=title key=timezone}}
              <option value="{{$timezone}}" {{if $timezone==$conf.$var}}selected{{/if}}>
                {{$title}}
              </option>
            {{/foreach}}
            </optgroup>
          {{/foreach}}
        </select>
      </td>
    </tr>

    {{assign var="m" value="system"}}
    {{mb_include module=system template=inc_config_str var=phone_number_format maxlength=30 cssClass="str pattern|9[9\\s]+9"}}

    <tr>
      <th></th>
      <td>
        <div class="small-info">
          Le format ne doit contenir que des '9' et des espaces.<br />
          Il doit y avoir au maximum 10 fois '9'. Un '9' correspond à un numéro de 0 à 9.
        </div>
      </td>
    </tr>

    {{mb_include module=system template=inc_config_num var=phone_min_length}}
    {{mb_include module=system template=inc_config_num var=phone_area_code}}

    <tr>
      <td class="button" colspan="2">
        <button class="modify">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>