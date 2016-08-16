MK Log
======

Scheduler
-----------

### DevLog WatchDog

The devlog scheduler checks the devlog entries for new messages and sends
these messages throu a transport.

Currently there are a mail and some gelf transports.

![](../../Images/SchedulerWatchDog.png)


### tx_devlog Scheduler

![](../../Images/SchedulerTask.png)

You have to provide an email address which receives the mails.

Furthermore you can choose from which severity on devlog entries should be sent. If you set it to WARN, all entries with the serverity of WARN and ERROR will be sent via mail.

Note, you will only get entries which are new since the last run. If nothing new no mail will be sent. Except if you force a summary mail.

It is even possible to include the data\_var/extra\_data field of a devlog entry into the mail.
