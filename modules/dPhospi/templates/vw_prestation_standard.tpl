{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_include template=inc_warning_config_prestations wanted=standard}}
{{mb_script module=dPhospi script=infrastructure ajax=1}}
<table class="main">

  <tr>
    <td class="halfPane">
      <a href="#" onclick="Infrastructure.showInfrastructure('prestation_id', '0', 'infrastructure_prestation')" class="button new">
        {{tr}}CPrestation-title-create{{/tr}}
      </a>

      <table class="tbl">

        <tr>
          <th colspan="4" class="title">{{tr}}CPrestation.all{{/tr}}</th>
        </tr>

        <tr>
          <th class="narrow">{{mb_title class=CPrestation field=code}}</th>
          <th>{{mb_title class=CPrestation field=nom}}</th>
          <th>{{mb_title class=CPrestation field=description}}</th>
        </tr>

        {{foreach from=$prestations item=_prestation}}
          <tr {{if $_prestation->_id == $prestation->_id}} class="selected" {{/if}}>
            <td>{{mb_value object=$_prestation field=code}}</td>
            <td>
              <a href="#"
                 onclick="Infrastructure.showInfrastructure('prestation_id', '{{$_prestation->_id}}', 'infrastructure_prestation')">
                {{mb_value object=$_prestation field=nom}}
              </a>
            </td>
            <td class="text compact">{{mb_value object=$_prestation field=description}}</td>
          </tr>
          {{foreachelse}}
          <tr>
            <td colspan="3" class="empty">{{tr}}CPrestation.none{{/tr}}</td>
          </tr>
        {{/foreach}}

      </table>

    </td>

    <td class="halfPane" id="infrastructure_prestation">
      {{mb_include module=hospi template=inc_vw_prestation}}
    </td>
  </tr>

</table>
