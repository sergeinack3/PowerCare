{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $is_last}}
  {{unique_id var=input_id}}

  <script>
    Main.add(function() {
      var container = $('{{$input_id}}').up('div.textarea-container').up('div');

      if (container) {
        container.setStyle({width: '100%'});
      }
    });
  </script>

  <textarea id="{{$input_id}}" class="{{$_prop.string}}" name="c[{{$_feature}}]" rows="5" {{if $is_inherited}} disabled{{/if}}>{{$value}}</textarea>
{{else}}
  {{$value}}
{{/if}}
