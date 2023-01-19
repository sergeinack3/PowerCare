{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=cabinet script=timeline_implement ajax=$ajax}}

{{if 'mondialSante'|module_active}}
  {{mb_script module=mondialSante script=MondialSante ajax=true}}
  {{mb_script module=files script=files ajax=true}}
{{/if}}

{{if 'mssante'|module_active}}
  {{mb_script module=mssante script=Attachment ajax=true}}
{{/if}}

<script>
  Main.add(function () {
    TimelineImplement.refreshResume([], '{{$base->_id}}');
  })
</script>

<div id="main_timeline"></div>
