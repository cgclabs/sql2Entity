# sql2Entity
CLI PHP to convert a SQL create table statement to a Doctrine Entity class. Written to work with Laravel-Doctrine. May work outside of Laravel, but untested.

### Usage ###
```
./convertSQL.php <sql file> <output folder (optional)> <options>
```
<options> can be -v for verbose mode. With the --help or -h options, you will get this help.

Output folder will default to the included generatedEntities folder

