{{*
 * @package Mediboard\Personnel
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=personnel script=plage}}

<script>
  function raz(form) {
    $(form).clear(true);
    $V(form.elements.date_debut, "");
    $V(form.elements.date_fin, "");
  }

  Main.add( function() {
    {{if $filter->user_id}}
      PlageConge.loadUser("{{$filter->user_id}}", "{{$filter->_id}}");
      PlageConge.edit("{{$filter->_id}}", "{{$filter->user_id}}");
    {{/if}}
  });
</script>

<table class="main">
  <tr>
    <td class="halfPane">
       {{mb_include module=personnel template=inc_filtre_plage}}
    </td>
    <td>
      <div id="edit_plage"></div>
    </td>
  </tr>
  <tr>
    <td>
      <table class="tbl">
        <tr>
          <th class="title" colspan="2">{{tr}}CPlageConge-list{{/tr}}</th>
        </tr>
        <tr>
          <th class="category">
          {{tr}}CMediusers-_user_last_name{{/tr}} {{tr}}CMediusers-_user_first_name{{/tr}}
          </th>
          <th class="category">
          {{tr}}CPlageConge-corresponding{{/tr}}
          </th>
        </tr>
        {{foreach from=$found_users item=mediuser}}
        <tr id="u{{$mediuser->_id}}" {{if $filter->user_id == $mediuser->_id}}class="selected"{{/if}}>
          <td>
            <a href="#{{$mediuser->_guid}}"
              onclick="PlageConge.loadUser('{{$mediuser->_id}}', ''); PlageConge.edit('','{{$mediuser->_id}}');">
              {{mb_include module=mediusers template=inc_vw_mediuser object=$mediuser}}
            </a>
          </td>
          <td>
            {{assign var=_user_id value=$mediuser->_id}}
            {{$plages_per_user.$_user_id}}
          </td>
        </tr> 
        {{foreachelse}}
        <tr>
          <td colspan="2" class="empty">{{tr}}CMediusers.none{{/tr}}</td>
        </tr>
        {{/foreach}}
      </table>
     </td>
     <td>
       <div id="vw_user"></div>
     </td>
   </tr>
</table>