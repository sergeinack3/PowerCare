{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=errors value=0}}

<table class="main tbl">
  <tr>
    <td colspan="5">
      {{mb_include module=system template=inc_pagination total=$total current=$start step=$step change_page='changePage'}}
    </td>
  </tr>
  <tr>
    <th class="narrow"></th>
    <th>{{tr}}CTranslationOverwrite-source{{/tr}}</th>
    <th>{{tr}}CTranslationOverwrite-_old_translation{{/tr}}</th>
    <th>{{tr}}CTranslationOverwrite-translation{{/tr}}</th>
    <th>{{tr}}CTranslationOverwrite-language{{/tr}}</th>
  </tr>
  {{foreach from=$translations_bdd item=_trad}}
    <tr {{if $_trad->_old_translation == $_trad->translation}}{{assign var=errors value=$errors+1}}class="hatching"{{/if}}>
      <td>
        <button class="button edit notext" onclick="editTrad({{$_trad->_id}})">{{tr}}Edit{{/tr}}</button>
      </td>
      <td>{{mb_value object=$_trad field=source}}</td>
      <td>{{$_trad->_old_translation|markdown}}</td>
      <td {{if !$_trad->_in_cache}}class="warning"{{/if}}><strong>{{mb_value object=$_trad field=translation}}</strong></td>
      <td><img src="images/icons/flag-{{$_trad->language}}.png"
               alt=""/> {{tr}}CTranslationOverwrite.language.{{$_trad->language}}{{/tr}}</td>
    </tr>
  {{foreachelse}}
    <tr>
      <td colspan="4" class="empty">{{tr}}CTranslationOverwrite.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>


<script>
  Main.add(function () {
    var btn = $('remove-old-translations');
    btn.innerHTML = $T('system-action-purge translations') + '({{$errors}})';
    if ({{$errors}} === 0) {
      btn.disable();
    }
  });
</script>