{{*
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  nextPurge = function(author_id, next_start) {
    var form = getForm("purge-dossiers-" + author_id);

    if (form) {
      if (next_start) {
        $V(form.elements.start, next_start);
      }

      form.onsubmit();
    }
  };

  showDryRun = function (author_id) {
    var url = new Url('importTools', 'vw_purge_dossiers_export_status');
    url.addParam('author_id', author_id);
    url.requestModal();
  }
</script>

<table class="main tbl">
  <tr>
    <th class="narrow">Auteur</th>
    <th class="narrow">Nombre de fichiers</th>
    <th></th>
  </tr>

  {{foreach from=$purge item=_author}}
    <tr>
      <td>
         <span onmouseover="ObjectTooltip.createEx(this, 'CMediusers-{{$_author.author_id}}');">
        CMediusers-{{$_author.author_id}}
      </span>
      </td>

      <td>
        {{$_author.count|number_format:0:',':' '}}
      </td>

      <td>
        <form name="purge-dossiers-{{$_author.author_id}}" method="post" onsubmit="return onSubmitFormAjax(this, null, 'result-purge-{{$_author.author_id}}')">
          <input type="hidden" name="m" value="importTools"/>
          <input type="hidden" name="dosql" value="do_purge_dossiers_export"/>
          <input type="hidden" name="author_id" value="{{$_author.author_id}}"/>
          <input type="hidden" name="start" value="0"/>

          <label>
            Automatique
            <input type="checkbox" name="continue" value="1"/>
          </label>

          <label>
            Essaie à blanc
            <input type="checkbox" name="dry_run" value="1" checked/>
          </label>

          <label>
            Reset
            <input type="checkbox" name="reset" value="1"/>
          </label>


          <button type="submit" class="change">Purger</button>
          <button type="button" class="search" onclick="showDryRun({{$_author.author_id}});">Afficher l'état</button>
        </form>

        <div id="result-purge-{{$_author.author_id}}"></div>
      </td>
    </tr>
  {{/foreach}}
</table>