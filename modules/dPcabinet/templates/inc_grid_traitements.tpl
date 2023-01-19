{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
function addTraitement(rques, type, element) {
  if (window.opener) {
    var oForm = window.opener.document.forms['editTrmtFrm'];
    if (oForm) {
      oForm.traitement.value = rques;
      window.opener.onSubmitTraitement(oForm);
      
      var input = element.select('input').first();
      input.checked = true;
      input.onclick = function(){return false};
      
      $(element).setStyle({cursor: 'default', opacity: 0.3}).onclick = null;
    }
  }
  window.focus();
}
</script>

<!-- Traitements -->
{{assign var=numCols value=4}}
{{math equation="100/$numCols" assign=width format="%.1f"}}

<table class="main tbl" id="traitements" style="display: none;">
{{foreach from=$traitement->_aides.traitement item=type key=curr_key}}
  <tr>
  {{assign var=n value=0}}
  {{foreach from=$type item=curr_helper_for key=curr_helper_for_key}}
    {{if $curr_helper_for_key == "Utilisateur"}}
      {{assign var=owner_icon value="user"}}
    {{elseif $curr_helper_for_key == "Fonction"}}
      {{assign var=owner_icon value="function"}}
    {{else}}
      {{assign var=owner_icon value="group"}}
    {{/if}}
    {{foreach from=$curr_helper_for item=curr_helper key=curr_helper_key name=helpers}}
      {{assign var=i value=$smarty.foreach.helpers.index}}
      {{assign var=n value=$n+1}}
      {{assign var=text value=$curr_helper_key|smarty:nodefaults|JSAttribute}}
      {{if isset($applied_traitements.$text|smarty:nodefaults)}}
        {{assign var=checked value=1}}
      {{else}}
        {{assign var=checked value=0}}
      {{/if}}
      <td class="text {{$owner_icon}}" style="cursor: pointer; {{if $checked}}opacity: 0.3; cursor: default;{{/if}}" 
          title="{{$curr_helper_key|smarty:nodefaults|JSAttribute}}"
          onclick="addTraitement('{{$curr_helper_key|smarty:nodefaults|JSAttribute}}', '{{$curr_key|smarty:nodefaults|JSAttribute}}', this)">
        <input type="checkbox" {{if $checked}}checked{{/if}} />
        {{if $show_text_complet}}{{$curr_helper_key}}{{else}}{{$curr_helper}}{{/if}}
      </td>
      {{if ($i % $numCols) == ($numCols-1) && !$smarty.foreach.helpers.last}}</tr><tr>{{/if}}
    {{/foreach}}
  {{/foreach}}
  {{if $n == 0}}<td class="empty">{{tr}}CAideSaisie.none{{/tr}}</td>{{/if}}
  </tr>
{{/foreach}}
</table>