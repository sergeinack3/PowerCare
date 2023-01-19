{{*
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  Main.add(function() {
    Configuration.edit(
      'messagerie',
      ['CGroups'],
      'group_configs'
    );
  });

  reloadHprimConfigs = function() {
    var url = new Url('messagerie', 'ajax_hprimnet_config');
    url.requestUpdate('hprimnet');
  };

  Main.add(function() {
    Control.Tabs.create('tabs-configure');
    reloadHprimConfigs();
  });
</script>

<ul id="tabs-configure" class="control_tabs">
  <li><a href="#messagerie">Messagerie</a></li>
  <li><a href="#ldap">LDAP</a></li>
  <li><a href="#hprimnet">HPRIM.Net</a></li>
</ul>

<div id="messagerie" style="display: none;">
  <form name="editConfig" method="post" onsubmit="return onSubmitFormAjax(this);">
    {{mb_configure module=$m}}

    <table class="form">
      <tr>
        <th colspan="2" class="title">Mises à jour planifiées</th>
      </tr>

      {{mb_include module=system template=inc_config_str numeric=true var=CronJob_nbMail}}
      {{mb_include module=system template=inc_config_str numeric=true var=CronJob_schedule}}
      {{mb_include module=system template=inc_config_str numeric=true var=CronJob_olderThan}}

      <tr>
        <td class="button" colspan="2">
          <button class="modify">{{tr}}Save{{/tr}}</button>
        </td>
      </tr>
    </table>
  </form>

  <div id="group_configs"></div>
</div>

<div id="ldap" style="display: none;">
  <div class="small-info">
    La configuration par établissement Annuaire LDAP doit être à Oui afin d'activer la recherche des destinataires dans l'annuaire LDAP
  </div>

  {{mb_include module=admin template=inc_sources_ldap}}
</div>

<div id="hprimnet" style="display: none;">

</div>