{{*
 * @package Mediboard\Context
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  Main.add(function(){
    Configuration.edit(
      'context',
      ['CGroups'],
      'context-config-groups'
    );
  });
</script>

<div id="context-config-groups"></div>
<div class="small-info" style="display: inline-block;">
  {{tr var1=$session_lifetime}}CPreferences-msg-Server session lifetime configuration : %s minutes{{/tr}}
</div>