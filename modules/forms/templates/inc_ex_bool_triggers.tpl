{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main tbl">
  <col class="narrow" />
  
  <tr>
    <th>Valeur</th>
    <th>Formulaire à déclencher</th>
    <th class="narrow">
      Coché par<br />défaut
    </th>
  </tr>
  
  <tbody>
  {{foreach from=","|explode:"1,0" item=_value}}
    <tr>
      <td>{{tr}}bool.{{$_value}}{{/tr}}</td>
      
      {{mb_include module=forms template=inc_ex_field_triggers value=$_value}}
      
      <td style="text-align: center;">
        <label style="display: block;">
          <input type="radio" name="default" value="{{$_value}}" {{if $spec->default == $_value}}checked="checked"{{/if}} />
        </label>
      </td>
    </tr>
  {{/foreach}}
  <tr>
    <td colspan="2">{{tr}}Undefined{{/tr}}</td>
    <td style="text-align: center;">
      <label style="display: block;">
        <input type="radio" name="default" value="" {{if $spec->default == ""}}checked="checked"{{/if}} />
      </label>
    </td>
  </tr>
  </tbody>
</table>

