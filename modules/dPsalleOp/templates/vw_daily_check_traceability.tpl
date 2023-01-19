{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=system script=object_selector}}

<script>
  changePage = function(start) {
    Traceability.loadChecklists(start)
  };

  changeObject = function() {
    var form = getForm("filter-check-lists");
    $('filter-check-lists__type').hide();
    $('filter-check-lists_type').hide();

    if (form.object_guid.value == "COperation-") {
      $('filter-check-lists_type').show();
      $V(form._type, '');
    }
    else {
      $('filter-check-lists__type').show();
      $V(form.type, '');
    }
  };

  Main.add(function() {
    changeObject();
  });
</script>
<table class="main layout">
  <tr>
    <td style="width:50%">
      <form name="filter-check-lists">
        <table class="main form">
          <tr>
            <th>{{mb_label object=$check_list_filter field=_date_min}}</th>
            <td>{{mb_field object=$check_list_filter field=_date_min register=true form="filter-check-lists" onchange="this.form.start.value=0"}}</td>
            <th>{{mb_label object=$check_list_filter field=object_id}}</th>
            <td>
              <select name="object_guid" onchange="changeObject();">
                <option value="">{{tr}}All{{/tr}}</option>
                {{foreach from=$list_rooms item=list key=class}}
                  <optgroup label="{{tr}}{{$class}}{{/tr}}">
                    {{foreach from=$list item=room}}
                      <option value="{{$room->_guid}}" {{if $object_guid == $room->_guid}}selected="selected"{{/if}}>{{if $room->_id}}{{$room}}{{else}}Toutes{{/if}}</option>
                    {{/foreach}}
                  </optgroup>
                {{/foreach}}
              </select>
            </td>
          </tr>
          <tr>
            <th>{{mb_label object=$check_list_filter field=_date_max}}</th>
            <td>{{mb_field object=$check_list_filter field=_date_max register=true form="filter-check-lists" onchange="this.form.start.value=0"}}</td>
            <th>{{mb_label object=$check_list_filter field=_type}}</th>
            <td >
              {{mb_field object=$check_list_filter field=_type emptyLabel="All"}}
              {{mb_field object=$check_list_filter field=type emptyLabel="All"}}
            </td>
          </tr>
          <tr>
            <td colspan="6" class="button">
              <button type="button" class="search" onclick="Traceability.filterChecklists(this.form)">{{tr}}Filter{{/tr}}</button>
            </td>
          </tr>
        </table>
      </form>

      {{mb_include module=system template=inc_pagination total=$count_check_lists current=$start change_page='changePage' step=40}}

      <table class="main tbl">
        <tr>
          <th>{{mb_title class=CDailyCheckList field=date}}</th>
          <th class="narrow">{{mb_title class=CDailyCheckList field=date_validate}}</th>
          <th>{{mb_title class=CDailyCheckList field=object_class}}</th>
          <th>{{mb_title class=CDailyCheckList field=object_id}}</th>
          <th>{{mb_title class=CDailyCheckList field=type}}</th>
          <th>{{mb_title class=CDailyCheckList field=comments}}</th>
          <th>{{mb_title class=CDailyCheckList field=list_type_id}}</th>
          <th>{{mb_title class=CDailyCheckList field=validator_id}}</th>
        </tr>
        {{foreach from=$list_check_lists item=curr_list}}
        <tr>
          <td>
            <a href="#" onclick="Traceability.loadChecklist({{$curr_list->_id}})">
              {{mb_value object=$curr_list field=date}}
            </a>
          </td>
          <td>{{mb_value object=$curr_list field=date_validate}}</td>
          <td>
            {{if $curr_list->list_type_id}}
              {{mb_value object=$curr_list->_ref_list_type field=type}}
            {{else}}
              {{mb_value object=$curr_list field=object_class}}
            {{/if}}
          </td>
          <td class="text">
            <span onmouseover="ObjectTooltip.createEx(this, '{{$curr_list->_ref_object->_guid}}')">
              {{$curr_list->_ref_object|truncate:35:"...":true}}
            </span>
          </td>
          <td>{{mb_value object=$curr_list field=type}}</td>
          <td>{{mb_value object=$curr_list field=comments}}</td>
          <td>{{mb_value object=$curr_list field=list_type_id}}</td>
          <td>{{mb_include module=mediusers template=inc_vw_mediuser mediuser=$curr_list->_ref_validator initials="border"}}</td>
        </tr>
        {{foreachelse}}
        <tr>
          <td colspan="10" class="empty">{{tr}}CDailyCheckList.none{{/tr}}</td>
        </tr>
        {{/foreach}}
      </table>
    </td>
    {{if $check_list->_id}}
    <td style="width:50%">
      <table class="main form">
        <tr>
          <th class="title" colspan="2">
            {{mb_include module=system template=inc_object_history object=$check_list}}
            {{$check_list}}
          </th>
        </tr>
        <tr>
          <th>{{mb_label object=$check_list field=date}}</th>
          <td>{{mb_value object=$check_list field=date}}</td>
        </tr>
        <tr>
          <th>{{mb_label object=$check_list field=object_id}}</th>
          <td>
            <span onmouseover="ObjectTooltip.createEx(this, '{{$check_list->_ref_object->_guid}}')">
              {{$check_list->_ref_object}}
            </span>
          </td>
        </tr>
        {{if $check_list->list_type_id}}
          <tr>
            <th>{{mb_label object=$check_list field=list_type_id}}</th>
            <td>{{mb_value object=$check_list field=list_type_id tooltip=true}}</td>
          </tr>
        {{else}}
          <tr>
            <th>{{mb_label object=$check_list field=type}}</th>
            <td>{{mb_value object=$check_list field=type}}</td>
          </tr>
        {{/if}}
        <tr>
          <th>{{mb_label object=$check_list field=validator_id}}</th>
          <td>{{mb_include module=mediusers template=inc_vw_mediuser mediuser=$check_list->_ref_validator}}</td>
        </tr>
        <tr>
          <th>{{mb_label object=$check_list field=date_validate}}</th>
          <td>{{mb_value object=$check_list field=date_validate}}</td>
        </tr>
        {{if $check_list->list_type_id && $check_list->_ref_list_type->use_validate_2}}
          <tr>
            <th>{{mb_label object=$check_list field=validator2_id}}</th>
            <td>{{mb_include module=mediusers template=inc_vw_mediuser mediuser=$check_list->_ref_validator2}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$check_list field=date_validate2}}</th>
            <td>{{mb_value object=$check_list field=date_validate2}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$check_list field=com_validate2}}</th>
            <td>{{mb_value object=$check_list field=com_validate2}}</td>
          </tr>
        {{/if}}
        {{if in_array($check_list->object_class, 'Ox\Mediboard\SalleOp\CDailyCheckList'|static:_HAS_classes) && $check_list->type == "preop_2016"}}
          <tr>
            <th>{{mb_label object=$check_list field=decision_go}}</th>
            <td>{{mb_value object=$check_list field=decision_go}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$check_list field=result_nogo}}</th>
            <td>{{mb_value object=$check_list field=result_nogo}}</td>
          </tr>
        {{/if}}
        {{if $check_list->code_red}}
          <tr>
            <th>{{mb_label object=$check_list field=code_red style="font-size: 1.4em;color:red;font-weight: bold;"}}</th>
            <td>{{mb_value object=$check_list field=code_red}}</td>
          </tr>
        {{/if}}
        <tr>
          <td colspan="2" style="padding: 0;">
            <table class="main">
              {{assign var=category_id value=0}}
              {{foreach from=$check_list->_ref_item_types item=curr_type name=loop_items_types}}
                {{if $curr_type->category_id != $category_id}}
                  <tr>
                    <th colspan="3" class="text category" style="text-align: left;">
                      <strong>{{$curr_type->_ref_category->title}}</strong>
                      {{if $curr_type->_ref_category->desc}}
                        &ndash; {{$curr_type->_ref_category->desc}}
                      {{/if}}
                    </th>
                  </tr>
                {{/if}}
                {{assign var=red_code value='Ox\Mediboard\SalleOp\CDailyCheckList::itemCanRedCode'|static_call:$check_list->object_class:$check_list->type:$smarty.foreach.loop_items_types.index}}
                <tr {{if $red_code}}style="background-color: rgba(255,0,0,0.2);"{{/if}}>
                  <td style="padding-left: 1em; width: 80%;" class="text" colspan="2">
                    {{mb_value object=$curr_type field=title}}
                    <small style="text-indent: 1em; color: #666;">{{mb_value object=$curr_type field=desc}}</small>
                  </td>
                  <td class="text" {{if $curr_type->_checked == "no" && $curr_type->default_value == "yes" || $curr_type->_checked == "yes" && $curr_type->default_value == "no"}}style="color: red; font-weight: bold;"{{/if}}>

                    {{$curr_type->_answer}}
                    {{if $curr_type->_commentaire}}
                      (<span title="{{$curr_type->_commentaire}}">{{$curr_type->_commentaire|truncate:25:"...":true}}</span>)
                    {{/if}}
                  </td>
                </tr>
                {{assign var=category_id value=$curr_type->category_id}}
              {{foreachelse}}
                <tr>
                  <td colspan="3" class="empty">{{tr}}CDailyCheckItemType.none{{/tr}}</td>
                </tr>
              {{/foreach}}
              <tr>
                <td colspan="3">
                  <strong>Commentaires:</strong><br />
                  {{mb_value object=$check_list field=comments}}
                </td>
              </tr>
            </table>
          </td>
        </tr>
        {{if  $check_list->date_validate && 'Ox\Core\CMbDT::minutesRelative'|static_call:$check_list->date_validate:$dtnow < 1440}}
          <tr>
            <td colspan="2" class="button">
              <form name="unvalidate-{{$check_list->_guid}}" method="post"
                    onsubmit="return onSubmitFormAjax(this, function() { document.location.reload(); });">
                {{mb_key object=$check_list}}
                {{mb_class object=$check_list}}
                <input type="hidden" name="validator_id" value=""/>
                <input type="hidden" name="date_validate" value=""/>
                <button type="button" class="cancel" onclick="this.form.onsubmit();">{{tr}}Cancel-validation{{/tr}}</button>
              </form>
            </td>
          </tr>
        {{/if}}
        {{if $check_list->date_validate2 && 'Ox\Core\CMbDT::minutesRelative'|static_call:$check_list->date_validate2:$dtnow < 1440}}
          <tr>
            <td colspan="2" class="button">
              <form name="unvalidate2-{{$check_list->_guid}}" method="post"
                    onsubmit="return onSubmitFormAjax(this, function() { document.location.reload(); });">
                {{mb_key object=$check_list}}
                {{mb_class object=$check_list}}
                <input type="hidden" name="validator2_id" value=""/>
                <input type="hidden" name="date_validate2" value=""/>
                <button type="button" class="cancel" onclick="this.form.onsubmit();">{{tr}}Cancel-validation2{{/tr}}</button>
              </form>
            </td>
          </tr>
        {{/if}}
      </table>
    </td>
    {{/if}}
  </tr>
</table>
