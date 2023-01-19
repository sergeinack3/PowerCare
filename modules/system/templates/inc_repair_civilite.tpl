{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  changePage = function(page) {
    var url = new Url('system', 'ajax_repair_civilite');
    url.addParam('start', page);
    url.addParam('check', 1);
    url.requestUpdate('result-patients-civilite');
  }
</script>

<table class="main tbl">
  <tr>
    <td colspan="5">
      {{mb_include module=system template=inc_pagination current=$start step=$step total=$count change_page=changePage}}
    </td>
  </tr>

  <tr>
    <th class="narrow" align="center">
      {{mb_label class=CPatient field=nom}}
    </th>
    <th class="narrow" align="center">
      {{mb_label class=CPatient field=prenom}}
    </th>
    <th class="narrow" align="center">
      {{mb_label class=CPatient field=naissance}}
    </th>
    <th align="center">
      {{mb_label class=CPatient field=sexe}}
    </th>
    <th align="center">
      {{mb_label class=CPatient field=civilite}}
    </th>
  </tr>

  {{foreach from=$patients item=_pat}}
    <tr>
      <td align="center">
        {{$_pat.nom}}
      </td>
      <td align="center">
        {{$_pat.prenom}}
      </td>
      <td align="center">
        {{$_pat.naissance|date_format:$conf.date}}
      </td>
      <td align="center">
        {{$_pat.sexe}}
      </td>
      <td align="center">
        {{$_pat.civilite}}
      </td>
    </tr>
  {{/foreach}}
</table>