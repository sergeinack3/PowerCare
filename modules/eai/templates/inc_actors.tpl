{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<style>
  .main {
    margin-top: 0px !important;
    margin-bottom: 0px !important;
  }
</style>

{{mb_default var=count_actors_actif value="-"}}
{{mb_default var=count_actors value="-"}}

<script>
  Main.add(function () {
    var link = $('tabs-actors').select("a[href=#{{$parent_class}}s]")[0];
    link.update("{{tr}}{{$parent_class}}-court{{/tr}} (<span title='Total actifs'>{{$count_actors_actif}}</span> / <span title='Total'>{{$count_actors}}</span>)");
    {{if $actors|@count == '0'}}
      link.addClassName('empty');
    {{else}}
      link.removeClassName('empty');
    {{/if}}
  });

  changeToggle = function(element) {
    var class_name = element.getAttribute('class');
    if (class_name.match(/toggle-off/)) {
      element.setAttribute('class', 'fas fa-toggle-on');
    }
    else {
      element.setAttribute('class', 'fas fa-toggle-off');
      }
  }
</script>

<div style="text-align: right; margin-bottom: 10px;">
  <div class="me-eai-acteurs-help" style="float: left;"
       onmouseover="ObjectTooltip.createDOM(this, 'history-legend', {duration: 0});" >
  </div>
  {{mb_include module=eai template=inc_vw_history_legend}}

  <button type="button" class="fas fa-toggle-off" style="float: left; margin-left: 5px;"
    onclick="changeToggle(this);" id="toggleActor{{$parent_class}}">
    <span class="me-color-black-high-emphasis" style="color: #000000">{{tr}}CInteropActor-msg-Show all actor|pl{{/tr}}</span>
  </button>

  <button type="submit" onclick="InteropActor.enableActors('prod', '{{$parent_class}}', 1);" class="fa fa-power-off me-eai-power-on"
          style="color: forestgreen !important;" title="{{tr}}CInteropActor-action-Enable prod{{/tr}}">
    <span class="me-color-black-high-emphasis" style="color: #000000">{{tr}}CInteropActor-role.prod{{/tr}}</span>
  </button>
  <button type="submit" onclick="InteropActor.enableActors('prod', '{{$parent_class}}', 0);" class="fa fa-power-off me-eai-power-off"
          style="color: firebrick !important;" title="{{tr}}CInteropActor-action-Disable prod{{/tr}}">
    <span class="me-color-black-high-emphasis" style="color: #000000;">{{tr}}CInteropActor-role.prod{{/tr}}</span>
  </button>

  <button type="submit" onclick="InteropActor.enableActors('qualif', '{{$parent_class}}', 1);" class="fa fa-power-off me-eai-power-on"
          style="color: forestgreen !important;" title="{{tr}}CInteropActor-action-Enable qualif{{/tr}}">
    <span class="me-color-black-high-emphasis" style="color: #000000;">{{tr}}CInteropActor-role.qualif{{/tr}}</span>
  </button>
  <button type="submit" onclick="InteropActor.enableActors('qualif', '{{$parent_class}}', 0);" class="fa fa-power-off me-eai-power-off"
          style="color: firebrick !important;" title="{{tr}}CInteropActor-action-Disable qualif{{/tr}}">
    <span class="me-color-black-high-emphasis" style="color: #000000;">{{tr}}CInteropActor-role.qualif{{/tr}}</span>
  </button>
</div>

<table class="main tbl">
  <tr>
    <th class="narrow">
      <a class="button add notext" style="float:left" onclick="InteropActor.modeEasy('receiver');"
         title="{{tr}}CInteropReceiver-msg-Mode easy{{/tr}}"></a>
    </th>
    <th>{{mb_label object=$actor field="nom"}}</th>
    <th></th>
    <th>{{mb_label object=$actor field="group_id"}}</th>
    <th>{{mb_label object=$actor field="actif"}}</th>
    {{if $actor->_class == "CInteropReceiver"}}
      <th></th>
    {{/if}}
    <th>{{tr}}CInteropActor-_ref_exchanges_sources{{/tr}}</th>
    <th></th>
  </tr>
  {{foreach from=$actors key=type_actor item=_actors}}
    <tr class="alternate">
      <th class="section" colspan="8" style="cursor:pointer">

        <a style="float: left" class="button add notext me-secondary" href="#"
            onclick="InteropActor.editActor(null, '{{$type_actor}}', '{{$parent_class}}');"
            title="Créer acteurs {{tr}}{{$type_actor}}{{/tr}}">
          {{tr}}{{$type_actor}}-title-create{{/tr}}
        </a>
        <div {{if $_actors.total > 0}}
          onclick="InteropActor.refreshListActors('{{$type_actor}}', '{{$parent_class}}', 'chevron_{{$type_actor}}')"{{/if}}
          style="display: inline;">
          {{tr}}{{$type_actor}}{{/tr}} <small>({{$_actors.total_actif}}/{{$_actors.total}})</small>
        </div>

          <a style="float: right; font-size: 17px" class="fas fa-chevron-circle-down  notext me-tertiary" href="#" id="chevron_{{$type_actor}}"
             onclick="InteropActor.refreshListActors('{{$type_actor}}', '{{$parent_class}}', 'chevron_{{$type_actor}}')"
             title="{{tr}}CInteropActor-msg-Show actor|pl{{/tr}}">
          </a>

      </th>
    </tr>

    <tbody class="main tbl" style="display: none" id="list_actors_{{$type_actor}}"></tbody>
  {{/foreach}}
</table>
