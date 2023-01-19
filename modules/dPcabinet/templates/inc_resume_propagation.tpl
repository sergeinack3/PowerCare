{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  {{foreach from=$resume key=_date item=_resume}}
  <tr>
    <th class="title">{{$_date|date_format:$conf.date}}</th>
  </tr>

  <tr>
    <th>
      {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$chir}}
    </th>
  </tr>
  <tr>
    <td>
      <div class="{{if $_resume.humaine.error}}warning{{else}}info{{/if}}">
        {{$_resume.humaine.msg}}
      </div>
    </td>
  </tr>

  {{foreach from=$_resume.materielle key=ressource_cab_id item=__ressource}}
    <tr>
      <th>
        {{$ressources.$ressource_cab_id}}
      </th>
    </tr>
    <tr>
      <td>
        <div class="{{if $__ressource.error}}warning{{else}}info{{/if}}">
          {{$__ressource.msg}}
        </div>
      </td>
    </tr>
  {{/foreach}}
  {{/foreach}}

  <tr>
    <td class="button">
      <button type="button" class="cancel" onclick="Control.Modal.close();">{{tr}}Close{{/tr}}</button>
    </td>
  </tr>
</table>