{{*
 * @package Mediboard\Etablissement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main">
  <tr>
    <td>
      {{mb_include module=system template=inc_pagination total=$total current=$page change_page="Group.changePage" step=$step}}
    </td>
  </tr>
  <tr>
    <td>
      <table class="tbl">
        <thead>
          <tr>
            <th class="title" colspan="5">
              <button onclick="Group.editCEtabExterne(0 , '{{$selected}}');" style="float: left;">
                <i class="fas fa-plus"></i> {{tr}}CEtabExterne-title-create{{/tr}}
              </button>

              {{tr}}CEtabExterne-List of external etablishment|pl{{/tr}}
            </th>
          </tr>
          <tr>
            <th>{{mb_label class=CEtabExterne field=nom}}</th>
            <th>{{mb_label class=CEtabExterne field=cp}}</th>
            <th>{{mb_label class=CEtabExterne field=ville}}</th>
            <th>{{mb_label class=CEtabExterne field=finess}}</th>
            <th>{{tr}}common-Action|pl{{/tr}}</th>
          </tr>
        </thead>
        <tbody>
          {{foreach from=$etab_externes item=_etab}}
            {{mb_include module=etablissement template=inc_line_etab_externe}}
          {{foreachelse}}
            <tr>
              <td class="empty" colspan="5">{{tr}}CEtabExterne.none{{/tr}}</td>
            </tr>
          {{/foreach}}
        </tbody>
      </table>
    </td>
  </tr>
</table>
