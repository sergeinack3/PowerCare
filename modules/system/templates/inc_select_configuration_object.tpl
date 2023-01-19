{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{me_form_field label=common-Context}}
  <select id="object_guid-selector-{{$uid}}" onchange="editObjectConfig($V(this), '{{$uid}}')">
    {{if $object_tree|@count > 1}}
      <option value="" selected="selected" disabled="disabled"> &mdash; </option>
    {{/if}}
    {{if $app->_ref_user->isAdmin()}}
      <option value="global"> Global </option>
    {{/if}}
    {{mb_include module=system template=inc_select_options_configuration items=$object_tree level=0}}
  </select>
{{/me_form_field}}

<script type="text/javascript">
Main.add(function(){
  var guid = $V($("object_guid-selector-{{$uid}}"));
  if (guid) {
    editObjectConfig(guid, '{{$uid}}');
  }
});
</script>