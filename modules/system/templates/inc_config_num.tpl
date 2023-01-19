{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=field_name value=$m}}
{{mb_default var=size value=4}}

{{if @$class}}
  {{assign var=field_name value="$field_name[$class]"}}

  {{if @$var}}
    {{assign var=field_name value="$field_name[$var]"}}
  {{/if}}
{{/if}}

{{if @$form}}
<script>
Main.add(function(){
  getForm("{{$form}}")["{{$field_name}}"].addSpinner();
});
</script>
{{/if}}

{{mb_include module=system template=inc_config_str size=$size}}