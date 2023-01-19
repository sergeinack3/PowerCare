{{*
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    Configuration.edit('importTools', ['CGroups', 'CService CGroups.group_id', 'CFunctions CGroups.group_id', 'CBlocOperatoire CGroups.group_id'], $('CConfigEtabImportTools'));
  });
</script>

<div id="CConfigEtabImportTools"></div>