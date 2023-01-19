{{*
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  Main.add(function () {
    var item = $("list-{{$pack->_guid}}");
    if (item) {
      item.addUniqueClassName("selected", ".list-container");
    }

    SupervisionGraph.listGraphToPack({{$pack->_id}});
    SupervisionGraph.addListenerContexts();
  });
</script>

<form name="edit-supervision-graph-pack" method="post" action="?m=monitoringPatient" onsubmit="return onSubmitFormAjax(this)">
  <input type="hidden" name="m" value="monitoringPatient" />
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="@class" value="CSupervisionGraphPack" />
  <input type="hidden" name="owner_class" value="CGroups" />
  <input type="hidden" name="owner_id" value="{{$g}}" />
  <input type="hidden" name="callback" value="SupervisionGraph.callbackEditPack" />
  {{mb_key object=$pack}}
  {{mb_field object=$pack field=timing_fields hidden=true}}

  <table class="main form">
    {{mb_include module=system template=inc_form_table_header object=$pack colspan=2}}

    <tr>
      <th>{{mb_label object=$pack field=title}}</th>
      <td>{{mb_field object=$pack field=title}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$pack field=disabled}}</th>
      <td>{{mb_field object=$pack field=disabled typeEnum=checkbox}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$pack field=use_contexts}}</th>
      <td>
        {{mb_field object=$pack field=use_contexts}}
        <div class="info" style="display: inline-block; margin-left: 2em;">Si aucun coché, le pack sera toujours disponible.</div>
      </td>
    </tr>

    <tr id="show_main_pack" {{if !$pack->main_pack}}style="display: none;"{{/if}}>
      <th>{{mb_label object=$pack field=main_pack}}</th>
      <td>{{mb_field object=$pack field=main_pack typeEnum=checkbox}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$pack field=planif_display_mode}}</th>
      <td>{{mb_field object=$pack field=planif_display_mode}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$pack field=timing_fields}}</th>
      <td>
        <button onclick="SupervisionGraph.editPackTimings()" type="button" class="edit">{{tr}}Edit{{/tr}}</button>
        <div id="edit-timing-fields" style="display: none;">
          <div class="small-info">
            Tous les champs listés ici ne sont pas forcément disponibles au bloc, cela dépend de votre paramétrage.
          </div>
          {{foreach from='Ox\Mediboard\MonitoringPatient\CSupervisionGraphPack'|static:_operation_timing_fields item=_field}}
            {{assign var=_fields value=$pack->_timing_fields}}
            {{assign var=_is_set value=false}}
            {{if array_key_exists($_field,$_fields)}}
              {{assign var=_is_set value=true}}
            {{/if}}
            <div class="timing-field">
              <input type="hidden" class="color" data-timing="{{$_field}}"
                     value="{{if $_is_set}}{{$_fields.$_field}}{{/if}}" />
              <span title="{{tr}}COperation-{{$_field}}-desc{{/tr}}">{{tr}}COperation-{{$_field}}{{/tr}}</span>
            </div>
          {{/foreach}}

          <div style="text-align: center">
            <button class="tick" type="button" onclick="Control.Modal.close()">{{tr}}Validate{{/tr}}</button>
          </div>
        </div>
      </td>
    </tr>

    <tr>
      <th>{{mb_label object=$pack field=anesthesia_type}}</th>
      <td>
        <select name="anesthesia_type" style="width: 15em;">
          <option value="">&mdash; Anesthésie</option>
          {{foreach from=$anesthesia_types item=type}}
            {{if $type->actif || $pack->anesthesia_type == $type->type_anesth_id}}
              <option
                value="{{$type->type_anesth_id}}"{{if $pack->anesthesia_type == $type->type_anesth_id}} selected="selected"{{/if}}>
                {{$type->name}}{{if !$type->actif && $pack->anesthesia_type == $type->type_anesth_id}} (Obsolète){{/if}}
              </option>
            {{/if}}
          {{/foreach}}
        </select>
      </td>
    </tr>

    <tr>
      <td></td>
      <td>
        <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>

        {{if $pack->_id}}
          <button type="button" class="trash"
                  onclick="confirmDeletion(
                    this.form,
                    {typeName:'', objName:'{{$pack->_view|smarty:nodefaults|JSAttribute}}'},
                    SupervisionGraph.callbackEditPack
                    )">
            {{tr}}Delete{{/tr}}
          </button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>

<div id="graph-to-pack-list"></div>
