## Inhaltsübersicht

* [Einleitung](#einleitung)
  - [Über das AddOn](#ueber-das-addon)
  - [Features](#features)
  - [Bug-Meldungen, Hilfe und Links](#bug-help-links)
  - [Installation](#installation)
* [Einstellungen](#einstellungen)
* [Server-Plugin](#server-plugin)
  - [Server](#server)
  - [Sync-Cronjob](#status-cronjob)

<a name="einleitung"></a>
## Einleitung

<a name="ueber-das-addon"></a>
### Über das Addon

Dieses Addon bietet Unterstützung bei der Verwaltung und Überprüfung der eigenen REDAXO-Installationen. 

&uarr; [zurück zur Übersicht](#top)

<a name="features"></a>
### Features

Die Client-Funktion ist für den Abruf der einzelnen Parameter gedacht.

* Hinterlegen eines API-Keys in den Hello-Einstellungen
* Abruf von Parametern der Installation, z.B. 
** Eingesetzte PHP-Version
** Aktuelle PHP-Version
** Installierte und updatefähige REDAXO-Addons
** Verwendete YRewrite-Domains
** Letzte Meldungen aus dem Syslog
** weiter geplant: Letzte Änderungen, Medienpool-Verzeichnisgröße, Backup-Status, PageSpeed-Status, Fehlerseiten-Status, 
** weiter geplant: EXTENSION_POINT, um eigene Prüfregeln zu hinterlegen

Das Server-Plugin dient zum Abruf dieser Parameter
* Cronjob zum automatisierten Abruf aller Parameter
* geplant: Verwaltung der REDAXO-Projekte und deren API-Keys
* geplant: Darstellung der wichtigsten Parameter und ggf. Fehler einer REDAXO-Installation

&uarr; [zurück zur Übersicht](#top)

<a name="bug-help-links"></a>
### Bug-Meldungen, Hilfe und Links

* Auf Github: https://github.com/alexplusde/hello/issues/
* im Forum: https://www.redaxo.org/forum/
* im Slack-Channel: https://friendsofredaxo.slack.com/

&uarr; [zurück zur Übersicht](#top)

<a name="installation"></a>
### Installation

Voraussetzung für die aktuelle Version des Hello-Addons: REDAXO 5.3, Cronjob-Addon, MarkItUp-Addon
Beim Installieren und Aktivieren des Addons wird automatisch ein API-Key eingerichtet.
Nach erfolgreicher Installation gibt es im Backend unter AddOns einen Eintrag "HELLO".

&uarr; [zurück zur Übersicht](#top)

<a name="einstellungen"></a>
## Einstellungen

### Übersicht

Unter dem Reiter **Einstellungen** lässt sich ein API-Key hinterlegen. Bei der Installation wird automatisch ein API-Key voreingestellt. Anschließend lassen sich die Parameter über die URL abrufen:

```
http://www.domain.de/?rex-api-call=hello&api_key=<api_key>
```

&uarr; [zurück zur Übersicht](#top)

<a name="server-plugin"></a>
## Server-PlugIn

<a name="projekte-server"></a>
### Server

Unter dem Reiter **Server** werden REDAXO-Installationen verwaltet.

Die einzelnen Felder sind:

* Website (Domain aus dem System oder Domain des YRewrite-Projekts, z.B. `domain.de`)
* API-Key

Außerdem werden in Logs der Status festgehalten.

<a name="status-cronjob"></a>
### Status-Cronjob

Der Status-Cronjob kann sich mit externen REDAXO-Installationen verbinden und deren Status abrufen.

&uarr; [zurück zur Übersicht](#top)
