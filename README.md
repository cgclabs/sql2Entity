[![Build Status](https://travis-ci.org/cgclabs/sql2Entity.svg?branch=master)](https://travis-ci.org/cgclabs/sql2Entity)

# sql2Entity
CLI PHP script to convert an SQL file with create table statements to a Doctrine Entity class(es). Written to work with Laravel-Doctrine. May work outside of Laravel, but untested.

### Usage ###
```
./convertSQL.php <sql file> <output folder (optional)> <options>
```
<options> can be -v for verbose mode. With the --help or -h options, you will get this help.

Output folder will default to the included generatedEntities folder

