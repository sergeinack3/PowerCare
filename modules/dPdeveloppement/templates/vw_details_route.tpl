{{*
* @package Mediboard\Developpement
* @author  SAS OpenXtrem <dev@openxtrem.com>
* @license https://www.gnu.org/licenses/gpl.html GNU General Public License
* @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<style>
  #route-details pre {
    max-height: 100%;
  }
</style>

<div id="route-details">
    {{$json|highlight:json|smarty:nodefaults}}
</div>