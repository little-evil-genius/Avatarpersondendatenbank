# Avatarpersondendatenbank
Dieses Plugin erweitert das Board, um eine eigene Avatarpersonendatenbank. Ausgewählte Gruppen können Avatarpersonen hinzufügen, welche vom Team freigegeben werden müssen. Avatarpersonen, welche vom Team hinzugefügt wurden werden automatisch freigeschaltet. User erhalten beim Ablehnen oder Annehmen ein Alert, falls dieses Plugin installiert ist. Beim hinzufügen der Avatarperson werden verschiedene Informationen abgefragt. Unter anderem die festgelegten Angaben Geschlecht, Jahrgang, Herkunft, Haarfarbe und Besonderheiten und die optionalen wie die Mediabase und ein Link zu einer Galerie. Auch bekommt jede Avatarperson ein eigenes Bild, damit man beim stöbern direkt ein Bild vor Augen hat. <br>
<br>
Man hat die Möglichkeit die Avatarpersonen nach verschiedenen Optionen zu filtern. Unter anderem durch ein Filtermenü auf der linken Seite, wo man nach Geschlecht, Herkunft, Haarfarbe und Alterspanne filtern kann. Das Menü baut sich automatisch aus den Angaben in den Einstellungen auf. Nur das Altersspannen sind festgelegt. Solltet ihr das Ändern wollen müsstet ihr in die PHP und es dort ändern. Mit diesem Menü hat man aber nur die Möglichkeit nach einer Option zu filtern sprich, klickt man auf Weiblich wird man auf die Seite mit allen weiblichen Avatarpersonen weitergeleitet. Möchte man nach mehreren Optionen filtern, wie weiblich und blond kann man die Filterseite benutzen. Auf dieser hat man die Möglichkeiten nach mehren Kriterien zu suchen. Auch kann man eine konkrete Altersangabe oder auch nach ganzen Namen suchen. <br>
<br>
Die Altersangabe der Avatarpersonen wird automatisch ermittelt. Beim erstellen von einem Eintrag wird das Geburtsjahr der Person angegeben und dies wird dann mit dem aktuellen Jahr verrechnet. Für Boards mit einer Altersbeschränkung, sprich wo das wahre Alter vom Charakteralter sich nur x Jahre unterscheiden darf gibt es die Möglichkeit diese Beschränkung auch anzugeben. Dafür muss das Team dies in den Einstellung aktivieren und ihre Jahresbegrenzung eintragen. So wird dann auch automatisch durch das Alter der Person das minimal und das maximalste Alter ausgerechnet und angegeben. Diese Begrenzung wird auch bei der Filterung nach dem Alter berücksichtigt.<br>
<br>
Auch kann das Team eine Zufallsseite aktivieren, wo den Usern zufällige Einträge aus der Datenbank angezeigt werden. Wie viele auf dieser Seite angezeigt werden kann auch in den Einstellungen festgehalten werden. Auch kann eingestellt werden, ob vergebene und reservierte Avatarpersonen besonders dargestellt werden. Ich habe mich für eine blassere Darstellung entschieden. Sprich sie werden weiterhin angezeigt in der Auflistung, nur etwas blasser. Die vergebene Avatarpersonen werden über ein Profilfeld abgefragt. Für die Option mit reservierten Avatare benötigt man das Reservierungen aller Art Plugin von risuena (weiter unten verlinkt). Oder eigene PHP Kenntnisse, um die Abfrage umzuschreiben.<br>
<br>
Die einzelnen Seiten, außer die Zufallsseite können als Multipage dargestellt werden. Dies bedeutet, dass die ausgelesenen Avatarpersonen auf mehrere Seite angezeigt werde, wie zB bei der Mitgliederliste. Dies kann nach Belieben vom Team an und ausgestellt werden und auch die Anzahl, wie viele Avatarpersonen pro Seite angezeigt werden kann vom Team bestimmt werden. Ich lege den Teamies diese Option ans Herz, da wenn ihr eine Menge an Einträgen habt und diese alle auf einmal anzeigen lasst, könnte es einige Browser und Internetleitungen ziemlich überfordern.<br>
<br>
Das Bearbeiten von Avatarpersoneneinträgen ist nur dem Team gestattet. Alle Texte sind in der Sprachdatei gespeichert und können dort angepasst werden. Das Design ist sehr simple aufgebaut und kann komplett geändert werden. 

# Datenbank-Änderungen
Hinzugefügte Tabellen:
- PRÄFIX_faceclaims_database

# Neue Templates
- faceclaims_database_add
- faceclaims_database_add_gallery
- faceclaims_database_add_mediabase
- faceclaims_database_edit
- faceclaims_database_faceclaim_all
- faceclaims_database_faceclaim_bit
- faceclaims_database_faceclaim_bit_gallery
- faceclaims_database_faceclaim_bit_mediabase
- faceclaims_database_faceclaim_bit_teamoption
- faceclaims_database_faceclaim_filters
- faceclaims_database_faceclaim_none
- faceclaims_database_filterpage
- faceclaims_database_filterpage_filters
- faceclaims_database_mainpage
- faceclaims_database_menu
- faceclaims_database_menu_cat
- faceclaims_database_modcp
- faceclaims_database_modcp_bit
- faceclaims_database_modcp_nav
- faceclaims_database_randompage

# Template Änderungen - neue Variablen
- header - {$newentry_faceclaims_database}
- modcp_nav_users - {$nav_faceclaims_database}

# ACP-Einstellungen - Avatarpersondendatenbank
- Erlaubte Gruppen
- Avatarperson-Profilfeld
- Schreibweise der Avatarperson
- Geschlechtsmöglichkeiten
- Herkunftsmöglichkeiten
- Haarfarbenmöglichkeiten
- Altersbegrenzung
- Altersbegrenzung - Jahre
- Filme und Serien
- Galerie Link
- Vergebene Avatarpersonen
- Reservierte Avatarpersonen
- Multipage-Navigation
- Anzahl der Avatarpersonen (Multipage-Navigation)
- Zufällige Seite
- Anzahl der Random Avatarpersonen
- Listen PHP (Navigation Ergänzung)

# Neues CSS - faceclaims_database.css
Wird automatisch in jedes bestehende und neue Design hinzugefügt. Man sollte es einfach einmal abspeichern, bevor man im Board mit der <b>Untersuchungsfunktion</b> dies bearbeiten will, da es dann passieren kann, dass das CSS für dieses Plugin in ein anderen Stylesheet gerutscht ist, obwohl es im ACP richtig ist. 

# Voraussetzungen
- Eingebundene Icons von Fontawesome (kann man sonst auch in den Templates ändern)

# Empfehlungen
- <a href="https://github.com/MyBBStuff/MyAlerts" target="_blank">MyAlerts</a> von EuanT
- <a href="https://github.com/katjalennartz/reservations" target="_blank">Reservierungen aller Art</a> von risuena

# Links
- euerforum.de/faceclaims_database.php?action=main
- euerforum.de/faceclaims_database.php?action=add
- euerforum.de/faceclaims_database.php?action=all
- euerforum.de/faceclaims_database.php?action=filter
- euerforum.de/faceclaims_database.php?action=random
- euerforum.de/faceclaims_database.php?filters=xxx
- euerforum.de/modcp.php?action=faceclaims_database

# Demo
Hauptmenü<p>
<img src="https://stormborn.at/plugins/avatarpersonendatenbank_menu.png">

(Default-) Filtermenü<p>
<img src="https://stormborn.at/plugins/avatarpersonendatenbank_filtermenu.png">

Maske beim Hinzufügen<p>
<img src="https://stormborn.at/plugins/avatarpersonendatenbank_add.png">

Anzeige der Avatare - normal und einmal vergeben (Emma Roberts)<p>
<img src="https://stormborn.at/plugins/avatarpersonendatenbank_anzeige.png">

Filter Maske auf der Filterseite<p>
<img src="https://stormborn.at/plugins/avatarpersonendatenbank_filter.png">

Teamhinweis <p>
<img src="https://stormborn.at/plugins/avatarpersonendatenbank_alert.png">

Modcp<p>
<img src="https://stormborn.at/plugins/avatarpersonendatenbank_modcp.png">

MyAlerts Benachrichtigung</p>
<img src="https://stormborn.at/plugins/avatarpersonendatenbank_alerts.png">
