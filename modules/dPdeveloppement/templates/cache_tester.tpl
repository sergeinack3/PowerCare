{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  CacheTester = {
    users: function(purge) {
      new Url('developpement', 'cache_tester_users') .
        addParam('purge', purge) .
        requestUpdate('users');
    },

    metamodel: function() {
      new Url('developpement', 'cache_tester_metamodel') .
        requestUpdate('metamodel');
    }
  };

  Main.add(function() {
    Control.Tabs.create('tabs-tests', true).activeLink.onmouseup();
  });
</script>


<ul id="tabs-tests" class="control_tabs">
  <li><a href="#users"     onmouseup="CacheTester.users();">Utilisateurs et fonctions</a></li>
  <li><a href="#metamodel" onmouseup="CacheTester.metamodel();">Métamodèle</a></li>
</ul>

<div id="users" style="display: none;"></div>

<div id="metamodel" style="display: none;"></div>