{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main layout">
  <tr>
    
  {{foreach from=$object->_back.check_lists item=check_list}}
    <td style="vertical-align: top">
      <table class="main form">
        <tr>
          <th class="title">
            Checklist {{mb_value object=$check_list field=type}}
            {{if !$check_list->type && $check_list->_ref_list_type}}{{$check_list->_ref_list_type}}{{/if}}
          </th>
        </tr>
        
        <tr>
          <th class="category">
            {{mb_label object=$check_list field=validator_id}} :
            {{mb_value object=$check_list field=validator_id}}
          </th>
        </tr>
        
        <tr>
          <td style="padding: 0;">
            
            <table class="main">
              {{assign var=category_id value=0}}
              {{foreach from=$check_list->_back.items item=_item}}
                {{assign var=curr_type value=$_item->_ref_item_type}}
                {{if $curr_type->category_id != $category_id}}
                  <tr>
                    <th colspan="3" class="text category" style="text-align: left; border: none;">
                      <strong>{{$curr_type->_ref_category->title}}</strong>
                      {{if $curr_type->_ref_category->desc}}
                        &ndash; {{$curr_type->_ref_category->desc}}
                      {{/if}}
                    </th>
                  </tr>
                {{/if}}
                <tr>
                  <td style="padding-left: 1em; width: 100%; border: none;" class="text" colspan="2">
                    {{mb_value object=$curr_type field=title}}
                    <small style="text-indent: 1em; color: #666;">{{mb_value object=$curr_type field=desc}}</small>
                  </td>
                  <td {{if !$_item->commentaire}}class="text"{{/if}}
                      style="border: none;{{if $_item->commentaire}}white-space: pre-line;{{/if}}{{if $_item->checked == "no"}}color: red; font-weight: bold;{{/if}}"
                  > {{$_item->getAnswer()}}{{if $_item->commentaire}}({{$_item->commentaire}}){{/if}}</td>
                </tr>
                {{assign var=category_id value=$curr_type->category_id}}
              {{foreachelse}}
                <tr>
                  <td colspan="3" class="empty" style="border: none;">{{tr}}CDailyCheckItemType.none{{/tr}}</td>
                </tr>
              {{/foreach}}

              {{if $check_list->comments}}
              <tr>
                <td colspan="3" style="border: none;">
                  <strong>Commentaires:</strong><br />
                  {{mb_value object=$check_list field=comments}}
                </td>
              </tr>
              {{/if}}
            </table>
          </td>
        </tr>
        {{if $check_list->type == "preop_2016" || $check_list->decision_go}}
          <tr>
            <td colspan="2" class="text">
              <hr />
              <strong>{{mb_label object=$check_list field=decision_go}}</strong>:
              <span style="font-size: 1.2em;
                {{if $check_list->decision_go == "nogo"}}color:red;{{elseif $check_list->decision_go == "go"}}color:green;{{/if}}">
                {{mb_value object=$check_list field=decision_go}}
                    {{if $check_list->decision_go == "nogo"}}
                      ({{mb_value object=$check_list field=result_nogo}})
                    {{/if}}
              </span>
            </td>
          </tr>
        {{/if}}
        {{if $check_list->validator_id}}
          <tr>
            <td>
              <strong>
                {{tr}}CDailyCheckList-Validated by{{/tr}}{{mb_value object=$check_list field=validator_id}}
                {{if $check_list->date_validate}}
                  {{tr var1=$check_list->date_validate|date_format:$conf.date var2=$check_list->date_validate|date_format:$conf.time}}
                    common-the %s at %s
                  {{/tr}}
                {{/if}}
              </strong>
            </td>
          </tr>
        {{/if}}
      </table>
    </td>
  {{foreachelse}}
    <td class="empty">{{tr}}CDailyCheckList.none{{/tr}}</td>
  {{/foreach}}
  
  </tr>
</table>
