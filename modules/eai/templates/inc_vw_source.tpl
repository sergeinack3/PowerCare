{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<tr id="line_{{$_source->_guid}}" {{if !$_source->active}}class="opacity-30"{{/if}}>
  <td class="narrow">
    <button title="Modifier {{$_source->_view}}" class="edit notext button me-tertiary"
            onclick="Source.edit('{{$_source->_guid}}')"
            style="float: left">
      {{tr}}edit{{/tr}} {{$_source->name}}
    </button>
  </td>
  <td class="narrow">
    <button class="lookup button me-tertiary notext" type="button" onclick="Source.showTrace('{{$_source->_guid}}')">
    </button>
  </td>
  <td class="narrow">
    {{if $_source->role != $conf.instance_role}}
      <i class="fas fa-exclamation-triangle" style="color: goldenrod;"
         title="{{tr var1=$_source->role var2=$conf.instance_role}}CExchangeSource-msg-Source incompatible %s with the instance role %s{{/tr}}"></i>
    {{/if}}

    {{if $_source->role == "prod"}}
      <strong style="color: red" title="{{tr}}CExchangeSource.role.prod{{/tr}}">
        {{tr}}CExchangeSource.role.prod-court{{/tr}}
      </strong>
    {{else}}
      <span style="color: green" title="{{tr}}CExchangeSource.role.qualif{{/tr}}">
        {{tr}}CExchangeSource.role.qualif-court{{/tr}}
      </span>
    {{/if}}
  </td>
  <td class="narrow">
    {{assign var=source_guid value=$_source->_guid}}

    {{mb_include module="system" template="inc_form_button_active" field_name="active" object=$_source
    onComplete="Source.refresh('$source_guid')"}}
  </td>
  <td>
    <form name="edit-loggable-{{$_source->_guid}}" method="post"
        onsubmit="return onSubmitFormAjax(this, function () { Source.refresh('{{$source_guid}}') });">
      {{mb_key object=$_source}}
      {{mb_class object=$_source}}
      {{mb_field object=$_source field="loggable" hidden=true}}

      <a href="#1" onclick="toggleUpdate(this.up('form').elements.loggable);" style="display: inline-block; vertical-align: middle;">
          {{if $_source->loggable}}
              <i class="fa fa-archive" style="color: #449944; font-size: large;"></i>
          {{else}}
              <i class="fa fa-archive" style="color: #CCC; font-size: large;"></i>
          {{/if}}
      </a>
    </form>
  </td>
  <td class="text compact narrow">
    {{if $_source->libelle}}
      {{$_source->libelle}}
      <br />
      <em style="font-size: 0.9em"> {{$_source->name}}</em>
    {{else}}
      {{$_source->name}}
    {{/if}}

    {{if $_source|instanceof:'Ox\Mediboard\System\CSourcePOP'}}
      <br />
      <br />
      <strong>{{mb_label object=$_source field="object_id"}} :</strong>
      {{if $_source->_ref_mediuser}}
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_source->_ref_mediuser}}
      {{else}}
        <input type="text" readonly="readonly" name="_object_view"
               value="{{$_source->_ref_metaobject->_view}}" size="50" />
      {{/if}}
    {{/if}}
  </td>
  <td class="narrow button">
    {{unique_id var=uid}}
    <button type="button" class="fas fa-network-wired notext me-tertiary"
            {{*            onclick="InteropActor.testAccessibilitySources('{{  $_actor->_guid}}')"*}}
            onclick="ExchangeSource.SourceReachable(this.parentNode);"
            title="{{tr}}CInteropActor-msg-Test accessibility sources{{/tr}}">
    </button>
    <i class="fa fa-circle" style="color:grey" id="{{$uid}}" data-id="{{$_source->_id}}"
       data-guid="{{$_source->_guid}}" guid="{{$_source->_guid}}" title="{{$_source->name}}"></i>
  </td>
  <td class="narrow"></td>
  <td></td>
</tr>
