{{*
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=id value=$name|md5}}

<div style="padding: 1px; padding-left: {{$depth*12}}px;">
  {{if $name|is_dir}}
    <img src="modules/ftp/images/directory.png" />
    {{$name|basename}}
  {{else}}
    <img src="{{$name}}" style="height: 25px;" onmouseover="ObjectTooltip.createDOM(this, 'img-{{$id}}', {duration: 1})" />
    <img src="{{$name}}" style="display: none;" id="img-{{$id}}" />
    <form name="select-picture-{{$id}}" method="post" onsubmit="return onSubmitFormAjax(this, Control.Modal.close)">
      <input type="hidden" name="m" value="monitoringPatient" />
      <input type="hidden" name="dosql" value="do_select_supervision_picture" />
      <input type="hidden" name="path" value="{{$name}}" />
      <input type="hidden" name="timed_picture_id" value="{{$timed_picture_id}}" />

      <a href="#1" onclick="this.up('form').onsubmit()">
        {{$name|basename}}
      </a>
    </form>
  {{/if}}
</div>

{{if $_subtree|@is_array}}
  {{foreach from=$tree key=_name item=_subtree}}
    {{mb_include module=monitoringPatient template=inc_supervision_picture_tree tree=$_subtree name=$_name depth=$depth+1}}
  {{/foreach}}
{{/if}}