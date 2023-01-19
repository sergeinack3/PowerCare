{{*
 * @package Mediboard\Dicom
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=dicom script=DicomSession ajax=true}}

<table class="main layout">
  <tr>
    <td id="search">
      {{mb_include template="inc_filter_sessions"}}
    </td>
  </tr>
  <tr>
    <td id="sessionsList">
      
    </td>
  </tr>
</table>
