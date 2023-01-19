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
      form["c[{{$_feature}}]"][1].addSpinner({{$_prop|@json}});
    });
  </script>
  <input type="text" class="{{$_prop.string}}" name="c[{{$_feature}}]" value="{{$value}}" {{if $is_inherited}} disabled {{/if}} size="4" />
{{else}}
  {{$value}}
{{/if}}