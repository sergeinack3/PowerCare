<?xml version="1.0" encoding="UTF-8"?>
<!-- 
    personName.sch :
    Contenu :
        Contrôle d'un nom de personne  
    Paramètres d'appel :
        $elt : élément nom de personne
        $vue_elt : chemin de l'élément pour affichage dans le rapport d'erreur
        $nullFlavor (0/1) : nullFlavor autorisé (1) ou non (0)
    Historique :
    05/06/11 : FMY ASIP/PRAS : Création
-->
<pattern xmlns="http://purl.oclc.org/dsdl/schematron" id="personName" abstract="true">
    <rule context="$elt">
        <assert test="(
                        (name(@*) = 'nullFlavor' and $nullFlavor and
                           (@* = 'UNK' or @* = 'NASK' or @* = 'ASKU' or @* = 'NAV' or @* = 'MSK')) or
                        ((./cda:family) and
                       ((./cda:family[@qualifier='BR' or @qualifier='SP' or @qualifier='CL']) or not(./cda:family[@qualifier])))
                    )">
            Erreur de conformité CI-SIS : L'élément <value-of select="$vue_elt"/>/family doit être présent 
            avec un attribut qualifier valorisé dans : BR (nom de famille), SP (nom d'usage) ou CL (pseudonyme)
            ou sans attribut qualifier. Valeur trouvée pour family : <value-of select="./cda:family"/>. Valeur trouvée pour family@qualifier : <value-of select="./cda:family/@qualifier"/>
        </assert>
    </rule>
</pattern>