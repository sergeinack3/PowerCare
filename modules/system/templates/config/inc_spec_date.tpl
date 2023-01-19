{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $is_last}}
  <script>
    Main.add(function() {
      var form = getForm("edit-configuration-{{$uid}}");
      Calendar.regField(form["c[{{$_feature}}]"][1], null, {datePicker: true, timePicker : false});
      {{if $is_inherited}}
        form["c[{{$_feature}}]_da"].disable();
      {{/if}}
    });
  </script>
  <input type="hidden" class="date" name="c[{{$_feature}}]" value="{{$value}}"{{if $is_inherited}} disabled{{/if}}>
{{else}}
  {{$value|date_format:$conf.date}}
{{/if}}