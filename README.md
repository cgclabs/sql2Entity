# sql2Entity
CLI PHP to convert a SQL create table statement to a Doctrine Entity class. Written to work with Laravel-Doctrine. May work outside of Laravel, but untested.
### Use ###
1. Copy the files from this repo into any directory.
 
2. Copy the column creation bit of your SQL create table command - no need to include anything except the column and type definitions (see data.example)

3. Run teh command, from teh command line, by entering "php convertSQL.php". You will be prompted for the name of the file with your SQL command, and the name of your entity (Convention calls for the entity to have uppercase first letter)
  
4. Retrieve your entity from the generatedEntities directory.  
