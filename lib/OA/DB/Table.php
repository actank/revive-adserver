<?php

/*
+---------------------------------------------------------------------------+
| Openads v2.5                                                              |
| ============                                                              |
|                                                                           |
| Copyright (c) 2003-2007 Openads Limited                                   |
| For contact details, see: http://www.openads.org/                         |
|                                                                           |
| This program is free software; you can redistribute it and/or modify      |
| it under the terms of the GNU General Public License as published by      |
| the Free Software Foundation; either version 2 of the License, or         |
| (at your option) any later version.                                       |
|                                                                           |
| This program is distributed in the hope that it will be useful,           |
| but WITHOUT ANY WARRANTY; without even the implied warranty of            |
| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
| GNU General Public License for more details.                              |
|                                                                           |
| You should have received a copy of the GNU General Public License         |
| along with this program; if not, write to the Free Software               |
| Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA |
+---------------------------------------------------------------------------+
$Id$
*/

require_once MAX_PATH . '/lib/OA/DB.php';
require_once MAX_PATH . '/lib/OA/DB/XmlCache.php';
require_once 'Date.php';
require_once 'MDB2.php';
require_once 'MDB2/Schema.php';

/**
 * An abstract class defining the interface for creating Openads database tables.
 *
 * Note that only permanent tables are created with the table prefix defined in the
 * configuration .ini file - temporary tables do NOT use the table prefix. This is
 * because temporary tables are not defined in the table array in the configuration
 * .ini file, and as such, must be referenced in the Data Abstraction Layer code
 * directly, and this is easier to do if the prefix doesn't have to be prepended
 * in order to do so.
 *
 * @package    OpenadsDB
 * @subpackage Table
 * @author     Andrew Hill <andrew.hill@openads.org>
 */
class OA_DB_Table
{

    /**
     * An instance of the OA_DB class.
     *
     * @var OA_DB
     */
    var $oDbh;

    /**
     * An instance of the MDB2_Schema class.
     *
     * @var MDB2_Schema
     */
    var $oSchema;

    /**
     * An array containing the database definition, as parsed from
     * the XML schema file.
     *
     * @var array
     */
    var $aDefinition;

    /**
     * Should the tables be created as temporary tables?
     *
     * @var boolean
     */
    var $temporary = false;

    var $cached_definition = true;

    /**
     * The class constructor method.
     */
    function OA_DB_Table()
    {
        $this->oDbh = &$this->_getDbConnection();
    }

    /**
     * A private method to manage creation of the utilised Openads_Dal class.
     *
     * @access private
     * @return mixed Reference to an MDB2 connection resource, or PEAR_Error
     *               on failure to connect.
     */
    function &_getDbConnection()
    {
        return OA_DB::singleton();
    }

    /**
     * A method to initialise the class by parsing a database XML schema file, so that
     * the class will be ready to create/drop tables for the supplied schema.
     *
     * @todo Better handling of cache files
     *
     * @param string $file     The name of the database XML schema file to parse for
     *                         the table definitions.
     * @param bool   $useCache If true definitions are loaded from the cache file
     * @return boolean True if the class was initialised correctly, false otherwise.
     */
    function init($file, $useCache = true)
    {
        // Ensure that the schema XML file can be read
        if (!is_readable($file)) {
            MAX::debug('Unable to read the database XML schema file: ' . $file, PEAR_LOG_ERR);
            return false;
        }
        // Create an instance of MDB2_Schema to parse the schema file
        $options = array('force_defaults'=>false);
        $this->oSchema = &MDB2_Schema::factory($this->oDbh, $options);

        if ($useCache) {
            $oCache = new OA_DB_XmlCache();
            $this->aDefinition = $oCache->get($file);
            $this->cached_definition = true;
        } else {
            $this->aDefinition = false;
        }

        if (!$this->aDefinition) {
            $this->cached_definition = false;
            // Parse the schema file
            $this->aDefinition = $this->oSchema->parseDatabaseDefinitionFile($file);
            if (PEAR::isError($this->aDefinition)) {
                MAX::debug('Error parsing the database XML schema file: ' . $file, PEAR_LOG_ERR);
                return false;
            }

            // On-the fly cache writing disabled
            //if ($useCache) {
            //    $oCache->save($this->aDefinition, $file);
            //}
        }
        return true;
    }

    /**
     * A private method to test if the class has been correctly initialised with a
     * valid database XML schema file.
     *
     * @return boolean True if the class has been correctly initialised, false otherwise.
     */
    function _checkInit()
    {
        if (is_null($this->aDefinition)) {
            MAX::debug('No database XML schema file parsed, cannot create table', PEAR_LOG_ERR);
            return false;
        } else if (PEAR::isError($this->aDefinition)) {
            MAX::debug('Previous error parsing the database XML schema file', PEAR_LOG_ERR);
            return false;
        }
        return true;
    }

    /**
     * return an array of tables in currently connected database
     * ensuring case is preserved
     * and that only tables with openads configured prefix are listed
     * optional 2nd prefix 'like' for narrowing the filter
     * this 'like' must be a simple string, no reg ex type stuff
     * e.g. $like= 'data_summary_'
     *
     * @param string $like
     * @return array
     */
    function listOATablesCaseSensitive($like='')
    {
        OA_DB::setCaseSensitive();
        $oDbh = &OA_DB::singleton();
        $aDBTables = $oDbh->manager->listTables(null, $GLOBALS['_MAX']['CONF']['table']['prefix'].$like);
        OA_DB::disableCaseSensitive();
        return $aDBTables;
    }

    /**
     * A method for creating a table from the currently parsed database XML schema file.
     *
     * @param string $table The name of the table to create, excluding table prefix.
     * @param Date $oDate An optional date for creating split tables. Will use current
     *                    date if the date is required for creation, but not supplied.
     * @param boolean $suppressTempTableError When true, do not produce an error debugging
     *                                        message if trying to create a temporary table
     *                                        that already exists.
     * @return mixed The name of the table created, or false if the table was not able
     *               to be created.
     */
    function createTable($table, $oDate = null, $suppressTempTableError = false)
    {
        $aConf = $GLOBALS['_MAX']['CONF'];
        if (!$this->_checkInit()) {
            return false;
        }
        // Does the table exist?
        if (!is_array($this->aDefinition['tables'][$table])) {
            MAX::debug('Cannot find table ' . $table . ' in the XML schema file', PEAR_LOG_ERR);
            return false;
        }
        $tableName = $table;
        // Does a table prefix need to be added to the table name?
        $prefixed = false;
        if ($aConf['table']['prefix'] && !$this->temporary) {
            $tableName = $aConf['table']['prefix'] . $tableName;
            $prefixed = true;
        }
        // Are split tables in operation, and is the table designed to be split?
        $split = false;
        if (($aConf['table']['split']) && ($aConf['splitTables'][$table])) {
            if ($oDate == NULL) {
                $oDate = new Date();
            }
            $tableName = $tableName . '_' . $oDate->format('%Y%m%d');
            $split = true;
        }
        // Prepare the options array
        $aOptions = array();
        if ($this->temporary) {
            $aOptions['temporary'] = true;
        }
        $aOptions['type'] = $aConf['table']['type'];
        // Merge any primary keys into the options array
        if (isset($this->aDefinition['tables'][$table]['indexes'])) {
            if (is_array($this->aDefinition['tables'][$table]['indexes'])) {
                foreach ($this->aDefinition['tables'][$table]['indexes'] as $key => $aIndex) {
                    if (isset($aIndex['primary']) && $aIndex['primary']) {
                        $aOptions['primary'] = $aIndex['fields'];
                        $indexName = $tableName.'_pkey';
                    } else {
                        // Disabled
                        //
                        // Eventually strip the leading table name prefix from the index and
                        // add the currently generated table name. This should ensure that
                        // index names are unique database-wide, required at least by PgSQL
                        //
                        //$indexName = $tableName . '_' . preg_replace("/^{$table}_/", '', $key);

                        continue;
                    }
                    // Does the index name need to be udpated to match either
                    // the prefixed table name, or the the split table name, or
                    // simply it has a wrong name in the xml definition?
                    if ($key != $indexName) {
                        // Eventually strip the hardcoded leading table name and add the
                        // correct prefix to the index name
                        $this->aDefinition['tables'][$table]['indexes'][$indexName] =
                            $this->aDefinition['tables'][$table]['indexes'][$key];
                        unset($this->aDefinition['tables'][$table]['indexes'][$key]);
                    }
                }
            }
        }
        // Create the table
        MAX::debug('Creating the ' . $tableName . ' table', PEAR_LOG_DEBUG);
        PEAR::pushErrorHandling(null);
        OA_DB::setCaseSensitive();
        $result = $this->oSchema->createTable($tableName, $this->aDefinition['tables'][$table], false, $aOptions);
        OA_DB::disableCaseSensitive();
        PEAR::popErrorHandling();
        if (PEAR::isError($result) || (!$result)) {
            $showError = true;
            if ($this->temporary && $suppressTempTableError) {
                $showError = false;
            }
            if ($showError) {
                MAX::debug('Unable to create the table ' . $table, PEAR_LOG_ERR);
            }
            return false;
        }
        return $tableName;
    }

    /**
     * A method for creating all tables from the currently parsed database XML schema file.
     *
     * @param Date $oDate An optional date for creating split tables. Will use current
     *                    date if the date is required for creation, but not supplied.
     * @return boolean True if all tables created successfuly, false otherwise.
     */
    function createAllTables($oDate = null)
    {
        if (!$this->_checkInit()) {
            return false;
        }
        foreach ($this->aDefinition['tables'] as $tableName => $aTable) {
            $result = $this->createTable($tableName, $oDate);
            if (PEAR::isError($result) || (!$result)) {
                return false;
            }
        }
        return true;
    }

    /**
     * A method for creating a table, and all other tables it relies on, based on the
     * "foriegn keys" the table has (actually taken from the DB_DataObjects .ini file).
     *
     * @param string $table The name of the (primary) table to create, excluding table prefix.
     * @param Date $oDate An optional date for creating split tables. Will use current
     *                    date if the date is required for creation, but not supplied.
     * @return boolean True if all required tables created successfuly, false otherwise.
     */
    function createRequiredTables($table, $oDate = null)
    {
        if (!$this->_checkInit()) {
            return false;
        }
        $aTableNames = $this->_getRequiredTables($table);
        $result = $this->createTable($table, $oDate);
        if (!$result) {
            return false;
        }
        foreach ($aTableNames as $tableName) {
            $result = $this->createTable($tableName, $oDate);
            if (!$result) {
                return false;
            }
        }
        return true;
    }

    /**
     * A method to easily drop a table.
     *
     * @param string $table The table name to drop. Must be the complete table name in use,
     *                      as no prefix or data values will be added before dropping the table.
     * @return boolean True if table dropped, false otherwise.
     */
    function dropTable($table)
    {
        MAX::debug('Dropping table ' . $table, PEAR_LOG_DEBUG);
        PEAR::pushErrorHandling(null);
        $result = $this->oDbh->manager->dropTable($table);
        PEAR::popErrorHandling();
        if (PEAR::isError($result)) {
            MAX::debug('Unable to drop table ' . $table, PEAR_LOG_ERROR);
            return false;
        }
        return true;
    }

    /**
     * A method for dropping all tables from the currently parsed database XML schema file.
     * Does not drop any tables that are set up to be "split", if split tables is enabled.
     *
     * @return boolean True if all tables dropped successfuly, false otherwise.
     */
    function dropAllTables()
    {
        $aConf = $GLOBALS['_MAX']['CONF'];
        if (!$this->_checkInit()) {
            return false;
        }
        $allTablesDropped = true;
        foreach ($this->aDefinition['tables'] as $tableName => $aTable) {
            if (($aConf['table']['split']) && ($aConf['splitTables'][$tableName])) {
                // Don't drop
                continue;
            }
            MAX::debug('Dropping the ' . $tableName . ' table', PEAR_LOG_DEBUG);
            $result = $this->dropTable($aConf['table']['prefix'].$tableName);
            if (PEAR::isError($result) || (!$result)) {
                MAX::debug('Unable to drop the table ' . $table, PEAR_LOG_ERROR);
                $allTablesDropped = false;
            }
        }
        return $allTablesDropped;
    }

    /**
     * A method to TRUNCATE a table.  If the DB is mysql it also sets autoincrement to 1.
     *
     * @param string $table the name of the table to truncate
     * @return boolean True if table truncated, false otherwise
     */
    function truncateTable($table)
    {
        $aConf = $GLOBALS['_MAX']['CONF'];
        MAX::debug('Truncating table ' . $table, PEAR_LOG_DEBUG);
        OA::disableErrorHandling();
        $query = "TRUNCATE TABLE $table";
        $result = $this->oDbh->exec($query);
        OA::enableErrorHandling();
        if (PEAR::isError($result)) {
            MAX::debug('Unable to truncate table ' . $table, PEAR_LOG_ERROR);
            return false;
        }
        if ($aConf['database']['type'] == 'mysql') {
            OA::disableErrorHandling();
            $result = $this->oDbh->exec("ALTER TABLE $table AUTO_INCREMENT = 1" );
            OA::enableErrorHandling();
            if (PEAR::isError($result)) {
                MAX::debug('Unable to set mysql auto_increment to 1', PEAR_LOG_ERROR);
                return false;
            }
        }
        return true;
    }

    /**
     * A method for truncating all tables from the currently parsed database XML
     * schema file, including any split versions of these tables, if they exist
     * in the database.
     *
     * @return boolean True if all tables truncated successfuly, false otherwise.
     */
    function truncateAllTables()
    {
        $aConf = $GLOBALS['_MAX']['CONF'];
        if (!$this->_checkInit()) {
            return false;
        }
        $allTablesTruncated = true;
        // Do we need to truncate split tables?
        if ($aConf['table']['split']) {
            $aTables = OA_DB_Table::listOATablesCaseSensitive();
        }
        // Iterate over each known table, and truncate
        foreach ($this->aDefinition['tables'] as $tableName => $aTable) {
            if (($aConf['table']['split']) && ($aConf['splitTables'][$tableName])) {
                // Find all split instances of this table
                foreach ($aTables as $realTable) {
                    if (preg_match("/^" . $aConf['table']['prefix'] . $tableName . '$/', $realTable)) {
                        MAX::debug('Truncating the ' . $tableName . ' table', PEAR_LOG_DEBUG);
                        $result = $this->truncateTable($realTable);
                    } else if (preg_match("/^" . $aConf['table']['prefix'] . $tableName . '_[0-9]{8}/', $realTable)) {
                        MAX::debug('Truncating the ' . $tableName . ' table', PEAR_LOG_DEBUG);
                        $result = $this->truncateTable($realTable);
                    }
                }
            } else {
                MAX::debug('Truncating the ' . $tableName . ' table', PEAR_LOG_DEBUG);
                $result = $this->truncateTable($aConf['table']['prefix'].$tableName);
            }
            if (PEAR::isError($result)) {
                MAX::debug('Unable to truncate the table ' . $tableName, PEAR_LOG_ERROR);
                $allTablesTruncated = false;
            }
        }
        return $allTablesTruncated;
    }

    /**
     * Resets a (postgresql) sequence to 1
     *
     * @param string $sequence the name of the sequence to reset
     * @return boolean true on success, false otherwise
     */
    function resetSequence($sequence)
    {
        $aConf = $GLOBALS['_MAX']['CONF'];
        MAX::debug('Resetting sequence ' . $sequence, PEAR_LOG_DEBUG);
        PEAR::pushErrorHandling(null);

        if ($aConf['database']['type'] == 'pgsql') {
            $result = $this->oDbh->exec("SELECT setval('$sequence', 1, false)");
            PEAR::popErrorHandling();
            if (PEAR::isError($result)) {
                MAX::debug('Unable to truncate table ' . $table, PEAR_LOG_ERROR);
                return false;
            }
        }
        return true;
    }

    /**
     * Resets all sequences
     *
     * @return boolean true on success, false otherwise
     */
    function resetAllSequences()
    {
        $allSequencesReset = true;
        $aSequences = $this->oDbh->manager->listSequences();
        if (is_array($aSequences)) {
            foreach ($aSequences as $sequence) {
                // listSequences returns sequence names without trailing '_seq'
                $sequence .= '_seq';
                MAX::debug('Resetting the ' . $sequence . ' sequence', PEAR_LOG_DEBUG);
            	if (!$this->resetSequence($sequence)) {
            	    MAX::debug('Unable to reset the sequence ' . $sequence, PEAR_LOG_ERROR);
            	    $allSequencesReset = false;
            	}
            }
        }
        return $allSequencesReset;
    }

    /**
     * A method to get all the required tables to create another table.
     *
     * @param string  $table  The table to check for.
     * @param array   $aLinks The links array, if already loaded.
     * @param array   $aSkip  The table(s) to skip (already checked).
     * @param integer $level  Recursion level.
     * @return array The required tables array.
     */
    function _getRequiredTables($table, $aLinks = null, $aSkip = null, $level = 0)
    {
        if (is_null($aLinks)) {
            require_once MAX_PATH . '/lib/OA/Dal/Links.php';
            $aLinks = Openads_Links::readLinksDotIni(MAX_PATH . '/lib/max/Dal/DataObjects/db_schema.links.ini');
        }
        $aTables = array();
        if (isset($aLinks[$table])) {
            foreach ($aLinks[$table] as $aLink) {
                $refTable = $aLink['table'];
                $aTables[$refTable] = $level;
                foreach (array_keys($aTables) as $refTable) {
                    if (!isset($aSkip[$refTable])) {
                        $aTables = $this->_getRequiredTables($refTable, $aLinks, $aTables, $level + 1) + $aTables;
                    }
                }
            }
        }
        if (!$level) {
            arsort($aTables);
            return array_keys($aTables);
        } else {
            return $aTables;
        }
    }
}

?>
