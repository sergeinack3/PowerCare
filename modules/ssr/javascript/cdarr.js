/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

CdARR = {
  viewActiviteStats: function(code) {
    new Url('ssr', 'vw_activite_cdarr_stats') .
      addParam('code', code) .
      requestModal(700);
  }
};