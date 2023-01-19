<?xml version="1.0" encoding="UTF-8"?>
<!-- 
    legalAuthenticatorTime.sch :
    Contenu :
        Règles de contrôle de la date et heure d'endossement du document par le responsabe  
    Paramètres d'appel :
        néant
    Historique :
        05/06/11 : FMY ASIP/PRAS : Création
-->
<pattern xmlns="http://purl.oclc.org/dsdl/schematron" id="legalAuthenticatorTime" is-a="IVL_TS">
    <p>Conformité de la date et heure d'endossement par le responsable du document, nullFlavor autorisé</p>
    <param name="elt" value="cda:legalAuthenticator/cda:time"/>
    <param name="vue_elt" value="'legalAuthenticator/time'"/>
    <param name="nullFlavor" value="1"/>
</pattern>