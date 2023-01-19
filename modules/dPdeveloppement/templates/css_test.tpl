{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<style>
  .opacity {
    background: red;
    width: 40px;
    text-align: center;
  }

  div.opacity {
    display: inline-block;
  }
</style>

<script type="text/javascript">
  Main.add(function(){
    var form = getForm("test");
    Calendar.regField(form.dateTime);
    Calendar.regProgressiveField(form.progressiveDate);
    Calendar.regField(form.time);
    Calendar.regField(form.date);
    Calendar.regField(form.dateInline, null, {inline: true, container: $(form.dateInline).up(), noView: true});

    //Form.multiSubmit($$("form"), {check: false});
  });

  /*
   Modal.open
   Modal.confirm
   Modal.alert
   */
  TestModal = {
    /**
     * @return {Url}
     */
    getUrl: function(){
      var url = new Url("developpement", "css_test");
      url.addParam("nodebug", 1);
      return url;
    },
    modal: function(){
      this.getUrl().modal();
    },
    modalClose: function(){
      this.getUrl().modal({
        onClose: function(){
          alert("Closed !");
        }
      });
    },
    modalSize: function(){
      this.getUrl().modal({
        width: 500,
        height: 400
      });
    },
    modalSizePercent: function(){
      this.getUrl().modal({
        width: "60%",
        height: "60%"
      });
    },
    modalSizeNegative: function(){
      this.getUrl().modal({
        width: -50,
        height: -50
      });
    },
    requestModal: function(){
      this.getUrl().requestModal();
    },
    requestModalClose: function(){
      this.getUrl().requestModal(null, null, {
        onClose: function(){
          alert("Closed !");
        }
      });
    },
    requestModalSmall: function(){
      var url = this.getUrl();
      url.addParam("a", "view_logs");
      url.requestModal();
    },
    requestModalSize: function(){
      this.getUrl().requestModal(500, 400);
    },
    requestModalSizePercent: function(){
      this.getUrl().requestModal("60%", "60%");
    },
    requestModalSizeNegative: function(){
      this.getUrl().requestModal(-50, -50);
    },
    modalIncrustable: function() {
      this.getUrl().requestModal("75%", "75%", {incrustable: true})
    },
    modalOpen: function(){
      Modal.open("buttons");
    },
    modalConfirm: function(){
      Modal.confirm("Sure ?");
    },
    modalAlert: function(){
      Modal.alert("Yo! Sure ?");
    }
  };
</script>

<table class="main layout">
  <tr>
    <td>
      <fieldset>
        <legend>Modal windows tests</legend>

        <button class="new" onclick="TestModal.modal()">
          url.modal (IFrame)
        </button>
        <button class="new" onclick="TestModal.modalSize()">
          url.modal + size (IFrame)
        </button>
        <button class="new" onclick="TestModal.modalClose()">
          url.modal + onClose (IFrame)
        </button>
        <button class="new" onclick="TestModal.modalSizePercent()">
          url.modal + size percent (IFrame)
        </button>
        <button class="new" onclick="TestModal.modalSizeNegative()">
          url.modal + size negative (IFrame)
        </button>

        <br />

        <button class="new" onclick="TestModal.requestModal()">
          url.requestModal
        </button>
        <button class="new" onclick="TestModal.requestModalSmall()">
          url.requestModal + small
        </button>
        <button class="new" onclick="TestModal.requestModalSize()">
          url.requestModal + size
        </button>
        <button class="new" onclick="TestModal.requestModalClose()">
          url.requestModal + onClose
        </button>
        <button class="new" onclick="TestModal.requestModalSizePercent()">
          url.requestModal + size percent
        </button>
        <button class="new" onclick="TestModal.requestModalSizeNegative()">
          url.requestModal + size negative
        </button>
        <button class="new" onclick="TestModal.modalIncrustable()">
          url.requestModal + Incrustable
        </button>

        <br />

        <button class="new" onclick="TestModal.modalOpen()">
          Modal.open
        </button>
        <button class="new" onclick="TestModal.modalConfirm()">
          Modal.confirm
        </button>
        <button class="new" onclick="TestModal.modalAlert()">
          Modal.alert
        </button>
      </fieldset>
    </td>
    <td>
      <fieldset>
        <legend>Popup windows</legend>

        <button class="new" onclick="showModalDialog('?m=dPdeveloppement&a=iframe_test&dialog=1', null, 'dialogHeight:700px;dialogWidth:900px;center:yes;resizable:no;scroll:no;')">showModalDialog</button>
        <button class="new" onclick="open('?m=dPdeveloppement&a=iframe_test&dialog=1', 'test', '')">popup</button>
      </fieldset>
    </td>
  </tr>
</table>

<button class="change" onclick="$$('body')[0].toggleClassName('touchscreen')">Touchscreen</button>
<button class="change" onclick="$$('body')[0].toggleClassName('dyslexic')">dyslexic</button>

<hr />

<table class="main layout">
  <tr>
    <td>
      <h1>header 1</h1>
      <h2>header 2</h2>
      <h3>header 3</h3>
    </td>
    <td>
      <h2>Test opacity &lt;div&gt;</h2>
      <div>
        <div class="opacity opacity-0">0</div>
        <div class="opacity opacity-10">10</div>
        <div class="opacity opacity-20">20</div>
        <div class="opacity opacity-30">30</div>
        <div class="opacity opacity-40">40</div>
        <div class="opacity opacity-50">50</div>
        <div class="opacity opacity-60">60</div>
        <div class="opacity opacity-70">70</div>
        <div class="opacity opacity-80">80</div>
        <div class="opacity opacity-100">100</div>
      </div>
    </td>
    <td>
      <h2>Test opacity &lt;table&gt;</h2>
      <table>
        <tr class="opacity opacity-0"><td>0</td><th>0</th></tr>
        <tr class="opacity opacity-10"><td>10</td><th>10</th></tr>
        <tr class="opacity opacity-20"><td>20</td><th>20</th></tr>
        <tr class="opacity opacity-30"><td>30</td><th>30</th></tr>
        <tr class="opacity opacity-40"><td>40</td><th>40</th></tr>
        <tr class="opacity opacity-50"><td>50</td><th>50</th></tr>
        <tr class="opacity opacity-60"><td>60</td><th>60</th></tr>
        <tr class="opacity opacity-70"><td>70</td><th>70</th></tr>
        <tr class="opacity opacity-80"><td>80</td><th>80</th></tr>
        <tr class="opacity opacity-90"><td>90</td><th>90</th></tr>
        <tr class="opacity opacity-100"><td>100</td><th>100</th></tr>
      </table>
    </td>
  </tr>
</table>

<ul class="control_tabs">
  <li><a href="#tab1">normal</a></li>
  <li><a href="#tab2" class="active">active</a></li>
  <li><a href="#tab3" class="empty">empty</a></li>
  <li><a href="#tab4" class="empty active">empty active</a></li>
  <li><a href="#tab5" class="wrong">wrong</a></li>
  <li><a href="#tab6" class="wrong active">wrong active</a></li>
  <li><a href="#tab7" class="special">special</a></li>
  <li><a href="#tab8" class="special active">special active</a></li>
</ul>

<ul class="control_tabs small">
  <li><a href="#tab1">normal</a></li>
  <li><a href="#tab2" class="active">active</a></li>
  <li><a href="#tab3" class="empty">empty</a></li>
  <li><a href="#tab4" class="empty active">empty active</a></li>
  <li><a href="#tab5" class="wrong">wrong</a></li>
  <li><a href="#tab6" class="wrong active">wrong active</a></li>
  <li><a href="#tab7" class="special">special</a></li>
  <li><a href="#tab8" class="special active">special active</a></li>
</ul>

<table class="main">
  <tr>
    <td class="narrow">
      <ul class="control_tabs_vertical">
        <li><a href="#tab1">normal</a></li>
        <li><a href="#tab2" class="active">active</a></li>
        <li><a href="#tab3" class="empty">empty</a></li>
        <li><a href="#tab4" class="empty active">empty active</a></li>
        <li><a href="#tab5" class="wrong">wrong</a></li>
        <li><a href="#tab6" class="wrong active">wrong active</a></li>
        <li><a href="#tab7" class="special">special</a></li>
        <li><a href="#tab8" class="special active">special active</a></li>
      </ul>
    </td>
    <td class="narrow">
      <ul class="control_tabs_vertical small">
        <li><a href="#tab1">normal</a></li>
        <li><a href="#tab2" class="active">active</a></li>
        <li><a href="#tab3" class="empty">empty</a></li>
        <li><a href="#tab4" class="empty active">empty active</a></li>
        <li><a href="#tab5" class="wrong">wrong</a></li>
        <li><a href="#tab6" class="wrong active">wrong active</a></li>
        <li><a href="#tab7" class="special">special</a></li>
        <li><a href="#tab8" class="special active">special active</a></li>
      </ul>

    </td>
    <td class="narrow">

      <table style="width: 0.1%;" id="buttons">
        <tr>
          <td>
            {{foreach from=$button_classes item=_class}}
              <button class="{{$_class}} notext" title="button.{{$_class}} notext">{{$_class}}</button><br />
            {{/foreach}}
          </td>
          <td>
            {{foreach from=$button_classes item=_class}}
              <button class="{{$_class}}" title="button.{{$_class}}">{{$_class}}</button><br />
            {{/foreach}}
          </td>
          <td style="text-align:right;">
            {{foreach from=$button_classes item=_class}}
              <button class="{{$_class}} rtl" title="button.{{$_class}} rtl">{{$_class}}</button><br />
            {{/foreach}}
          </td>
          <td>
            {{foreach from=$button_classes item=_class}}
              <a href="#1" class="button {{$_class}} notext" title="a.button {{$_class}} notext">{{$_class}}</a><br />
            {{/foreach}}
          </td>
          <td>
            {{foreach from=$button_classes item=_class}}
              <a href="#1" class="button {{$_class}}" title="a.button {{$_class}}">{{$_class}}</a><br />
            {{/foreach}}
          </td>
          <td style="text-align:right;">
            {{foreach from=$button_classes item=_class}}
              <a href="#1" class="button {{$_class}} rtl" title="a.button {{$_class}} rtl">{{$_class}}</a><br />
            {{/foreach}}
          </td>
        </tr>
      </table>

    </td>
    <td>

      <table class="tbl">
        <tr>
          <th class="title" colspan="5">Title 1</th>
        </tr>
        <tr>
          <th>Title 1</th>
          <th>Title 2</th>
          <th>Title 3</th>
          <th>Title 4</th>
          <th class="disabled">disabled</th>
        </tr>
        <tr >
          <td class="highlight">highlight</td>
          <td class="ok">ok</td>
          <td class="warning">warning</td>
          <td class="error">error</td>
          <td class="disabled">disabled</td>
        </tr>
        <tr>
          <td colspan="5" class="empty">
            empty
          </td>
        </tr>
        <tr>
          <td>Cell 1 - 1</td>
          <td>Cell 1 - 2</td>
          <td>Cell 1 - 3</td>
          <td>Cell 1 - 4</td>
          <td>Cell 1 - 5</td>
        </tr>
        <tr>
          <td>Cell 2 - 1</td>
          <td colspan="2">Cell 2 - 2-3</td>
          <td>Cell 2 - 4</td>
          <td>Cell 2 - 5</td>
        </tr>
        <tr>
          <th colspan="5" class="section">Section normale</th>
        </tr>
        <tr>
          <td colspan="5" class="text">
            <p>
              Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Sed non risus. Suspendisse lectus tortor, dignissim sit amet, adipiscing nec, ultricies sed, dolor. Cras elementum ultrices diam. Maecenas ligula massa, varius a, semper congue, euismod non, mi. Proin porttitor, orci nec nonummy molestie, enim est eleifend mi, non fermentum diam nisl sit amet erat. Duis semper. Duis arcu massa, scelerisque vitae, consequat in, pretium a, enim. Pellentesque congue. Ut in risus volutpat libero pharetra tempor. Cras vestibulum bibendum augue. Praesent egestas leo in pede.
            </p>
          </td>
        </tr>
        <tr>
          <th colspan="5" class="section">Section compacte</th>
        </tr>
        <tr>
          <td colspan="5" class="text compact">
            <p>
              Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Sed non risus. Suspendisse lectus tortor, dignissim sit amet, adipiscing nec, ultricies sed, dolor. Cras elementum ultrices diam. Maecenas ligula massa, varius a, semper congue, euismod non, mi. Proin porttitor, orci nec nonummy molestie, enim est eleifend mi, non fermentum diam nisl sit amet erat. Duis semper. Duis arcu massa, scelerisque vitae, consequat in, pretium a, enim. Pellentesque congue. Ut in risus volutpat libero pharetra tempor. Cras vestibulum bibendum augue. Praesent egestas leo in pede.
            </p>
          </td>
        </tr>
        <tr>
          <td>Cell 4 - 1</td>
          <td>Cell 4 - 2</td>
          <td>Cell 4 - 3</td>
          <td>Cell 4 - 4</td>
          <td>Cell 4 - 5</td>
        </tr>

        <tr>
          <th class="title">
            Test title
          </th>
          <th class="title">
            <div class="small-error">small-error</div>
          </th>
          <th class="title">
            <div class="small-warning">small-warning</div>
          </th>
          <th class="title">
            <div class="small-info">small-info</div>
          </th>
          <th class="title">
            <div class="small-success">small-success</div>
          </th>
        </tr>

        <tr>
          <th class="category">
            Test title
          </th>
          <th class="category">
            <div class="small-error">small-error</div>
          </th>
          <th class="category">
            <div class="small-warning">small-warning</div>
          </th>
          <th class="category">
            <div class="small-info">small-info</div>
          </th>
          <th class="category">
            <div class="small-success">small-success</div>
          </th>
        </tr>

        <tr>
          <th class="section">
            Test title
          </th>
          <th class="section">
            <div class="small-error">small-error</div>
          </th>
          <th class="section">
            <div class="small-warning">small-warning</div>
          </th>
          <th class="section">
            <div class="small-info">small-info</div>
          </th>
          <th class="section">
            <div class="small-success">small-success</div>
          </th>
        </tr>
      </table>

      <br />

      <form action="?" name="test" method="post" onsubmit="return false">
        <table class="form">
          <tr>
            <th class="title" colspan="4">Title 1</th>
          </tr>
          <tr>
            <th class="category" colspan="2">Category 1</th>
            <th class="category" colspan="2">Category 2</th>
          </tr>
          <tr>
            <th>
              <label class="notNull">Title 1</label>
            </th>
            <td>
              <input name="text_foo" type="text" value="text" /><br />
              <input name="text_autocomplete" type="text" value="text" class="autocomplete" /><br />
              <input name="password_foo" type="password" value="password" />
            </td>
            <th rowspan="2">
              <label>Title 2</label>
            </th>
            <td rowspan="2">
              <input type="hidden" class="date" name="dateInline" />
            </td>
          </tr>
          <tr>
            <th>
              <label class="canNull">Title 3</label>
            </th>
            <td>
              <textarea name="textarea_foo"></textarea>
            </td>
          </tr>
          <tr>
            <th>
              <label class="notNullOK">Title 5</label>
            </th>
            <td>
              <select name="select_foo">
                <option style="background: url(./images/icons/cancel.png)">Option 1</option>
                <option value="1">Option 2</option>
                <option value="2">Option 3</option>
                <optgroup label="Optgroup 1">
                  <option value="3">Option 4</option>
                  <option value="4">Option 5</option>
                </optgroup>
              </select>
            </td>
            <th>
              <label>Title 6</label>
            </th>
            <td>
              <input type="file" name="file" />
            </td>
          </tr>
          <tr>
            <th>
              <label>Title 7</label>
            </th>
            <td>
              <input type="hidden" class="dateTime" name="dateTime" value="{{$dtnow}}" />
            </td>
            <th>
              <label>Title 8</label>
            </th>
            <td>
              <input type="hidden" class="time" name="time" value="{{$tnow}}" />
            </td>
          </tr>
          <tr>
            <th>
              <label>Title 7</label>
            </th>
            <td>
              <input type="hidden" class="date" name="date" value="{{$dnow}}" />
            </td>
            <th>
              <label>Title 8</label>
            </th>
            <td>
              <label for="checkbox_1"> 1 </label><input type="checkbox" name="checkbox" value="1" />
              <label for="checkbox_2"> 2 </label><input type="checkbox" name="checkbox" value="2" />
              <br />
              <label for="radio_1"> 1 </label><input type="radio" name="radio" value="1" />
              <label for="radio_2"> 2 </label><input type="radio" name="radio" value="2" />
            </td>
          </tr>

          <tr>
            <th>
              <label>Title 7</label>
            </th>
            <td>
              <input type="hidden" class="date" name="progressiveDate" value="{{$dnow}}" />
            </td>
            <th>
              <label>Title 8</label>
            </th>
            <td>

            </td>
          </tr>

          <tr>
            <td colspan="10">
              <button class="tick" type="button">button</button>
              <a class="button tick">a.button</a>
              <input type="checkbox" />
              <input type="radio" />
              <input type="text" />
              <select>
                <option>select</option>
              </select>
            </td>
          </tr>
          <tr>
            <td class="button" colspan="4">
              <button class="submit oneclick" type="submit">{{tr}}Save{{/tr}}</button>
              <button class="trash" type="button">{{tr}}Remove{{/tr}}</button>
            </td>
          </tr>
        </table>
      </form>

      <div style="font-size: 1.5em;">
        Font Awesome :
        <i class="fa fa-search"></i>
        <i class="fas fa-glass-martini"></i>
        <button class="fas fa-glass-martini"></button>
        <a class="button fas fa-glass-martini"></a>
        <a class="button fas fa-glass-martini notext"></a>
        ...

        <br />

        WebFont Medical Icons :
        <i class="icon-alternative-complementary"></i>
        <i class="icon-i-anesthesia"></i>
        ...
      </div>
    </td>
    <td>

      <div class="small-error">small-error</div>
      <div class="small-warning">small-warning</div>
      <div class="small-info">small-info</div>
      <div class="small-success">small-success</div>
      <div class="small-mail">small-mail</div>

      <div class="big-error">big-error</div>
      <div class="big-warning">big-warning</div>
      <div class="big-info">big-info</div>
      <div class="big-success">big-success</div>

      <div class="error">error</div>
      <div class="warning">warning</div>
      <div class="info">message</div>
      <div class="success">success</div>
      <div class="loading">loading</div>

    </td>
  </tr>
</table>

<div id="tooltip-container"></div>
