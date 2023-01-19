{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$liste->_id}}
  <div class="small-info">{{tr}}CCompteRendu-msg-The list must be created in order to manage the choices{{/tr}}</div>
  {{mb_return}}
{{/if}}

<table class="tbl me-table-card-list me-no-border-radius-bottom">
  <tr><th class="category" colspan="2">{{tr}}CCompteRendu-Available choice|pl{{/tr}}</th></tr>
</table>

<div style="height: 150px; overflow-y: auto;" class="me-no-align">
  <table class="tbl me-align-auto me-no-border-radius-top">
    {{foreach from=$liste->_valeurs item=_valeur name=choix}}
    <tr>
      <td class="text" data-valeur="{{$_valeur|nl2br}}">{{$_valeur|nl2br}}</td>
      <td class="narrow">
        <form name="Del-Choix-{{$smarty.foreach.choix.iteration}}" method="post" onsubmit="return ListeChoix.onSubmitChoix(this);">
          {{mb_class object=$liste}}
          {{mb_key   object=$liste}}

          {{mb_field object=$liste field=valeurs hidden=1}}
          <input type="hidden" name="_del" value="{{$_valeur}}" />
          <input type="hidden" name="_modify" />
          <button class="cancel notext compaxt me-tertiary" type="button" style="display: none;"
                  onclick="ListeChoix.cancelEditChoix(this);">{{tr}}Cancel{{/tr}}</button>
          <button class="tick notext compaxt me-secondary"   type="button" style="display: none;"
                  onclick="ListeChoix.valideChoix(this);">{{tr}}Validate{{/tr}}</button>
          <button class="edit notext compact" type="button"
                  onclick="ListeChoix.editChoix(this);">{{tr}}Edit{{/tr}}</button>
          <button class="remove notext compact me-tertiary" type="submit">{{tr}}Delete{{/tr}}</button>
        </form>
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td class="empty">{{tr}}CListeChoix{{/tr}}</td>
    </tr>
    {{/foreach}}
  </table>
</div>
 
<form name="Add-Choix" action="?m={{$m}}" method="post" onsubmit="return ListeChoix.onSubmitChoix(this);">
  {{mb_class object=$liste}}
  {{mb_key   object=$liste}}
  
  {{mb_field object=$liste field=valeurs hidden=1}}
  
  <table class="form">
    <tr>
      <th id="inc_list_choix_ajouter_choix" class="category" colspan="2">{{tr}}CCompteRendu-action-Add choice{{/tr}}</th>
    </tr>
    <tr>
      <td>
        <textarea name="_new"></textarea>
      </td>
    </tr>
    <tr>
      <td class="button">
        <button id="inc_list_choix_ajouter_choix_button" type="submit" class="add">{{tr}}Add{{/tr}}</button>
      </td>
     </tr>
  </table>

</form>
