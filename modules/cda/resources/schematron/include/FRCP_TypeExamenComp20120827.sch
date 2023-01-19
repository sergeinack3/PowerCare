<?xml version="1.0" encoding="UTF-8"?>
<!-- 
    FRCP_carePlan20120827.sch :
    Contenu :
    Contrôle du code du niveau diagnostique d'une entrée Problem Entry (nullFlavor interdit)
        Spécialisation du pattern dansJeuDeValeurs
    Paramètres d'appel :
        Néant 
    Historique :
        27/06/11 : CRI ASIP/PRAS : Création
-->
<pattern xmlns="http://purl.oclc.org/dsdl/schematron" id="FRCP_TypeExamenComp" is-a="dansJeuDeValeurs">
    <p>Conformité des demandes d'examen complémentaires</p>
    <param name="path_jdv" value="$jdv_FRCP_TypeExamenComp"/>
    <param name="vue_elt" value="ClinicalDocument/component/structuredBody/component/section/entry/observation/code"/>
    <param name="xpath_elt" value="/cda:ClinicalDocument/cda:component/cda:structuredBody/cda:component/cda:section[cda:templateId/@root='1.3.6.1.4.1.19376.1.5.3.1.3.31']/cda:entry/cda:observation/cda:code"/>
    <param name="nullFlavor" value="0"/>
</pattern>   

