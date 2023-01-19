{{*
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<select name="lang" style="float:right;" onchange="this.form.submit()">
  <option value="{{$cim10|const:'LANG_FR'}}" {{if $lang == $cim10|const:'LANG_FR'}}selected="selected"{{/if}}>
    {{tr}}CCodeCIM10.lang.LANG_FR{{/tr}}
  </option>
  <option value="{{$cim10|const:'LANG_EN'}}" {{if $lang == $cim10|const:'LANG_EN'}}selected="selected"{{/if}}>
    {{tr}}CCodeCIM10.lang.LANG_EN{{/tr}}
  </option>
  <option value="{{$cim10|const:'LANG_DE'}}" {{if $lang == $cim10|const:'LANG_DE'}}selected="selected"{{/if}}>
    {{tr}}CCodeCIM10.lang.LANG_DE{{/tr}}
  </option>
</select>