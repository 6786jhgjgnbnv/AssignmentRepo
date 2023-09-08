# AssignmentRepo
Drupal migration

REQUIREMENTS
============
You need the contrib modules Migrate Plus and Migrate Tools.
To make the cities.json file available for import, the file will be copied
from the artifacts folder to your sites/default/files folder.

USAGE
=====
Enable the module, check status, import all products, and rollback with Drush
drush en migration_module
drush migrate-status
drush migrate-import cities
drush migrate-rollback cities

See config/optional/migrate_plus.migration.cities.yml for details about the
migration.
