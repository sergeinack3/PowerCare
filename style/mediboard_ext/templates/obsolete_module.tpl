{{*
 * @package Mediboard\Style\Mediboard
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $obsolete_module}}
  <div class="small-error">
    <strong>Ce module n'est pas à jour.</strong> 
    Veuillez vous rendre sur <a href="?m=system&amp;tab=view_modules">la page d'administration des modules</a>.
  </div>
{{/if}}