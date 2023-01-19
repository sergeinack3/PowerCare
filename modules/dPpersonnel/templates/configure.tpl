{{*
 * @package Mediboard\Personnel
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  affUpdate = function() {
    new Url('personnel', 'ajax_update_affectations')
      .addParam('step', $V(getForm('Configure').step))
      .requestUpdate('aff_area');
  };
  Main.add(Control.Tabs.create.curry('tabs-configure', true, {
    afterChange: function(container) {
      if (container.id === 'CConfigEtab') {
        Configuration.edit('personnel', ['CGroups', 'CService CGroups.group_id'], $('CConfigEtab'));
      }
    }
  }));
</script>

<ul id="tabs-configure" class="control_tabs">
  <li><a href="#general">{{tr}}General{{/tr}}</a></li>
  <li><a href="#CConfigEtab">{{tr}}CConfigEtab{{/tr}}</a></li>
</ul>

<div id="general">
  <form name="Configure" method="post" onsubmit="return onSubmitFormAjax(this);">
    {{mb_configure module=$m}}
    <table class="form">
      {{assign var=class value=CPlageConge}}
      <tr>
        <th class="category" colspan="2">{{tr}}CSyslogSource-Utilities{{/tr}}</th>
      </tr>
      <tr>
        <td>
          <input type="text" name="step" value="0" />
          <button type="button" class="change" onclick="affUpdate();">Mettre à jour les affectations (plages opératoires)</button>
        </td>
        <td id="aff_area"></td>
      </tr>
    </table>
  </form>
</div>
<div id="CConfigEtab"></div>