{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{foreach from=$sspi->_ref_sspis_links item=_sspi_link}}
  <tr>
    <td class="narrow">
      <form name="delSSPI{{$_sspi_link->_id}}" method="post"
            onsubmit="return onSubmitFormAjax(this, Bloc.reloadLists.curry('{{$sspi->_id}}'));">
        {{mb_class object=$_sspi_link}}
        {{mb_key   object=$_sspi_link}}
        <input type="hidden" name="del" value="1" />
        <button type="button" class="trash notext" title="{{tr}}Delete{{/tr}}"
                onclick="this.form.onsubmit();"></button>
      </form>
    </td>
    <td>
      {{mb_value object=$_sspi_link->_ref_bloc field=nom}}
    </td>
  </tr>
{{foreachelse}}
  <tr>
    <td class="empty">{{tr}}CBlocOperatoire.none{{/tr}}</td>
  </tr>
{{/foreach}}