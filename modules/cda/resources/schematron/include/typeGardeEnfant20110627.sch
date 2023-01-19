<?xml version="1.0" encoding="UTF-8"?>
<!-- 
    typeGardeEnfant.sch :
    Contenu :
    Contrôle du code du type d'établissement utilisé pour la garde d'un enfant de la section Coded Social history  (nullFlavor interdit)
        Spécialisation du pattern dansJeuDeValeurs
    Paramètres d'appel :
        Néant 
    Historique :
        27/06/11 : CRI ASIP/PRAS : Création
-->
<pattern xmlns="http://purl.oclc.org/dsdl/schematron" id="typeGardeEnfant" is-a="dansJeuDeValeurs">
    <p>Conformité de la catégorie professionnelle de la personne au CI-SIS</p>
    <param name="path_jdv" value="$jdv_typeGardeEnfant"/>
    <param name="vue_elt" value="'ClinicalDocument/component/structuredBody/component/section/entry/observation/value'"/>
    <param name="xpath_elt" value="/cda:ClinicalDocument/cda:component/cda:structuredBody/cda:component/cda:section/cda:entry/cda:observation[cda:templateId/@root='1.3.6.1.4.1.19376.1.5.3.1.4.13.4' and cda:code/@code='S-80000']/cda:value"/>
    <param name="nullFlavor" value="0"/>
</pattern>   
