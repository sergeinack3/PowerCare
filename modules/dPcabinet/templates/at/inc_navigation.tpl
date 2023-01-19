{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=previous value=false}}
{{mb_default var=next value=false}}

<button type="button" class="left" {{if $previous}}onclick="AccidentTravail.displayView('{{$previous}}');"{{else}}disabled{{/if}}>
  {{tr}}Previous{{/tr}}
</button>

<select name="at_navigation_{{$actual}}" onchange="AccidentTravail.displayView($V(this), this);">
  <option value="">&mdash; {{tr}}Goto{{/tr}}</option>
  <option value="at_context{{$uid}}">
    {{tr}}CAccidentTravail-title-context{{/tr}}
  </option>
  <option value="at_duration{{$uid}}">
    {{tr}}CAccidentTravail-title-duration{{/tr}}
  </option>
  <option value="at_patient_situation{{$uid}}">
    {{tr}}CAccidentTravail-title-patient_situation{{/tr}}
  </option>
  <option value="at_sorties{{$uid}}">
    {{tr}}CAccidentTravail-title-sorties{{/tr}}
  </option>
  <option value="at_summary{{$uid}}">
    {{tr}}CAccidentTravail-title-summary{{/tr}}
  </option>
</select>

<button type="button" class="right" {{if $next}}onclick="AccidentTravail.displayView('{{$next}}');"{{else}}disabled{{/if}}>
  {{tr}}Next{{/tr}}
</button>
