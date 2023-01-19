{{*
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div class="classAutocomplete">
  <div class="classAutocomplete-section expand">
    <div class="classAutocomplete-title">
        {{$class_tr|html_entity_decode|emphasize:$keywords:'mark'}}
    </div>
    <div class="classAutocomplete-subtitle">
        {{$shortname|emphasize:$keywords:'mark'}}
    </div>
  </div>
  <div class="classAutocomplete-section">
    <div class="classAutocomplete-extra">
      <div class="me-text-align-right opacity-50">
          {{$module_tr|html_entity_decode|emphasize:$keywords:'mark'}}
      </div>
      <div class="classAutocomplete-extraIcon">
        <div class="module-icon">
          <i class="mdi mdi-18px mdi-bookmark" style="float: right"></i>
        </div>
      </div>
    </div>
    <div class="classAutocomplete-extra">
      <div class="me-text-align-right opacity-50 classAutocomplete-extraTable">
          {{$table_name|emphasize:$keywords:'mark'}}
      </div>
      <div class="classAutocomplete-extraIcon database">
        <i class="me-icon database"></i>
      </div>
    </div>
  </div>
</div>
