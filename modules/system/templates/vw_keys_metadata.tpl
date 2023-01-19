{{*
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=system script=key_metadata}}

<table class="main tbl">
  <tr>
    <th class="narrow">{{mb_title class=CKeyMetadata field=name}}</th>
    <th class="narrow">{{mb_title class=CKeyMetadata field=alg}}</th>
    <th class="narrow">{{mb_title class=CKeyMetadata field=mode}}</th>
    <th>{{mb_title class=CKeyMetadata field=creation_date}}</th>
  </tr>

    {{foreach from=$keys_metadata key=_name item=_metadata}}
      <tr id="key-metadata-{{$_metadata->_id}}">
        {{mb_include module=system template=inc_vw_key_metadata metadata=$_metadata}}
      </tr>
    {{/foreach}}
</table>
