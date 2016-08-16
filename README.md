mklog
=======

This extension offers a [developer log](Documentation/UsersManual/Devlog/Index.md). 
Ther is a scheduler task too, the watch dog, which aggregates devlog entries and sends them via a transport.
So it is possible to send a mail with fatal errors the minute they occur but warnings only every 6 hours. 
Or send a mail with infos from an import every night.

Or transport all the logs to a graylog server

Of course the devlog has to be used by the core and extensions.
To have exceptions and errors logged to the devlog the error handling of mktools can be used.

[UsersManual](Documentation/UsersManual/Index.md)

[ChangeLog](Documentation/ChangeLog/Index.md)