{{*
 * @package Mediboard\Ftp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main">
  <tr>
    <td>
      <table class="tbl">
        {{if is_array($files)}}
          <tr>
            <th> Liste des fichiers du dossier </th>
          </tr>
          {{foreach from=$files item=_file}}
          <tr>
            <td class="text">
              {{if $_file.size === "-1"}}
                <img src="modules/ftp/images/directory.png"/>
              {{else}}
                <a target="blank"
                   href="?m=system&a=download_file&filename={{$_file.name}}&exchange_source_guid={{$exchange_source->_guid}}&dialog=1&suppressHeaders=1"
                   class="button download notext">
                    {{tr}}Download{{/tr}}
                </a>
              {{/if}}
              {{$_file.name}}
            </td>
          </tr>
          {{/foreach}}
        {{else}}
          <tr>
            <th>Impossible de lister les fichiers</th>
          </tr>
        {{/if}}
      </table>
    </td>
  </tr>
</table>
