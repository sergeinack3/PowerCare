{{*
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=width value=false}}
{{mb_default var=empty_value value=false}}

<select name="{{$field}}" {{if $width}} style="width: {{$width}};"{{/if}}>
  {{if $empty_value}}
    <option value="">
      &mdash; {{tr}}Select{{/tr}}
    </option>
  {{/if}}
  {{foreach from=$specialities item=_speciality}}
    <option value="{{$_speciality->spec_cpam_id}}"{{if $selected == $_speciality->spec_cpam_id}} selected{{/if}}>
      {{$_speciality}}
    </option>
  {{/foreach}}
</select>