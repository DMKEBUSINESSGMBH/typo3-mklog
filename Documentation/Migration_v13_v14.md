# Migration v13 → v14

Dieses Dokument listet alle signifikanten Änderungen des Upgrades auf TYPO3 14-Unterstützung.
Die Extension unterstützt TYPO3 13.4 und 14. Unter TYPO3 14 werden keine Deprecations mehr ausgelöst (siehe Abschnitt 11).

---

## 1. TYPO3 14 Breaking Changes

Entfernte oder inkompatibel geänderte APIs, die in TYPO3 14 nicht mehr vorhanden sind.

### `BackendModuleController`: `StandaloneView` entfernt

`TYPO3\CMS\Fluid\View\StandaloneView` wurde in TYPO3 13 deprecated und in TYPO3 14 vollständig entfernt.
Der Controller nutzte die Klasse nur noch als Union-Type im Property (`ModuleTemplate|StandaloneView $view`),
obwohl seit TYPO3 13 ausschließlich `ModuleTemplate` über die `ModuleTemplateFactory` erzeugt wird.

- Import `use TYPO3\CMS\Fluid\View\StandaloneView` entfernt
- Property-Typ von `ModuleTemplate|StandaloneView` auf `ModuleTemplate` vereinfacht

### `SchedulerFieldProviderWatchDog`: `getCurrentAction()` entfernt

`SchedulerModuleController::getCurrentAction()` wurde in TYPO3 13 deprecated und in TYPO3 14 entfernt.
Die Methode wurde verwendet, um den Edit-Modus zu erkennen (`if ('edit' == $action)`).

Stattdessen wird geprüft, ob der `$task`-Parameter `null` ist — `null` bedeutet Neu-Anlage,
ein gesetztes Objekt bedeutet Bearbeitung. Diese Prüfung ist kompatibel mit TYPO3 13 und 14.

- `$schedulerModule->getCurrentAction()` entfernt
- Prüfung ersetzt durch `if (null !== $task)`

Gleichzeitig wurden die `@codingStandardsIgnoreStart/End`-Kommentare entfernt (siehe Abschnitt 4).

### `SeverityIconViewHelper`: `Icon::SIZE_SMALL` entfernt

`TYPO3\CMS\Core\Imaging\Icon::SIZE_SMALL` (String-Konstante der alten Icon-API) wurde in TYPO3 14 entfernt.
Ab TYPO3 13 ist der Ersatz `TYPO3\CMS\Core\Imaging\IconSize::SMALL` (Enum-Case).

Die bisherige Verzweigung per `VersionUtility::isTypo3Version12OrLower()` ist für TYPO3 13/14 obsolet.

- `use TYPO3\CMS\Core\Imaging\Icon` entfernt
- `use DMK\Mklog\Utility\VersionUtility` entfernt
- Ternary-Ausdruck durch direkten Aufruf mit `IconSize::SMALL` ersetzt

---

## 2. Versionsdeklarationen

Anpassung der deklarierten Kompatibilität in Metadaten-Dateien.

### `composer.json`: Abhängigkeiten aktualisiert

| Paket | Alt | Neu | Begründung |
|---|---|---|---|
| `typo3/cms-core` | `^v12.4.2 \|\| ^13.4` | `^13.4 \|\| ^14.0` | TYPO3 12 Support eingestellt |
| `typo3/cms-scheduler` | `^v12.4 \|\| ^13.4` | `^13.4 \|\| ^14.0` | wie oben |
| `php` | _(nicht deklariert)_ | `>=8.2` | TYPO3 13 Mindestanforderung explizit gesetzt |
| `doctrine/dbal` | `~2.13.1 \|\| ^3.6 \|\| ^4.0` | `^3.6 \|\| ^4.0` | TYPO3 14 nutzt DBAL 4; DBAL 2 veraltet |
| `symfony/rate-limiter` | `^6.4 \|\| ^7.0` | `^6.4 \|\| ^7.0` | unverändert, TYPO3 13=Symfony 6, 14=Symfony 7 |
| `egulias/email-validator` | `^2.1 \|\| ^3.1 \|\| ^4.0` | `^3.1 \|\| ^4.0` | v2 nicht mehr unterstützt |
| `graylog2/gelf-php` | `~1 \|\| ^2.0` | `^2.0` | v1 veraltet |

**Dev-Abhängigkeiten:**

| Paket | Alt | Neu | Begründung |
|---|---|---|---|
| `phpstan/phpstan` | `^1.1` | `^2.0` | Major-Update; neue Fehlerklassen (siehe Abschnitt 3) |
| `saschaegerer/phpstan-typo3` | `^1.0` | `^2.0` | Unterstützt PHPStan 2 und TYPO3 13/14 |
| `typo3/testing-framework` | `^8.0 \|\| ^9.0` | `^9.0` | PHPUnit 11/12 und TYPO3 13/14 |
| `ssch/typo3-rector` | `^2.8` | `^3.0` | TYPO3-14-Migrationsregeln |

Branch-Alias: `dev-12.4` entfernt, `dev-14.0` ergänzt.

### `ext_emconf.php`: Version und TYPO3-Constraint

- `version`: `13.2.4` → `14.0.0`
- `depends.typo3`: `12.4.0-13.4.99` → `13.4.0-14.4.99`

---

## 3. Aktualisierung der Entwicklungswerkzeuge

Neue Hauptversionen von QS- und Test-Abhängigkeiten, die Breaking Changes in ihrer API mitbringen.

### PHPStan: `^1.1` → `^2.0` / `saschaegerer/phpstan-typo3`: `^1.0` → `^2.0`

PHPStan 2.0 ist strenger bei veralteten Ignore-Kommentaren: Ein `@phpstan-ignore`, das keinen Fehler
mehr unterdrückt, wird selbst als Fehler gewertet. Betroffene Stellen wurden bereinigt (siehe Abschnitt 4).
`saschaegerer/phpstan-typo3` 2.0 unterstützt PHPStan 2 und TYPO3 13/14.

### `typo3/testing-framework`: `^8.0 || ^9.0` → `^9.0`

Testing Framework 9 bringt PHPUnit 12 mit. Version 8 wird nicht mehr benötigt.

### `ssch/typo3-rector`: `^2.8` → `^3.0`

Rector 3 unterstützt TYPO3 14 Migrationsregeln. Version 2 kennt keine TYPO3-14-spezifischen Rektoren.

---

## 4. Bereinigung von Legacy-Code und veralteten Annotationen

Code, der für TYPO3 12 oder ältere Werkzeugversionen eingeführt wurde und nun toter Code ist.

### `ext_tables.php`: TYPO3-12-Fallback entfernt

Seit TYPO3 13 wird `Configuration/page.tsconfig` automatisch geladen. Der bisherige Code:

```php
if (DMK\Mklog\Utility\VersionUtility::isTypo3Version12OrLower()) {
    ExtensionManagementUtility::addPageTSConfig(
        '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:mklog/Configuration/page.tsconfig">'
    );
}
```

war für TYPO3 13/14 bereits ein No-op. Die Datei enthält nur noch den Guard `defined('TYPO3') || exit`.

### `SchedulerFieldProviderWatchDog`: `@codingStandardsIgnoreStart` entfernt

Die drei `@codingStandardsIgnoreStart (interface/abstract mistake)`-Blöcke wurden eingefügt, weil das
TYPO3-Scheduler-Interface `$task` ohne Typangabe deklarierte, was PHP-CS-Fixer bemängelte. Das Interface
wurde in TYPO3 13 nicht geändert, daher bleibt der Parameter weiterhin untypisiert — die Ignores waren
jedoch übermäßig breit (sie schützten die gesamte Methodensignatur). Da die Methoden nun keine anderen
Verstöße mehr enthalten, konnten die Kommentare ersatzlos entfernt werden.

### `AbstractLogger`: Fehlende `@SuppressWarnings(PHPMD.Superglobals)` ergänzt

PHPMD meldete für `createDevlogEntry` einen `Superglobals`-Befund, weil die Methode indirekt über
`Typo3Utility::getTsFe()` und `Typo3Utility::getBeUserId()` auf `$GLOBALS` zugreift. Der Zugriff ist
bewusst und unvermeidbar (TYPO3-Laufzeitkontext), wurde in den aufgerufenen Utility-Methoden aber nicht
als bekannter Befund deklariert. Die Annotation wurde an der aufrufenden Methode ergänzt.

### `SchedulerWatchDog`: Veralteter `@phpstan-ignore-next-line` entfernt

Der Kommentar unterdrückte einen PHPStan-Fehler beim Aufruf `parent::__wakeup()`. In TYPO3 13+
existiert diese Methode in der Elternklasse nicht mehr, sodass `method_exists(parent::class, '__wakeup')`
immer `false` ergibt und der Block nie ausgeführt wird. PHPStan 2.0 meldet nicht mehr unterdrückte
Ignores als Fehler — der Kommentar wurde entfernt.

### `phpstan.neon`: Veraltete Ignore-Patterns entfernt

- `#Undefined variable: \$_EXTKEY#` — wird durch keine analysierte Datei mehr ausgelöst
- `#Attribute class TYPO3\\CMS\\Backend\\Attribute\\Controller does not exist#` — das Attribut
  `AsController` existiert in TYPO3 13+ und löst keinen PHPStan-Fehler mehr aus
- `reportUnmatchedIgnoredErrors: false` — entfernt; PHPStan 2.0 erzwingt das Melden ungenutzter Ignores
  (non-ignorable), die Unterdrückung war kontraproduktiv

---

## 5. PHPUnit 11/12-Kompatibilität

`typo3/testing-framework ^9.0` bringt PHPUnit 12 mit. Mehrere veraltete APIs und falsche Konfigurationen
wurden bereinigt.

### `phpunit.xml`: Schema auf PHPUnit 11 aktualisiert

Die Konfiguration referenzierte noch `https://schema.phpunit.de/9.3/phpunit.xsd`. PHPUnit 12 generierte
daraus eine Deprecation (Exit-Code 1), die den pre-push Hook blockierte.

Entfernte veraltete Attribute: `backupGlobals`, `backupStaticAttributes`, `convertErrorsToExceptions`,
`convertWarningsToExceptions`, `forceCoversAnnotation`, `verbose`.

- Schema: `9.3/phpunit.xsd` → `11.0/phpunit.xsd`
- `cacheResultFile` → `cacheDirectory=".Build/.phpunit.cache"`
- `<coverage processUncoveredFiles>` mit `<include>` → `<source>` auf Root-Ebene

### `AbstractLoggerTest`: `getMockForAbstractClass()` ersetzt

`TestCase::getMockForAbstractClass()` wurde in PHPUnit 11 entfernt. Ersatz ist eine anonyme Klasse,
die die abstrakte Klasse konkret implementiert:

```php
// Alt (PHPUnit ≤10):
$this->abstractLogger = $this->getMockForAbstractClass(AbstractLogger::class);

// Neu (PHPUnit 11+):
$this->abstractLogger = new class extends AbstractLogger {
    public function writeLog(\TYPO3\CMS\Core\Log\LogRecord $record): static
    {
        return $this;
    }
};
```

### `BaseTestCase`: `setAccessible(true)` entfernt

`ReflectionMethod::setAccessible(true)` wurde in PHP 8.1 als no-op deprecated. In PHPUnit 12 erzeugte
jeder Aufruf von `callInaccessibleMethod()` eine PHPUnit Notice, was zu Exit-Code 1 führte. Da PHP 8.1+
Reflection-Zugriff ohne `setAccessible` erlaubt, wurde die Zeile ersatzlos entfernt.

### `DevlogEntryMapperTest` / `EntryDataParserUtilityTest`: Namespace und `#[Test]`-Attribut korrigiert

Beide Testklassen hatten falsche Namespaces, die nicht der PSR-4-Autoloader-Konfiguration entsprachen:

| Datei | Alter Namespace | Neuer Namespace |
|---|---|---|
| `Tests/Classes/Domain/Mapper/DevlogEntryMapperTest.php` | `DMK\Mklog\Domain\Model` | `DMK\Mklog\Tests\Domain\Mapper` |
| `Tests/Classes/Utility/EntryDataParserUtilityTest.php` | `DMK\Mklog\WatchDog` | `DMK\Mklog\Tests\Utility` |

Zudem nutzten beide Klassen ausschließlich die `@test`-Annotation ohne `#[Test]`-Attribut. In PHPUnit 12
wurde die `@test`-Annotation für Methoden ohne `test`-Prefix nicht mehr erkannt — PHPUnit meldete
„No tests found in class" und gab Exit-Code 1 zurück. Das `#[Test]`-Attribut wurde ergänzt, der fehlende
`use`-Import für `DMK\Mklog\Domain\Model\DevlogEntry` in `DevlogEntryMapperTest` wurde ebenfalls ergänzt.

---

## 6. Weitere Fixes

### `LogMessageUtility`: PHP-Warning bei ungültigem Regex unterdrückt

`preg_match()` gibt für ungültige Regex-Muster (z.B. Strings ohne Delimiter wie `wrong`) ein PHP-Warning
aus. Die Testsuite prüft bewusst solche Fälle; das Warning erzeugte Exit-Code 1 im pre-push Hook.

Da das Verhalten korrekt ist (ungültige Muster werden ignoriert → kein Match → `false`), wird das Warning
gezielt unterdrückt:

```php
// Alt:
if (preg_match($regex, $logMessage)) { ... }

// Neu:
if (1 === @preg_match($regex, $logMessage)) { ... }
```

### `DataHandlerTest`: Expliziter `(string)`-Cast ergänzt

Mit `declare(strict_types=1)` erwartet `explode()` zwingend einen `string`. Der Wert
`$dataHandlerParent->copyWhichTables` kann `null` sein — ein expliziter Cast auf `(string)` wurde ergänzt.

### `.gitignore`: `/.phpunit.result.cache` ergänzt

Die PHPUnit-Result-Cache-Datei im Projekt-Root wurde bisher nicht ignoriert. Der neue Cache-Pfad
(`cacheDirectory=".Build/.phpunit.cache"`) liegt innerhalb von `.Build/` und ist bereits abgedeckt;
der veraltete Root-Pfad wurde explizit ignoriert.

---

## 7. Nachgemeldete Fixes (nach initialem Upgrade)

### `locallang_db.xlf`: Verschiebung nach `Resources/Private/Language/`

Die Dateien `locallang_db.xlf` und `de.locallang_db.xlf` lagen bisher im Extension-Root —
ein veraltetes TYPO3-Muster. TYPO3 14's `LabelFileResolver::resolveLocalizedFilePath()` nutzt
`GeneralUtility::revExplode('/', $relativePath, 2)`, das bei Pfaden ohne Verzeichnisanteil
(kein `/`) nur ein Element zurückgibt. Der Destructuring-Ausdruck `[$path, $basename] = ...`
löst dann eine `PHP Warning: Undefined array key 1` aus, die TYPO3s ErrorHandler als Exception
behandelt und den Scheduler- und Maintenance-Modul-Aufruf im Backend abbricht.

**Lösung:**
- `locallang_db.xlf` und `de.locallang_db.xlf` nach `Resources/Private/Language/` verschoben
- LLL-Referenzen in `ext_localconf.php` angepasst:
  `LLL:EXT:mklog/locallang_db.xlf:` → `LLL:EXT:mklog/Resources/Private/Language/locallang_db.xlf:`

### `MailTransportTest`: Mailer-Mock ergänzt

Mit der Umstellung von `$mail->send()` auf `Factory::makeInstance(Mailer::class)->send($mail)`
fehlte in den Tests ein Mock für `Mailer`. Im Test-Environment (TYPO3 13.4, kein DI-Container)
scheitert `GeneralUtility::makeInstance(Mailer::class)` an `TransportFactory::__construct()`,
das zwei DI-Argumente erwartet. Die Tests mocken jetzt zusätzlich den `Mailer` via
`GeneralUtility::addInstance(Mailer::class, ...)`, und der überflüssige `'send'`-Mock auf
`MailMessage` wurde entfernt.

---

## 9. Nachgefundene TYPO3-14-Inkompatibilitäten

Weitere API-Entfernungen und Verhaltensänderungen in TYPO3 14, die erst nach dem initialen Upgrade
beim Aufbau der vollständigen QS-Pipeline (PHPStan, PHPMD, Rector) sichtbar wurden.

### `Typo3Utility::getTsFe()`: `TypoScriptFrontendController` entfernt

`TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController` existiert in TYPO3 14 nicht mehr.
Die statische Methode `getTsFe()`, die bisher das Objekt per `TYPO3_REQUEST`-Attribut `frontend.controller`
zurückgab, wurde entfernt. Sie hatte ausschließlich einen Aufrufer in `AbstractLogger`.

**Alter Code (Typo3Utility.php):**
```php
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

public static function getTsFe(): ?TypoScriptFrontendController
{
    $tsfe = null;
    if ($GLOBALS['TYPO3_REQUEST'] ?? null) {
        $tsfe = $GLOBALS['TYPO3_REQUEST']->getAttribute('frontend.controller');
    }
    return is_object($tsfe) ? $tsfe : null;
}
```

**Neuer Code (AbstractLogger.php):** Direkter Zugriff auf das `frontend.page.information`-Attribut:
```php
$pageInformation = ($GLOBALS['TYPO3_REQUEST'] ?? null)?->getAttribute('frontend.page.information');
if ($pageInformation !== null) {
    $entry->setPid($pageInformation->getId());
}
```

- `getTsFe()` und der `use`-Import von `TypoScriptFrontendController` in `Typo3Utility` entfernt
- `instanceof`-Prüfung in `AbstractLogger::createDevlogEntry()` durch direkten Attributzugriff ersetzt

### `DataHandler::$copyWhichTables`: Eigenschaft entfernt

Die öffentliche Eigenschaft `$copyWhichTables` der TYPO3-`DataHandler`-Klasse existiert in TYPO3 14
nicht mehr. In TYPO3 ≤13 wurde sie im `processCmdmap_preProcess`-Hook gesetzt, um zu verhindern,
dass Logeinträge beim Kopieren einer Seite mitkopiert werden.

In TYPO3 14 baut `DataHandler::copyPages()` die Tabellenliste intern aus der TCA-Schema-Factory auf:

```php
$copyTablesAlongWithPage = $this->BE_USER->isAdmin()
    ? $this->tcaSchemaFactory->all()->getNames()  // ALLE TCA-Tabellen für Admins
    : explode(',', $this->BE_USER->groupData['tables_modify']);
```

Dieser Array ist von außen nicht mehr modifizierbar. Die Lösung: Im `processCmdmap_postProcess`-Hook
werden die bereits kopierten Logeinträge auf der neu erstellten Seite sofort wieder gelöscht.
Die neue Seiten-UID wird über `$dataHandler->copyMappingArray['pages'][$pageId]` ermittelt.

**Alt (`processCmdmap_preProcess`):**
```php
case 'copy':
    $this->removeLogTablesFromTablesThatCanBeCopied($dataHandler);
    break;
// Setzte $dataHandler->copyWhichTables = ... (Property existiert nicht mehr)
```

**Neu (`processCmdmap_postProcess`):**
```php
public function processCmdmap_postProcess($command, $table, int $pageId, $value, $dataHandler): void
{
    if ($table === 'pages' && $command === 'copy') {
        $newPageId = $dataHandler->copyMappingArray['pages'][$pageId] ?? null;
        if ($newPageId !== null) {
            $this->deleteLogEntriesByPageId((int)$newPageId);
        }
    }
}
```

Das Ergebnis ist identisch: Auf der kopierten Seite liegen keine Logeinträge.

### `SchedulerWatchDog`: Veraltete TYPO3-8.7-Kompatibilitätsfelder entfernt

Die privaten Felder `$options` und `$transport` in `SchedulerWatchDog` waren seit TYPO3 9 deprecated
und dienten ausschließlich der Abwärtskompatibilität mit serialisierten Scheduler-Tasks aus TYPO3 8.7.
Da TYPO3 14 keine Migration von Daten vor TYPO3 9 mehr unterstützt, sind die Felder entfernt.

Das `__unserialize()`-Array-Mapping bleibt erhalten, da es die korrekte Deserialisierung bestehender
Scheduler-Task-Datensätze aus der Datenbank sicherstellt.

### `SchedulerFieldProviderWatchDog`: `tx_scheduler_Module` und `AbstractTask::getOptions()`

Zwei veraltete Referenzen korrigiert:

1. `@param tx_scheduler_Module $schedulerModule` — veralteter Klassenname aus der Pre-Namespace-Ära.
   Ersetzt durch `@param SchedulerModuleController $schedulerModule` (bereits der native Typ der Methode).

2. `$task->getOptions()` wurde auf einem `AbstractTask`-Parameter aufgerufen, der diese Methode nicht
   besitzt. `getOptions()` ist exklusiv auf `SchedulerWatchDog` definiert. Beide Aufrufstellen
   (`getAdditionalFields` und `saveAdditionalFields`) prüfen jetzt korrekt via `instanceof SchedulerWatchDog`.

### `SchedulerWatchDog::getLogger()`: Vereinfachung

Die Methode prüfte bisher per `$this->logger->getName()`, ob der injizierte Logger den richtigen
Namen hat — dabei rief sie `getName()` auf `Psr\Log\LoggerInterface` auf, das diese Methode nicht
deklariert. In TYPO3 14 ist der von der `LoggerAwareTrait` injizierte Logger ohnehin korrekt
konfiguriert; der Name-Check ist obsolet.

```php
// Alt: Prüfte Logger-Namen via PSR-3-Interface (getName() existiert dort nicht)
protected function getLogger(): Logger
{
    $name = 'DMK.Mklog.WatchDog.SchedulerWatchDog';
    if ($this->logger->getName() !== $name) {
        $this->logger = Factory::makeInstance(LogManager::class)->getLogger($name);
    }
    return $this->logger;
}

// Neu: Direkte Erzeugung, kein PSR-3-inkompatibles getName()
protected function getLogger(): Logger
{
    return Factory::makeInstance(LogManager::class)->getLogger('DMK.Mklog.WatchDog.SchedulerWatchDog');
}
```

### `DataHandler::removeLogTablesFromTablesThatCanBeCopied()`: PHPMD-UnusedPrivateField entfernt

Im Zuge der `$copyWhichTables`-Migration wurden auch die veralteten
`@SuppressWarnings(PHPMD.UnusedFormalParameter)`-Annotationen bereinigt, die PHPMD zu Unrecht
auf Methoden mit `$dataHandler`-Parameter anwendete. Die Annotations waren notwendig, weil der
Parameter früher nur für den inzwischen entfernten Property-Zugriff genutzt wurde.

---

## 10. PHPStan-Compliance

Aufbau einer vollständigen PHPStan-Analyse (Level 3) ohne Baseline — alle Fehler wurden behoben.

### `saschaegerer/phpstan-typo3`: `^2.0` → `^3.0` / `phpstan/extension-installer` hinzugefügt

`phpstan-typo3 ^2.0` unterstützt nur TYPO3 13. Für TYPO3 14 ist Version `^3.0` erforderlich.
`phpstan/extension-installer` wird ergänzt, damit die Extension automatisch in PHPStan eingebunden wird.

Die Extension liefert TYPO3-spezifische Stubs, die u.a. die globale Konstante `LF` und weitere
TYPO3-Laufzeitkonstanten für PHPStan bekannt machen.

### `phpstan.neon`: `ignoreErrors` für unvermeidliche Ausnahmen

Zwei Fehlerkategorien werden in `phpstan.neon` konfiguriert ignoriert, statt mit einer Baseline
unterdrückt zu werden — mit expliziter Begründung:

```neon
ignoreErrors:
    -
        # PHPMD @SuppressWarnings-Annotations verwenden Punkt-Notation (PHPMD.RuleName),
        # die PHPStan als ungültige PHPDoc-Syntax wertet. Die Annotations sind für PHPMD korrekt.
        identifier: phpDoc.parseError
        message: '#^PHPDoc tag @SuppressWarnings#'
    -
        # GenericArrayObject implementiert __call() für get*/set*/has*/uns*-Accessoren.
        # Alle Aufrufe sind intentional und zur Laufzeit funktional.
        identifier: method.notFound
        message: '#^Call to an undefined method DMK\\Mklog\\Domain\\Model\\GenericArrayObject::#'
```

`packages/*/rector.php` ist von der Analyse ausgeschlossen, da `ssch/typo3-rector` im
Webroot-Projekt nicht installiert ist.

### Korrigierte PHPDoc-Typen

Mehrere `@return`- und `@param`-Annotationen stimmten nicht mit den nativen Typdeklarationen überein:

| Datei | Methode | Alt | Neu |
|---|---|---|---|
| `DevlogEntry.php` | `getPid()` | `@return int\|null` | `@return int` |
| `DevlogEntryRepository.php` | `quoteInArray()` | `@param string $list` | `@param array $list` |
| `GelfLogger.php` | `writeLog()` | `@return WriterInterface` | `@return static` |
| `HttpGelf.php` | `getTransport()` | `@return AbstractTransport` | `@return HttpTransport` |
| `UdpGelf.php` | `getTransport()` | `@return AbstractTransport` | `@return UdpTransport` |
| `SchedulerWatchDog.php` | `getTransportId()` | `@return InterfaceTransport` | `@return string` |
| `DataHandler.php` | `processCmdmap_preProcess()` | `@param \DataHandler` _(ohne Variable)_ | `@param \DataHandler $dataHandler` |
| `DataHandler.php` | `removeLogTablesFromTablesThatCanBeCopied()` | `@param DataHandler` _(ohne Variable)_ | entfällt mit Methodenentfernung |

---

## 8. Code-Qualitätsstandard: `declare(strict_types=1)`

In allen PHP-Dateien der Extension fehlte `declare(strict_types=1)`. Dies ist Standard gemäß
PSR-12 (Extended) und Symfony Coding Standards und vermeidet implizite Typkonvertierungen,
die zu schwer nachvollziehbaren Fehlern führen können.

Betroffen: 31 Dateien — alle Klassen unter `Classes/`, `Configuration/Backend/Modules.php`,
`ext_localconf.php`, `ext_tables.php` sowie `Tests/Fixtures/LocalConfiguration.php`.
Die Datei `CleanupLogTableTask.php` war bereits korrekt und blieb unverändert.

---

## 11. Entfernung aller TYPO3-14-Deprecations (TYPO3 13 bleibt unterstützt)

Alle in TYPO3 14.0–14.3 eingeführten Deprecations, die mklog betreffen, sind entfernt.
Wo TYPO3 13 keine Alternative bietet, entscheidet `VersionUtility::isTypo3Version14OrHigher()`
(mit `@todo` zur Entfernung, sobald TYPO3 13 nicht mehr unterstützt wird).
Die QS-Skripte laufen gegen TYPO3 14.3.9 (Fluid 5) und 13.4.36, jeweils mit PHPUnit 13.

### Scheduler-Tasks als native TCA-Tasktypen (Deprecation #98453)

Die Registrierung über `$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']` und
`AdditionalFieldProviderInterface` sind deprecated.

- TYPO3 14: Registrierung als native Tasktypen in
  `Configuration/TCA/Overrides/scheduler_mklog_watchdog.php` und
  `Configuration/TCA/Overrides/scheduler_mklog_cleanup_log_table.php`
- TYPO3 13: unverändert über `SC_OPTIONS` in `ext_localconf.php` mit `SchedulerFieldProviderWatchDog`
  (nur noch für TYPO3 13 registriert). Die TCA-Overrides sind unter TYPO3 13 wirkungslos.
- Unter TYPO3 14 sind die Optionen Felder in `tx_scheduler_task`:
  `tx_mklog_watchdog_transport`, `_credentials`, `_severity`, `_message_limit`,
  `_extension_whitelist`, `_extension_blacklist`, `_mail_subject`
- `SchedulerWatchDog` implementiert `getTaskParameters()`, `setTaskParameters()` (inkl. Übernahme
  des alten `schedulerOptions`-Werts) und `validateTaskParameters()` (Zugangsdaten passend zum Transport)
- Bugfix: `SchedulerWatchDog::__unserialize()` hat die gemangelten Property-Namen alter, serialisierter
  Tasks (`"\0*\0schedulerOptions"`) nicht erkannt, die Optionen gingen bei der Migration verloren.

**Upgrade auf TYPO3 14:** `typo3 extension:setup` (oder Database Analyzer) und den Scheduler-Upgrade-Wizard ausführen.

### `GeneralUtility::getIndpEnv()` (Deprecation #109551)

Ersetzt durch `NormalizedParams` des aktuellen Requests über neue Helfer in `Typo3Utility`:
`getNormalizedParams()`, `getRequestUrl()` (vorher `TYPO3_REQUEST_URL`) und
`getRequestHostOnly()` (vorher `TYPO3_HOST_ONLY`). Ohne Request (CLI) wird `''` geliefert.

### `ext_tables.php` (Deprecation #109438)

Die (leere) Datei wurde entfernt.

### `ext_emconf.php` (Deprecation #108345)

Version (`extra.typo3/cms.version`) und `extra.typo3/cms.Package.providesPackages`
(`graylog2/gelf-php`) stehen jetzt in `composer.json`. `ext_emconf.php` bleibt nur für TER/Tailor erhalten.

### Backend-Modul

- `parent` bleibt `web`: TYPO3 14 bildet den Alias ohne Deprecation auf `content` ab;
  nicht registrierter `iconIdentifier` entfernt
- Bugfix: Request-Parameter (`severity`, `runId`, `currentPage`, `itemsPerPage`) werden auf `int`
  gecastet; vorher `TypeError` unter `strict_types` beim Filtern und Blättern

### Entfernte Altlasten

- `Configuration/page.tsconfig` (Konfiguration des nicht mehr existierenden Moduls `DevlogModule`)
- `Resources/Private/Templates/BackendModule_11.html`, `Resources/Private/Layouts/Default.html` (TYPO3 11)
- `VersionUtility::isTypo3Version12OrLower()` (ungenutzt), ersetzt durch `isTypo3Version14OrHigher()`
- `SeverityUtility`: typisierte Klassenkonstanten (erst ab PHP 8.3) wieder untypisiert, da PHP 8.2 unterstützt wird
- `DataConverterUtility::encode()` liefert strikt `string` (`JSON_THROW_ON_ERROR`)

### Werkzeuge und Tests

- `rector.php`: `UP_TO_TYPO3_13` (niedrigste unterstützte Version), PHP 8.2, `.Build`/`var` ausgeschlossen, deprecated Rule entfernt
- `.php-cs-fixer.php`: `var` ausgeschlossen; `phpcompatibility`: `testVersion 8.2-`
- Composer: veraltete Optionen (`app-dir`, `cms-package-dir`) entfernt, Abhängigkeiten auf TYPO3-14-Stand
- CI: PHP 8.2–8.5, Unit-Tests gegen TYPO3 `^13.4` und `^14.3`
- Tests für PHPUnit 13 ohne Deprecations/Notices (`any()` und Mocks ohne Erwartungen durch Stubs ersetzt),
  `TransientMemoryBackend` je nach Version mit/ohne Kontext-Argument, neue Tests für die nativen Task-Parameter,
  die Validierung, den Kopier-Hook und `Typo3Utility`
