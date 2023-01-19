{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if count($intersect) == 0}}
  <div class="small-info">{{tr}}CDomain-msg-no_conflicts{{/tr}}</div>
  
  <form name="form-no-conflicts" action="?m=eai" method="post" onsubmit="return onSubmitFormAjax(this)">
    <input type="hidden" name="domain_1_id" value="{{$d1_id}}" />
    <input type="hidden" name="domain_2_id" value="{{$d2_id}}" />
    
    <button type="button" class="change" onclick="return Domain.selectMergeFields(this.form)">
      {{tr}}CDomain-no_conflicts{{/tr}}
    </button>
  </form>
        
  {{mb_return}}
{{/if}}

<div class="small-warning">{{tr}}CDomain-msg-resolve_conflicts{{/tr}} </div>

<form name="form-resolve-conflicts" action="?m=eai" method="post" onsubmit="return onSubmitFormAjax(this)">
  <input type="hidden" name="domain_1_id" value="{{$d1_id}}" />
  <input type="hidden" name="domain_2_id" value="{{$d2_id}}" />
          
  <table class="tbl main">
    <tr>
      <th class="category">{{tr}}CIdSante400-id400{{/tr}} ({{$intersect|@count}})</th>
      <th class="category">{{tr}}CDomain{{/tr}} 1</th>
      <th class="category">{{tr}}CDomain{{/tr}} 2</th>
    </tr>
    {{foreach from=$intersect item=_idex}}
      {{assign var=idex_d1 value=$_idex.0}}
      {{assign var=idex_d2 value=$_idex.1}}
      
      {{assign var=object_d1 value=$idex_d1->_ref_object}}
      {{assign var=object_d2 value=$idex_d2->_ref_object}}
      <tr>
        <td>{{$idex_d1->id400}}</td>
        <td>
          <label>
            <input type="radio" name="idex_ids[{{$idex_d1->id400}}]" value="{{$idex_d1->_id}}" />
            <span onmouseover="ObjectTooltip.createEx(this, '{{$object_d1->_guid}}');">
              {{$object_d1}}
            </span>
          </label>
        </td>
        <td>
           <label>
             <input type="radio" name="idex_ids[{{$idex_d1->id400}}]" value="{{$idex_d2->_id}}" checked />
             <span onmouseover="ObjectTooltip.createEx(this, '{{$object_d2->_guid}}');">
               {{$object_d2}}
             </span>
           </label>
        </td>
      </tr>
    {{/foreach}}
    
    <tr>
      <td colspan="3" style="text-align: center">
        <button type="button" class="change" onclick="return Domain.selectMergeFields(this.form)">
          {{tr}}CDomain-resolve_conflicts{{/tr}}
        </button>
      </td>
    </tr>
  </table>
</form>