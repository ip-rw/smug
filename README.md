SMUG
====

SMUG is a really simple ORMish framework. It was built years ago in a frantic rush for use in a project (due the next
day) requiring lots of data objects. All the ORM frameworks I looked into at the time struck me as overly complex, I
wanted something very minimal without a steep learning curve.

It makes developing CRUD objects + controllers as painless and as quick as possible. The focus is on ease of use,
logical design and maximum functionality without sacrificing simplicity.

It is NOT designed to be particularly performant, although I've not had any issues with it in the past, hardware is
cheap.

It's found it's way onto GitHub because it's actually seen quite a bit of action over the years. A friend of mine (who
uses it daily) wanted multi-DBMS support (originally it was MySQL only). So we thought we'd give it a bit of a tune up
and implement a basic plugin system for DB Drivers.

The DBMS abstraction is (nearly) done, the core functionality works at least. We only have a MySQL driver at present. If
you want to implement your own, then see core/dbms.php and use the MySQL driver (see core/drivers/) as a guideline.

See base/ for a basic example of how to create Data Controls.

NB: There is no DELETE functionality, this is a constant point of contention but I'm not implementing it. If you want to
delete something you mark it deleted in the DB.