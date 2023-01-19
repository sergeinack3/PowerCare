{{*
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module="mediusers" script="CDiscipline"}}

<script>
  Main.add(function() {
    getForm('listFilter').onsubmit();
  });
</script>

{{if $can->edit}}
  <div class="me-margin-top-4 me-padding-left-8">
    <a class="button new" onclick="CDiscipline.edit('0')">
      {{tr}}CDiscipline-title-create{{/tr}}
    </a>
  </div>
{{/if}}

<table class="main main me-w100 me-margin-top-4">
  <tr>
    <td style="width: 60%">
      <form name="listFilter" action="?" method="get"
            onsubmit="return onSubmitFormAjax(this, null, 'list_disciplines')">
        <input type="hidden" name="m" value="mediusers" />
        <input type="hidden" name="a" value="ajax_search_discipline" />
        <input type="hidden" name="page" value="{{$page}}" onchange="this.form.onsubmit()"/>

        <table class="main layout">
          <tr>
            <td class="separator expand" onclick="MbObject.toggleColumn(this, $(this).next())"></td>

            <td>
              <table class="form">
                <tr>
                  <th style="width: 8%"> Mots clés : </th>
                  <td> <input type="text" name="filter" value="" style="width: 20em;" onchange="$V(this.form.page, 0)" /> </td>
                </tr>

                <tr>
                  <td colspan="2">
                    <button type="submit" class="search">{{tr}}Filter{{/tr}}</button>
                  </td>
                </tr>
               </table>
             </td>
          </tr>
        </table>
      </form>
    </td>
  </tr>
</table>

<div id="list_disciplines" style="overflow: hidden" class="me-padding-10"></div>