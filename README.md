SMUG
====

SMUG is a really simple ORMish framework. It was built years ago in a frantic rush for use in a project (due the next
day) requiring lots of data objects. All the ORM frameworks I looked into at the time struck me as overly complex, I
wanted something very minimal without a steep learning curve.

It makes developing CRUD objects + controllers as painless and as quick as possible. The focus is on ease of use,
logical design and maximum functionality without sacrificing simplicity.

It is NOT designed to be particularly performant, although I've not had any issues with it in the past, hardware is
cheap.

It's found it's way onto Github because it's actually seen quite a bit of action over the years. A friend of mine (who
uses it daily) wanted multi-DBMS support (Originally it was MySql only). So we thought we'd give it a bit of a tune up
and implement a basic plugin system for DB Drivers.

The DBMS abstraction is (nearly) done, the core functionality works, we only have a MySql driver at present. If you want
to implement your own, then see core/dbms.php and use the MySql driver (see core/drivers/) as a guideline.

See base/ for a basic example of how to create Data Controls.