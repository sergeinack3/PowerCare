{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}


{{mb_script module=system script=object_selector ajax=true}}
{{mb_script module=system script=object_navigation ajax=true}}
{{unique_id var=form_uid}}

<script>
  Main.add(function () {
    Control.Tabs.create("tabs_modale_{{$form_uid}}", true);
  });
</script>

<ul id="tabs_modale_{{$form_uid}}" class="control_tabs">
  <li>
    <a href="#tab_plainfields_{{$form_uid}}">{{tr}}mod-system-object-nav-plain-fields{{/tr}} ({{$counts.plain}})</a>
  </li>
  <li>
    <a href="#tab_formfields_{{$form_uid}}">{{tr}}mod-system-object-nav-form-fields{{/tr}} ({{$counts.form}})</a>
  </li>
  {{if $counts.total > 0}}
    <li>
      <a href="#tab_collections_{{$form_uid}}">{{tr}}mod-system-object-nav-collections{{/tr}} ({{$counts.total}})</a>
    </li>
  {{/if}}
</ul>

<div id="tab_plainfields_{{$form_uid}}" style="display: none">
  {{mb_include module=system template=inc_tab_plainfields}}
</div>
<div id="tab_formfields_{{$form_uid}}" style="display: none">
  {{mb_include module=system template=inc_tab_formfields}}
</div>
{{if $counts.total > 0}}
  <div id="tab_collections_{{$form_uid}}" style="display: none">
    {{mb_include module=system template=inc_tab_collections}}
  </div>
{{/if}}