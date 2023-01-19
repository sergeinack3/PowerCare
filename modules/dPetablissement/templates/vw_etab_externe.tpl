{{*
 * @package Mediboard\Etablissement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=etablissement script=Group ajax=1}}

<script>
  Main.add(function () {
    Group.listEtabExternes(getForm('filter-etab_externes'));
  });
</script>

<fieldset class="me-align-auto">
  <legend><i class="fas fa-filter"></i> {{tr}}CEtabExterne-Filter etablishment|pl{{/tr}}</legend>
  <form name="filter-etab_externes" method="get" action="?">
    <input type="hidden" name="m" value="etablissement" />
    <input type="hidden" name="{{$actionType}}" value="{{$action}}" />
    <input type="hidden" name="page" value="0" />
    <input type="hidden" name="selected" value="{{$selected}}" />
    <table class="form">
      <tr>
        <th>
          {{mb_label object=$filter field=nom}}
        </th>
        <td>
          {{mb_field object=$filter field=nom canNull=true}}
        </td>
        <th>
          {{mb_label object=$filter field=cp}}
        </th>
        <td>
          {{mb_field object=$filter field=cp canNull=true}}
        </td>
      </tr>
      <tr>
        <th>
          {{mb_label object=$filter field=finess}}
        </th>
        <td>
          {{mb_field object=$filter field=finess canNull=true}}
        </td>
        <th>
          {{mb_label object=$filter field=ville}}
        </th>
        <td>
          {{mb_field object=$filter field=ville canNull=true}}
        </td>
      </tr>
      <tr>
        <td colspan="4" class="button">
          <button type="button" onclick="Group.listEtabExternes(this.form);">
            <i class="fas fa-search"></i> {{tr}}Filter{{/tr}}
          </button>
        </td>
      </tr>
    </table>
  </form>
</fieldset>

<div id="list_etab_externe" class="me-padding-0"></div>

