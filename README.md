mklog
=======

This extensions offers a scheduler task, the watch dog, which aggregates devlog entries and sends them via mail. So it is possible to send a mail with fatal errors the minute they occur but warnings only every 6 hours. Or send a mail with infos from an import every night.

Of course the devlog has to be used by the core and extensions. To have exceptions and errors logged to the devlog the error handling of mktools can be used.

Additional features
-------------------

Normally when a page is copied the devlog entries are copied along (depending on the permissions). This is annoying and can lead to confusion as those entries are recognized as new even though they aren't. That's why this extensions hooks into the copy process and removes the devlog table from the list of tables which can be copied. This happens for all admins as well.

When a page is deleted by a non admin TYPO3 checks if the user has permissions to delete all tables on that page. This is fine except for the devlog table as this can be safely deleted and users shouldn't have permissions for those. That's why this extensions hooks into the deletion process and deletes all devlog entries on a page before the page is deleted ignoring permissions.


[UsersManual](Documentation/UsersManual/Index.md)

[ChangeLog](Documentation/ChangeLog/Index.md)