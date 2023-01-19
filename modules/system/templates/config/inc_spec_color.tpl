{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $is_last}}
  <script>
    Main.add(function() {
      var e = getForm("edit-configuration-{{$uid}}").elements["_config_{{$_feature}}"];

      e.colorPicker({
        {{if $is_inherited}}
          disabled: true
        {{/if}}
      });

      e.up('.custom-value').observe("conf:enable", function(){
        jQuery(e).spectrum("enable");
      });
      e.up('.custom-value').observe("conf:disable", function(){
        jQuery(e).spectrum("disable");
      });
    });
  </script>

  <input type="hidden" name="_config_{{$_feature}}" value="{{$value}}"
         onchange="var elt = this.form.elements['c[{{$_feature}}]']; elt.disabled = null; $V(elt, this.value.substr(1));" />
{{else}}
  <span style="display: inline-block; vertical-align: top; padding: 0; margin: 0; width: 16px; height: 16px; background-color: #{{$value}};"></span>
{{/if}}