{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  changePage = function(page) {
    var form = getForm("filterEvenements");
    $V(form.current, page);
    form.onsubmit();
  }
</script>

<form name="filterEvenements" method="get" onsubmit="return onSubmitFormAjax(this, null, 'area_evenements')">
  <input type="hidden" name="m" value="ssr" />
  <input type="hidden" name="a" value="ajax_list_actes_doublons" />
  <input type="hidden" name="current" value="0" />
  <input type="hidden" name="dry_run" value="1" />
  <table class="form">
    <tr>
      <th class="title" colspan="2">{{tr}}filter{{/tr}}</th>
    </tr>
    <tr>
      <th>
        {{mb_label object=$evenement field="_debut"}}
      </th>
      <td>
        {{mb_field object=$evenement field="_debut" form="filterEvenements" register=true}}
      </td>
    </tr>
    <tr>
      <th>
        {{mb_label object=$evenement field="_fin"}}
      </th>
      <td>
        {{mb_field object=$evenement field="_fin" form="filterEvenements" register=true}}
      </td>
    </tr>
    <tr>
      <td colspan="2" class="button">
        <button class="search" type="button" onclick="$V(this.form.dry_run, 1); this.form.onsubmit();">{{tr}}Filter{{/tr}}</button>
        <button class="trash"  type="button" onclick="$V(this.form.dry_run, 0); this.form.onsubmit();">{{tr}}ssr-erase_doublons{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

<div id="area_evenements"></div>