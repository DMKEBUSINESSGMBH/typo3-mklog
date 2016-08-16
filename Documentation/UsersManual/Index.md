MK Log
======

UsersManual
-----------

* [DevLog](Devlog/Index.md)
* [Scheduler](Scheduler/Index.md)


Additional features
-------------------

Normally when a page is copied the tx_devlog entries are copied along (depending on the permissions).
This is annoying and can lead to confusion as those entries are recognized as new even though they aren't.
That's why this extensions hooks into the copy process and removes the devlog table from the list of tables which can be copied.
This happens for all admins as well.

When a page is deleted by a non admin TYPO3 checks if the user has permissions to delete all tables on that page.
This is fine except for the devlog table as this can be safely deleted and users shouldn't have permissions for those.
That's why this extensions hooks into the deletion process and deletes all devlog entries on a page before the page is deleted ignoring permissions.