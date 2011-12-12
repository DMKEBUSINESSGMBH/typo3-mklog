
Sinn und Zweck von mklog.

1. Automatische Systemmails bei bestimmten Fehlermeldungen im Devlog
2. Neues BE-Modul zur Anzeige des devlogs


1. Infomail
Ablauf: Der Job prüft, ob seit dem letzten Lauf Fehler im Devlog eingetroffen sind, die bestimmten
Kriterien entsprechen. In diesem Fall wird eine Infomail erzeugt.

Die Mail enthält zum einen eine Auflistung aller LogLevel mit der Summe der gefundenen Einträge
Ab dem gesetzten LogLevel und höher, werden die letzte Einträge einzeln aufgelistet.

Konfiguration
email: Zieladressen
minlevel: Minimales Loglevel ab dem die Mails verschickt werden
forceSummery: Sendet zumindest das Summery auch wenn das minlevel nicht erreicht wurde.
