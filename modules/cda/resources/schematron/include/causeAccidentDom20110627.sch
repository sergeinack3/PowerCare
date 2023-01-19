<?xml version="1.0" encoding="UTF-8"?>
<!-- 
    causeAccidentDom.sch :
    Contenu :
    Contrôle du code du type d'accidents survenus chez un enfant durant la période périnatale dans la section History of past illness  (nullFlavor interdit)
        Spécialisation du pattern dansJeuDeValeurs
    Paramètres d'appel :
        Néant 
    Historique :
        27/06/11 : CRI ASIP/PRAS : Création
-->
<pattern xmlns="http://purl.oclc.org/dsdl/schematron" id="causeAccidentDom" is-a="dansJeuDeValeurs">
    <p>Conformité de la catégorie professionnelle de la personne au CI-SIS</p>
    <param name="path_jdv" value="$jdv_causeAccidentDom"/>
    <param name="vue_elt" value="/ClinicalDocument/component/structuredBody/component/section/entry/act/entryRelationship/observation/entryRelationship/observation/code"/>
    <!--  
    <param name="xpath_elt" value="/cda:ClinicalDocument/cda:component/cda:structuredBody/cda:component/cda:section/cda:entry/cda:act/cda:entryRelationship/cda:observation[cda:templateId/@root='1.3.6.1.4.1.19376.1.5.3.1.4.5' and cda:code/@code='XX-MCH142']/cda:entryRelationship/cda:observation/cda:code"/>
    -->
    <param name="xpath_elt" value="/cda:ClinicalDocument/cda:component/cda:structuredBody/cda:component/cda:section/cda:entry/cda:act/cda:entryRelationship/cda:observation[cda:templateId/@root='1.3.6.1.4.1.19376.1.5.3.1.4.5' and cda:value/@code='XX-MCH142']/cda:entryRelationship/cda:observation/cda:value"/>
    <param name="nullFlavor" value="0"/>
</pattern>   

