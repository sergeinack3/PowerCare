{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div id="pseudonymise-vw">
  <form name="pseudonymise-form" method="post" onsubmit="return onSubmitFormAjax(this, null, 'result-pseudonymise');">
    <input type="hidden" name="m" value="system"/>
    <input type="hidden" name="dosql" value="do_pseudonymise_some"/>
    <input type="hidden" name="continue" value="1"/>

    <table class="main form">
      <tr>
        <th><label for="class_selected">{{tr}}CObjectPseudonymiser-object type{{/tr}}</label></th>
        <td>
          <select name="class_selected" onchange="ObjectPseudonymiser.changeClassSelected(this);">
            {{foreach from=$classes item=_class}}
              <option value="{{$_class}}" {{if $_class == $class_selected}}selected{{/if}}>
                {{tr}}{{$_class}}{{/tr}}
              </option>
            {{/foreach}}
          </select>
        </td>
      </tr>

      <tr>
        <th><label for="count">{{tr}}CObjectPseudonymiser-count{{/tr}}</label></th>
        <td>
          <select name="count"">
            {{foreach from=$counts item=_count}}
              <option value="{{$_count}}" {{if $_count == 100}}selected{{/if}}>
                {{$_count}}
              </option>
            {{/foreach}}
          </select>
        </td>
      </tr>

      <tr>
        <th><label for="last_id">{{tr}}common-last_id{{/tr}}</label></th>
        <td>
          <input type="number" name="last_id" value="{{if $last_id}}{{$last_id|number_format:0:',':' '}}{{else}}0{{/if}}"/>
          / <span id="pseudonymise-total-count">{{$total|number_format:0:',':' '}}</span>
        </td>
      </tr>

      <tr>
        <th><label for="delais">{{tr}}system-pseudonymise-delais{{/tr}}</label></th>
        <td>
          <input type="number" name="delais" size="5" value="{{if $delais}}{{$delais}}{{/if}}"/>
        </td>
      </tr>

      {{if $class_selected == 'CUser'}}
        <tr>
          <th><label for="pseudo_admin" title="{{tr}}system-pseudonymise-Exclude administrator-desc{{/tr}}">{{tr}}system-pseudonymise-Exclude administrator{{/tr}}</label></th>
          <td>
            <input type="checkbox" name="pseudo_admin" value="0"/>
          </td>
        </tr>
      {{/if}}


      <tr>
        <td colspan="2" class="button">
          <button class="button import" id="pseudonymise-start-btn" type="button" onclick="ObjectPseudonymiser.doPseudonymiseSome();">
            {{tr}}CObjectPseudonymiser-action-pseudonymise{{/tr}}
          </button>
          <button class="button stop" id="pseudonymise-stop-btn" type="button" onclick="ObjectPseudonymiser.stopPseudonymise()" disabled>
          {{tr}}Stop{{/tr}}
          </button>
        </td>
      </tr>
    </table>
  </form>

  <div id="result-pseudonymise"></div>
</div>
