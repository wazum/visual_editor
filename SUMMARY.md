# SUMMARY – Editara (TYPO3 Extension)

Kurzüberblick
- Zweck: Frontend-orientiertes, strukturiertes Bearbeiten von Inhalten in TYPO3 über „Editables“ und „Template Bricks“ (Renderlets/Areas). Ziel: schnellere Autorenschaft, klare Trennung von Layout und Daten.
- Kompatibilität: TYPO3 v13 (composer require typo3/cms-core ^13.0)
- Extension-Key: editara, Lizenz: GPL-2.0-or-later

Architektur und Komponenten
- Composer/Autoload
  - PSR-4: Andersundsehr\\Editara\\ -> Classes/
- Fluid-Namespace
  - In ext_localconf.php wird der Namespace „e“ auf Andersundsehr\\Editara\\ViewHelpers gemappt.
- Services (Classes/Service)
  - DataHandlerService: Persistierung von Änderungen in DB-Records (wird von Middleware genutzt); setzt u. a. L10N-State für Felder.
  - BrickService, EditableService, RecordService: Hilfslogik rund um Bricks/Editables/Records (Details im Code der Services, Autowiring via Services.yaml aktiv).
- Middleware (Classes/Middleware/EditaraPersistenceMiddleware.php)
  - Eingebunden als Frontend-Request-Middleware (Configuration/RequestMiddlewares.php); Reihenfolge: vor prepare-tsfe-rendering, nach tsfe/page-resolver/adminpanel-logging.
  - Aufgabe: Persistiert übermittelte Änderungen (POST application/json, Query-Param editara). Erwartet JSON mit editable-Daten und schreibt per DataHandlerService in DB.
  - Sicherheit: Erfordert eingeloggten Backend-User mit Seitenzugriff (WebMount) und CONTENT_EDIT-Recht; verweigert falschen Content-Type; prüft Seitenkontext (PageInformation).
  - Lokalisierung: Unterstützt spezielle Kennung __languageSyncUid, um L10N-State für Felder zu setzen.

Datenmodell (TCA)
- Tabelle editable (Configuration/TCA/editable.php)
  - ctrl: label=name, type=type, Sprache (sys_language_uid), l10n_source, enablecolumns (hidden/starttime/endtime), Suche in name/type/value, Icon editable.svg.
  - Typen/Spalten: value (Text), link (type=link), image (FAL, 1:1), rte (Richtext), checkbox, datetime (inputDateTime), number (double), color (colorpicker), video (FAL, Medien inkl. youtube/vimeo), password (technisch vorhanden, evtl. TODO).
  - type-Auswahl: input, link, image, select, rte, checkbox, datetime, number, color, video, password.
  - Relation: template_brick (readOnly) als Zuordnung des Editables zu einem Template Brick.
  - Lokalisierungsverhalten: Bei mehreren Feldern allowLanguageSynchronization aktiviert.
- Tabelle template_brick (Configuration/TCA/template_brick.php)
  - ctrl: label=area_name, label_userFunc=TcaHelper->templateBrickLabel, Sprache (sys_language_uid), l10n_source, enablecolumns, Icon template_brick.svg.
  - Strukturfelder: area_name, template_name (beide mit l10n_mode=exclude).
  - Inline-Relationen:
    - editables: inline zu editable (foreign_field=template_brick, sort by name), Cascading Delete aktiv.
    - children: inline zu template_brick (Self-Relation mit parent_uid, parent_table, parent_sort), Cascading Delete aktiv.
  - Eltern-Referenz: parent_table, parent_uid, parent_sort sind readOnly.

Entwickler-API/Modelle
- DTOs (Classes/Dto): Editable, EditableResult, Input, Link, Image, Checkbox u. a.
- Enum (Classes/Enum): EditableType (Aufzählung der unterstützten Editable-Typen).
- Fluid/Scope: Projektspezifische Fluid-Hilfen.

ViewHelpers (Classes/ViewHelpers)
- Globale: BackendIframeViewHelper, BlocksViewHelper, BricksViewHelper, EditmodeViewHelper.
- Editables: Editable/InputViewHelper, ImageViewHelper, LinkViewHelper, CheckboxViewHelper.
- Nutzung via Namespace „e“ in Fluid-Templates.

Templates & Ressourcen
- Private Templates (Resources/Private)
  - Bricks/teaser.html – Beispiel-„Brick“-Template.
  - PageView/Pages/Default.html – Seitentemplate.
  - TemplateOverrides/Partials/PageLayout/... – Backend-PageLayout-Anpassungen (z. B. Grid/Column, LanguageColumns).
- Öffentliche Assets (Resources/Public)
  - JavaScript: index.js, editable-input.js, editara-save-button.js, reset-button.js, translation-selector.js, changes-store.js, iframe-popup.js, middleFrameScript.js.
  - CSS: editable.css.
  - Icons: editable.svg, template_brick.svg.

JavaScript-Integration
- Import Map (Configuration/JavaScriptModules.php)
  - dependencies: ['backend']
  - imports: '@andersundsehr/editara/' -> EXT:editara/Resources/Public/JavaScript/ (alle *.js import-mapped)

Konfiguration & Sets
- Services (Configuration/Services.yaml)
  - Autowire/Autoconfigure=true, public=false; scan Classes/* (Domain/Model wird ausgeschlossen).
- RequestMiddlewares (Configuration/RequestMiddlewares.php)
  - Registriert die EditaraPersistenceMiddleware im FE-Stack.
- Sets (Configuration/Sets/Editara)
  - config.yaml: Name/Label des Sets (andersundsehr/editara – Editara).
  - setup.typoscript: definiert PAGE pageRenderingEditara (typeNum per {$editara.typenum}), schaltet admin panel an.
  - page.tsconfig: Backend-Template-Override Mapping (templates.typo3/cms-backend) und mod.web_layout.defLangBinding=1.

Ablauf – „Wie speichert Editara?“
1) Benutzer ist als Backend-User eingeloggt und hat Rechte auf der aktuellen Seite.
2) Frontend-JS erfasst Änderungen an Editables und sendet POST (application/json) mit Query-Param editara an die Seite.
3) EditaraPersistenceMiddleware prüft Content-Type, Rechte, Seitenkontext und verarbeitet das JSON.
4) DataHandlerService aktualisiert Datensätze in der Tabelle editable (und setzt bei Bedarf L10N-State).

Weitere Hinweise
- README.md im Projekt enthält zahlreiche TODOs/Ideen für zusätzliche Editables, UX-Verbesserungen und Caching-/Workspace-Aspekte.
- Die Tabellen editable und template_brick sind für strukturierte Inhalte (Felder je Brick, verschachtelte Bricks via children) ausgelegt, inkl. Lokalisierungs- und Sichtbarkeitslogik.
