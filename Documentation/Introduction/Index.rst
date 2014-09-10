.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _introduction:

Introduction
============


.. _what-it-does:

What does it do?
----------------

This extensions offers a scheduler task, the watch dog, which aggregates devlog entries and sends
them via mail. So it is possible to send a mail with fatal errors the minute they
occur but warnings only every 6 hours. Or send a mail with infos from an import every night.

Of course the devlog has to be used by the core and extensions. To have exceptions and errors
logged to the devlog the error handling of mktools can be used.

