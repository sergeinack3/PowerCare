/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

verifIntubDifficileAndSave = function(oForm, intubation_auto){
  if (intubation_auto == '1') {
    // Avertissement d'intubation difficile
    var intubation_difficile = ($V(oForm.intub_difficile) == 1) ||
      ((oForm.mallampati[2].checked ||
        oForm.mallampati[3].checked ||
        oForm.bouche[0].checked ||
        oForm.bouche[1].checked ||
        oForm.bouche_enfant[0].checked ||
        oForm.mob_cervicale[0].checked ||
        oForm.mob_cervicale[1].checked ||
        oForm.distThyro[0].checked ||
        oForm.tourCou[0].checked) &&
        $V(oForm.intub_difficile) != '0');
    var div = $('divAlertIntubDiff');

    div.show();

    // Possibilité de dire qu'elle ne l'est pas.
    if (intubation_difficile) {
      div.setStyle({color: "#f00"});
      div.update("<strong>"+$T("CConsultAnesth-_intub_difficile")+"</strong>");
      div.previous("button#force_difficile").hide();
      div.previous("button#force_pas_difficile").show();
    }
    // Sinon affichage en grisé, et possibilité de dire
    // qu'elle l'est en réalité
    else {
      div.setStyle({color: "#000"});
      div.update($T("CConsultAnesth-_intub_pas_difficile"));
      div.previous("button#force_difficile").show();
      div.previous("button#force_pas_difficile").hide();
    }
  }

  onSubmitFormAjax(oForm);

  if (oForm.mallampati) {
    for (var i = 0; i < 4; i++) {
      var o = oForm.mallampati[i];
      var bg = $('mallampati_bg_classe' + (i + 1));
      bg.setClassName('mallampati-selected', o.checked);
    }

    var bg_no_eval = $('mallampati_bg_no_eval');
    bg_no_eval.setClassName('mallampati-selected', oForm.mallampati[4].checked);
  }
};

resetIntubation = function(form) {
  var fields = ["mallampati", "bouche", "distThyro", "risque_intub", "tourCou", "mob_cervicale", "etatBucco", "conclusion"];
  fields.each(function(f){
    $V(form[f], '');
  });
  $V(form.intub_difficile, '');
  verifIntubDifficileAndSave(form);
};

SchemaDentaire = {
  sId: null,
  oListEtats: null,
  aStates: null,
  fAlpha: 0.5,
  iSelectedDent: null,
  aDentsId: null,
  sPaint: null,
  aDentsNumbers: [
    // Adulte
    10,
    11, 12, 13, 14, 15, 16, 17, 18, // haut droite
    21, 22, 23, 24, 25, 26, 27, 28, // haut gauche
    30,
    31, 32, 33, 34, 35, 36, 37, 38, // bas gauche
    41, 42, 43, 44, 45, 46, 47, 48, // bas droite
    
    // Enfant
    50,
    51, 52, 53, 54, 55, // haut droite
    61, 62, 63, 64, 65, // haut gauche
    70,
    71, 72, 73, 74, 75, // bas gauche
    81, 82, 83, 84, 85  // bas droite
  ],
  
  initialize: function(id, states) {
    // Class attributes
    this.sId = id;
    this.aStates = states;
    
    // Elements
    var oSchema = $(this.sId),
        oMap = $(this.sId+"-map"),
        oImage = $(this.sId+"-image");
        
    oSchema.addClassName('schema-dentaire');
    
    if (true || Prototype.Browser.Gecko || Prototype.Browser.WebKit) {
    // Clone the image's size to the container
    var img = new Image();
    img.src = oImage.src;

    if (img.width != 0) {
      oSchema.setStyle({width: img.width+'px'});
    } else {
      oSchema.setStyle({width: '407px'});
    }
    
    // Menu initialization
    var oMenu   = DOM.div({id: this.sId+'-menu',   className: 'dent-menu'}),
        oLegend = DOM.div({id: this.sId+'-legend', className: 'dent-legend', style: 'height: 75px'});
    
    // Buttons initialization
    var oActions = DOM.div({className: 'dent-buttons not-printable'}),
        oButton  = DOM.a({href: '#1', className: 'button cancel me-tertiary'}, $T('Reset')).observe('click', this.reset.bindAsEventListener(this));
    oActions.insert({top: oButton});
    
    // For each possible state, we add a link in the menu and an item in the legend
    var oClose = DOM.a({className: 'cancel'}, 'x').observe('click', this.closeMenu.bindAsEventListener(this));
    oMenu.insert({bottom: oClose});
    
    // Options and legend items
    states.each (function (o) {
      var oOption = DOM.a();
      var className = o || 'none',
          label = $T("CEtatDent.etat."+o);
      
      var oItem = DOM.a({href: '#1', style: 'display: block;', className: className}, label)
        .observe('click', (function(){
          this.setPaint(className);
        }).bindAsEventListener(this));
      
      oLegend.insert({bottom: oItem});

      oOption.addClassName(className).update(label);
        
      oOption.observe('click', this.onSelectState.bindAsEventListener(this));
      oMenu.insert({bottom: oOption});
    }, this);
    
    oSchema.insert({bottom: oMenu.hide()})
           .insert({top: oLegend})
           .insert({top: oActions});
    
    this.aDentsId = [];
    
    /* For each area in the map */
    oMap.childElements().each(
      function (o) {
        // We parse the coords attribute to get coordinates and radius of the circle area
        var area = o.coords.split(','),
            x = parseInt(area[0]),
            y = parseInt(area[1]),
            r = parseInt(area[2]);
        
        // New div for the tooth
        var oDent = DOM.div({className: 'dent'});
        oDent.setStyle({
          top: y-r+'px',
          left: x-r+'px',
          width: r*2+'px',
          height: r*2+'px'
        });
        oSchema.insert({top: oDent});
        
        var id = parseInt(o.id.substr(5)), etat;
        oDent.id = this.sId+'-dent-'+id;
        oDent.dentId = id;
        this.aDentsId.push(oDent.id);
        
        if (etat = this.oListEtats[oDent.dentId]) {
          this.setState(oDent.dentId, etat, true);
        }
        
        // Callbacks on the tooth
        oDent.observe('click', this.onClick.bindAsEventListener(this));
      }
    , this);
    } else {
      oSchema.innerHTML = '' + oSchema.innerHTML;
    }
  },
  
  setPaint: function(state) {
    var legend = $('dents-schema-legend');
    legend.childElements().invoke('removeClassName', 'active');
    if (this.sPaint != state) {
      this.sPaint = state;
      legend.down('.'+state).addClassName('active');
    }
    else {
      this.sPaint = null;
    }
  },
  
  getDent: function (id) {
    return $(this.sId+'-dent-'+id);
  },
  
  // Change the state of a tooth
  setState: function (id, state, displayOnly) {
    var dent = this.getDent(id);
    if (dent) {
      dent.setOpacity(this.fAlpha);
      dent.className = 'dent';
      
      if (state)
        dent.addClassName(state);
      else
        dent.setOpacity(1);
      
      if (!displayOnly) {
        var oForm = document.forms['etat-dent-edit'];
        if (oForm) {
          $V(oForm.dent, id);
          $V(oForm.etat, (((state != 'none') && state) ? state : ''));
          onSubmitFormAjax(oForm);
        }
      }
    }
  },
  
  // Show the menu
  onClick: function (e) {
    var dent = e.element(),
        menu = $(this.sId+'-menu');

    if (!this.sPaint) {
      if (this.iSelectedDent) {
        this.getDent(this.iSelectedDent).removeClassName('focus');
      }
      dent.addClassName('focus');
      menu.setStyle({
       top: dent.getStyle('top'),
       left: (parseInt(dent.getStyle('left')) + dent.getWidth() + 4) + 'px'
      }).show();
      
      this.iSelectedDent = dent.dentId;
    }
    else {
      this.setState(dent.dentId, this.sPaint);
    }
  },
  
  // Selection of a new state in the menu
  onSelectState: function (e) {
    this.setState(this.iSelectedDent, Event.element(e).className);
    this.closeMenu();
  },
  
    // Close the menu
  closeMenu: function (e) {
    $(this.sId+'-menu').hide();
    this.getDent(this.iSelectedDent).removeClassName('focus');
    this.iSelectedDent = null;
  },
  
  // Reset the teeth state
  reset: function () {
    this.aDentsId.each(function (name, key) {
      key = this.aDentsNumbers[key];
      var oDent = $(name);
      this.aStates.each(function (state) {
        if (oDent.hasClassName(state)) {
          this.setState(key, null);
        }
      }, this);
    }, this);
    return false;
  }
};

assignDataOldConsultAnesth = function (mallampati, bouche, distThyro, tourCou, mob_cervicale, etatBucco, conclusion, use_old_anesth_conclu, risque_ventil, risque_intub, risque_dentaire) {
  var oform = getForm("editFrmIntubation");

  $V(oform.elements.mallampati, mallampati);
  $V(oform.elements.bouche    , bouche);
  $V(oform.elements.distThyro , distThyro);
  $V(oform.elements.tourCou   , tourCou);
  $V(oform.elements.mob_cervicale, mob_cervicale);
  if (use_old_anesth_conclu == 1) {
    $V(oform.elements.etatBucco, etatBucco);
    $V(oform.elements.conclusion, conclusion);
  }

  var oformRsques = getForm("editFrmRiqueIntubation");
  if (oformRsques) {
    $V(oformRsques.risque_ventil, risque_ventil);
    $V(oformRsques.risque_intub, risque_intub);
    $V(oformRsques.risque_dentaire, risque_dentaire);
    verifIntubDifficileAndSave(oformRsques);
  }

  verifIntubDifficileAndSave(oform);
  Control.Modal.close();
};

loadOldConsultsIntubation = function (patient_id, consult_anesth_id) {
  var url = new Url("dPcabinet", "ajax_old_consult_intubation");
  url.addParam("patient_id", patient_id);
  url.addParam("consult_anesth_id", consult_anesth_id);
  url.requestModal("1200px", "70%");
};

Intubation = {
  ras: function(form) {
    $V(form.mallampati, 'classe1');
    $V(form.bouche, 'p35');
    $V(form.distThyro, 'p65');
    $V(form.mob_cervicale, 'p100');
    var name_form = form.name;
    $(name_form+'_mallampati_classe1').onclick();
    $(name_form+'_bouche_p35').onclick();
    $(name_form+'_distThyro_p65').onclick();
    $(name_form+'_mob_cervicale_p100').onclick();
  },
  editScoreCormack: function(button) {
    var form = button.form;
    var inputs = $$('.grade_cormack input[type="radio"]');

    button.hide();
    inputs.each(function(input) {
      input.disabled = false;
      input.on('click', function(event) {
        $$('.cormack-selected').invoke('removeClassName', 'cormack-selected');
        input.up('div').addClassName('cormack-selected');
        verifIntubDifficileAndSave(form);
      });
    });
    form.down('tr.show_com_cormack').hide();
    form.down('tr.edit_com_cormack').show();
  }
};