<?php

/*
+---------------------------------------------------------------------------+
| Revive Adserver                                                           |
| http://www.revive-adserver.com                                            |
|                                                                           |
| Copyright: See the COPYRIGHT.txt file.                                    |
| License: GPLv2 or later, see the LICENSE.txt file.                        |
+---------------------------------------------------------------------------+
*/

require_once LIB_PATH . '/Plugin/ComponentGroupManager.php';

/*class testFoo
{
    function testFoo($arg1, $arg2)
    {
        $this->arg1 = $arg1;
        $this->arg2 = $arg2;
    }
}*/

/**
 * A class for testing the OX_Plugin_ComponentGroupManager class.
 *
 * @package Plugins
 * @subpackage TestSuite
 */
class Test_OX_Plugin_ComponentGroupManager extends UnitTestCase
{
    public $testpathData = '/lib/OX/Plugin/tests/data/';
    public $testpathPackages = '/lib/OX/Plugin/tests/data/plugins/etc/';
    public $testpathPluginsAdmin = '/lib/OX/Plugin/tests/data/www/admin/plugins/';


    /**
     * The constructor method.
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function test_init()
    {
        $oManager = new OX_Plugin_ComponentGroupManager();
        $aConf = $GLOBALS['_MAX']['CONF'];
        $this->assertEqual($aConf['pluginPaths']['packages'], $oManager->pathPackages);
        $this->assertEqual($aConf['pluginPaths']['plugins'], $oManager->pathPlugins);
        $this->assertEqual($aConf['pluginPaths']['admin'], $oManager->pathPluginsAdmin);
        $this->assertEqual($aConf['pluginPaths']['var'] . 'DataObjects/', $oManager->pathDataObjects);
    }

    public function test_instantiateClass()
    {
        $oManager = new OX_Plugin_ComponentGroupManager();
        $this->assertFalse($oManager->_instantiateClass(''));
        $this->assertFalse($oManager->_instantiateClass('foo'));
        $this->assertTrue($oManager->_instantiateClass('stdClass'));

        $classname = 'testFoo';
        eval('class testFoo { function __construct() { $this->hello = "world"; } }');
        $oFoo = $oManager->_instantiateClass('testFoo', ['foo', 'bar']);
        $this->assertIsA($oFoo, 'testFoo');
        $this->assertEqual($oFoo->hello, 'world');

        /*$classname = 'testFoo';
        eval('class testFoo { function testFoo($arg1, $arg2) { $this->arg1 = $arg1; $this->arg2 = $arg2; } }');
        $oFoo = $oManager->_instantiateClass('testFoo',array('foo','bar'));
        $this->assertIsA($oFoo, 'testFoo');
        $this->assertEqual($oFoo->arg1, 'foo');
        $this->assertEqual($oFoo->arg2, 'bar');*/
    }

    public function test_buildDependencyArray()
    {
        Mock::generatePartial(
            'OX_Plugin_ComponentGroupManager',
            $oMockManager = 'OX_Plugin_ComponentGroupManager' . rand(),
            [
                                      'getFilePathToXMLInstall',
                                      'parseXML',
                                      'getComponentGroupVersion'
                                     ]
        );
        $oManager = new $oMockManager($this);

        $aComponentGroup['foo'] = [
                                'name' => 'foo',
                                'install' => ['syscheck' => ['depends' => []]],
                                ];
        $aComponentGroup['bar'] = [
                                'name' => 'bar',
                                'install' => ['syscheck' => ['depends' => [0 => ['name' => 'foo', 'version' => '1.0.0']]]],
                                ];
        $aComponentGroup['bar1'] = [
                                'name' => 'bar1',
                                'install' => ['syscheck' => ['depends' => [0 => ['name' => 'foo', 'version' => '1.0.0']]]],
                                ];
        $oManager->setReturnValueAt(0, 'parseXML', $aComponentGroup['bar']);
        $oManager->setReturnValueAt(1, 'parseXML', $aComponentGroup['foo']);
        $oManager->setReturnValueAt(2, 'parseXML', $aComponentGroup['bar']);
        $oManager->setReturnValueAt(3, 'parseXML', $aComponentGroup['foo']);
        $oManager->setReturnValueAt(4, 'parseXML', $aComponentGroup['bar']);
        $oManager->setReturnValueAt(5, 'parseXML', $aComponentGroup['bar1']);
        $oManager->expectCallCount('parseXML', 6);

        $fileBar = MAX_PATH . $this->testpathData . 'bar.xml';
        $fileBar1 = MAX_PATH . $this->testpathData . 'bar1.xml';
        $fileFoo = MAX_PATH . $this->testpathData . 'foo.xml';

        // Test 1 - missing xml file
        $aConf = ['bar' => 0];
        $GLOBALS['_MAX']['CONF']['pluginGroupComponents'] = $aConf;
        $oManager->setReturnValueAt(0, 'getFilePathToXMLInstall', '');
        $oManager->aErrors = [];
        $aResult = $oManager->_buildDependencyArray();
        $this->assertIsA($aResult, 'array');
        $this->assertEqual(count($aResult), 0);
        $this->assertEqual(count($oManager->aErrors), 1);

        // Test 2 - missing dependency : bar depends on foo but foo is not installed
        $aConf = ['bar' => 0];
        $GLOBALS['_MAX']['CONF']['pluginGroupComponents'] = $aConf;

        $oManager->setReturnValueAt(1, 'getFilePathToXMLInstall', $fileBar);
        $oManager->aErrors = [];
        $aResult = $oManager->_buildDependencyArray();
        $this->assertIsA($aResult, 'array');
        $this->assertEqual(count($aResult), 2);
        $this->assertEqual(count($oManager->aErrors), 1);

        $this->assertEqual($aResult['bar']['dependsOn']['foo'], OX_PLUGIN_DEPENDENCY_NOTFOUND);
        $this->assertEqual($aResult['foo']['isDependedOnBy'][0], 'bar');

        // Test 3 - missing dependency : bar depends on foo but foo is the wrong version
        $aConf = ['foo' => 1, 'bar' => 0];
        $GLOBALS['_MAX']['CONF']['pluginGroupComponents'] = $aConf;

        $oManager->setReturnValueAt(0, 'getComponentGroupVersion', '0.1.1-alpha');
        $oManager->setReturnValueAt(2, 'getFilePathToXMLInstall', $fileBar);
        $oManager->setReturnValueAt(3, 'getFilePathToXMLInstall', $fileFoo);
        $oManager->aErrors = [];
        $aResult = $oManager->_buildDependencyArray();
        $this->assertIsA($aResult, 'array');
        $this->assertEqual(count($aResult), 2);
        $this->assertEqual(count($oManager->aErrors), 1);

        $this->assertEqual($aResult['bar']['dependsOn']['foo'], OX_PLUGIN_DEPENDENCY_BADVERSION);
        $this->assertEqual($aResult['foo']['isDependedOnBy'][0], 'bar');

        // Test 4 - dependencies ok
        $aConf = ['foo' => 1, 'bar' => 0, 'bar1' => 0];
        $GLOBALS['_MAX']['CONF']['pluginGroupComponents'] = $aConf;

        $oManager->setReturnValueAt(1, 'getComponentGroupVersion', '1.0.0');
        $oManager->setReturnValueAt(2, 'getComponentGroupVersion', '1.0.0');
        $oManager->setReturnValueAt(4, 'getFilePathToXMLInstall', $fileBar);
        $oManager->setReturnValueAt(5, 'getFilePathToXMLInstall', $fileFoo);
        $oManager->setReturnValueAt(6, 'getFilePathToXMLInstall', $fileBar1);
        $oManager->aErrors = [];
        $aResult = $oManager->_buildDependencyArray();
        $this->assertIsA($aResult, 'array');
        $this->assertEqual(count($aResult), 3);
        $this->assertEqual(count($oManager->aErrors), 0);

        $this->assertEqual($aResult['bar']['dependsOn']['foo'], '1.0.0');
        $this->assertEqual($aResult['foo']['isDependedOnBy'][0], 'bar');

        $this->assertEqual($aResult['bar1']['dependsOn']['foo'], '1.0.0');
        $this->assertEqual($aResult['foo']['isDependedOnBy'][1], 'bar1');


        $oManager->expectCallCount('getComponentGroupVersion', 3);
        $oManager->expectCallCount('getFilePathToXMLInstall', 7);

        $oManager->tally();
        unset($GLOBALS['_MAX']['CONF']['pluginGroupComponents']['foo']);
        unset($GLOBALS['_MAX']['CONF']['pluginGroupComponents']['bar']);
    }

    public function test_saveDependencyArray()
    {
        Mock::generatePartial(
            'OA_Cache',
            $oMockCache = 'OA_Cache' . rand(),
            [
                                      'save',
                                      'setFileNameProtection'
                                     ]
        );
        $oCache = new $oMockCache($this);
        $oCache->setReturnValueAt(0, 'save', false);
        $oCache->setReturnValueAt(1, 'save', true);
        $oCache->expectCallCount('save', 2);

        Mock::generatePartial(
            'OX_Plugin_ComponentGroupManager',
            $oMockManager = 'OX_Plugin_ComponentGroupManager' . rand(),
            [
                                      '_getOA_Cache'
                                     ]
        );
        $oManager = new $oMockManager($this);
        $oManager->setReturnValue('_getOA_Cache', $oCache);
        $oManager->expectCallCount('_getOA_Cache', 2);

        $aTest = ['dependsOn' => ['foo' => ['bar' => ['installed' => 1, 'enabled' => 0]]]];
        $this->assertFalse($oManager->_saveDependencyArray($aTest));
        $this->assertTrue($oManager->_saveDependencyArray($aTest));

        $oCache->tally();
        $oManager->tally();
    }

    public function test_loadDependencyArray()
    {
        Mock::generatePartial(
            'OA_Cache',
            $oMockCache = 'OA_Cache' . rand(),
            [
                                      'load',
                                      'setFileNameProtection'
                                      ]
        );
        $oCache = new $oMockCache($this);
        $aTest['isDependedOnBy'] = ['foo' => ['bar' => ['installed' => true, 'enabled' => false]]];
        $aTest['dependsOn'] = ['bar' => ['foo' => ['installed' => true, 'enabled' => true]]];
        $oCache->setReturnValueAt(0, 'load', false);
        $oCache->setReturnValueAt(1, 'load', $aTest);
        $oCache->expectCallCount('load', 2);

        Mock::generatePartial(
            'OX_Plugin_ComponentGroupManager',
            $oMockManager = 'OX_Plugin_ComponentGroupManager' . rand(),
            [
                                      '_getOA_Cache'
                                     ]
        );
        $oManager = new $oMockManager($this);
        $oManager->setReturnValue('_getOA_Cache', $oCache);
        $oManager->expectCallCount('_getOA_Cache', 2);

        $this->assertFalse($oManager->_loadDependencyArray($aTest));
        $aResult = $oManager->_loadDependencyArray();
        $this->assertIsA($aResult, 'array');
        $this->assertEqual(count($aResult), 2);
        $this->assertEqual(count($aResult['dependsOn']), 1);
        $this->assertEqual(count($aResult['isDependedOnBy']), 1);
        $this->assertTrue(isset($aResult['isDependedOnBy']['foo']['bar']));
        $this->assertTrue(isset($aResult['dependsOn']['bar']['foo']));
        $this->assertEqual($aResult['isDependedOnBy']['foo']['bar']['installed'], true);
        $this->assertEqual($aResult['dependsOn']['bar']['foo']['installed'], true);
        $this->assertEqual($aResult['isDependedOnBy']['foo']['bar']['enabled'], false);
        $this->assertEqual($aResult['dependsOn']['bar']['foo']['enabled'], true);

        $oCache->tally();
        $oManager->tally();
    }

    public function test_isEnabled()
    {
        $oManager = new OX_Plugin_ComponentGroupManager();
        $GLOBALS['_MAX']['CONF']['pluginGroupComponents']['foo'] = 0;
        $this->assertFalse($oManager->isEnabled('foo'));
        $GLOBALS['_MAX']['CONF']['pluginGroupComponents']['foo'] = 1;
        $this->assertTrue($oManager->isEnabled('foo'));
        unset($GLOBALS['_MAX']['CONF']['pluginGroupComponents']['foo']);
    }

    public function test_getPathToComponentGroup()
    {
        $oManager = new OX_Plugin_ComponentGroupManager();
        $path = $oManager->getPathToComponentGroup('testplugin');
        $confpath = $GLOBALS['_MAX']['CONF']['pluginPaths']['packages'];
        $this->assertEqual($path, MAX_PATH . $confpath . 'testplugin/');
    }

    public function test_getFilePathToMDB2Schema()
    {
        $oManager = new OX_Plugin_ComponentGroupManager();
        $path = $oManager->getFilePathToMDB2Schema('testplugin', 'testschema');
        $confpath = $GLOBALS['_MAX']['CONF']['pluginPaths']['packages'];
        $this->assertEqual($path, MAX_PATH . $confpath . 'testplugin/etc/testschema.xml');
    }

    public function test_getFilePathToXMLInstall()
    {
        $oManager = new OX_Plugin_ComponentGroupManager();
        // The file needs to exist for this method to return now
        $file = MAX_PATH . $GLOBALS['_MAX']['CONF']['pluginPaths']['packages'] . 'testplugin/testplugin.xml';
        mkdir(dirname($file));
        touch($file);
        $result = $oManager->getFilePathToXMLInstall('testplugin');
        $this->assertEqual($result, $file);
        unlink($file);
        rmdir(dirname($file));

        // Check that if the file doesn't exist, but it can be found in the old /extensions path, that this is returned.
        // NOTE: This requires the test env to have write permissions to /path/to/openx/
        $file = str_replace('/plugins/', '/extensions/', $file);
        if (is_writable(MAX_PATH)) {
            mkdir(dirname($file), 0777, true);
            touch($file);
            $result = $oManager->getFilePathToXMLInstall('testplugin');
            $this->assertEqual($result, $file);
            unlink($file);
            rmdir(dirname($file));
        }
    }

    public function test_checkFiles()
    {
        $aFiles[] = ['path' => OX_PLUGIN_ADMINPATH . '/templates/', 'name' => 'testPlugin.html'];
        $aFiles[] = ['path' => OX_PLUGIN_ADMINPATH . '/images/', 'name' => 'testPlugin2.jpg'];
        $aFiles[] = ['path' => OX_PLUGIN_ADMINPATH . '/', 'name' => 'testPlugin-index.php'];

        $aFiles[] = ['path' => OX_PLUGIN_PLUGINPATH, 'name' => 'testPluginPackage.readme.txt'];

        $aFiles[] = ['path' => OX_PLUGIN_GROUPPATH . '/', 'name' => 'processPreferences.php'];
        $aFiles[] = ['path' => OX_PLUGIN_GROUPPATH . '/etc/', 'name' => 'tables_testplugin.xml'];
        $aFiles[] = ['path' => OX_PLUGIN_GROUPPATH . '/etc/DataObjects/', 'name' => 'Testplugin_table.php'];

        $name = 'testPlugin';
        $oManager = new OX_Plugin_ComponentGroupManager();

        $this->assertFalse($oManager->_checkFiles($name, $aFiles));

        $oManager->aErrors = [];
        $oManager->pathPackages = $this->testpathPackages;
        $oManager->pathPluginsAdmin = $this->testpathPluginsAdmin;
        $this->assertTrue($oManager->_checkFiles($name, $aFiles));

        if ($oManager->countErrors()) {
            foreach ($oManager->aErrors as $msg) {
                $this->assertTrue(false, $msg);
            }
        }
    }

    public function test_checkNavigationCheckers()
    {
        $aFiles[] = ['path' => OX_PLUGIN_ADMINPATH . '/navigation/', 'name' => 'testPluginChecker.php'];

        $aCheckers = [];
        $name = 'testPlugin';

        $oManager = new OX_Plugin_ComponentGroupManager();
        $oManager->aErrors = [];
        $oManager->pathPackages = $this->testpathPackages;
        $oManager->pathPluginsAdmin = $this->testpathPluginsAdmin;

        // No checkers return true
        $this->assertTrue($oManager->_checkNavigationCheckers($name, $aCheckers, $aFiles));

        // File not found
        $oManager->aErrors = [];
        $aCheckers = [];
        $aCheckers[] = ['class' => 'Plugins_Admin_TestPlugin_TestPluginChecker2', 'include' => 'testPluginChecker2.php'];
        $this->assertFalse($oManager->_checkNavigationCheckers($name, $aCheckers, $aFiles));

        // Class not found
        $oManager->aErrors = [];
        $aCheckers = [];
        $aCheckers[] = ['class' => 'Plugins_Admin_TestPlugin_TestPluginChecker2', 'include' => 'testPluginChecker.php'];
        $this->assertFalse($oManager->_checkNavigationCheckers($name, $aCheckers, $aFiles));

        // Checker found
        $oManager->aErrors = [];
        $aCheckers = [];
        $aCheckers[] = ['class' => 'Plugins_Admin_TestPlugin_TestPluginChecker', 'include' => 'testPluginChecker.php'];
        $this->assertTrue($oManager->_checkNavigationCheckers($name, $aCheckers, $aFiles));

        if ($oManager->countErrors()) {
            foreach ($oManager->aErrors as $msg) {
                $this->assertTrue(false, $msg);
            }
        }
    }

    public function test_getVersionController()
    {
        $oManager = new OX_Plugin_ComponentGroupManager();
        $oVerControl = $oManager->_getVersionController();
        $this->assertIsA($oVerControl, 'OA_Version_Controller');
        $this->assertIsA($oVerControl->oDbh, 'MDB2_Driver_Common');
    }

    public function test_runScript()
    {
        $oManager = new OX_Plugin_ComponentGroupManager();
        $oManager->pathPackages = $this->testpathPackages;
        global $testScriptResult;
        $this->assertNull($testScriptResult);
        $this->assertTrue($oManager->_runScript('testPlugin', 'testScript.php'));
        $this->assertTrue($testScriptResult);
    }

    public function test_runTasks_Pass()
    {
        Mock::generatePartial(
            'Mock_OX_Plugin_ComponentGroupManager',
            $oMockManager = 'OX_Plugin_ComponentGroupManager' . rand(),
            [
                                      'task1',
                                      'task2'
                                     ]
        );
        $oManager = new $oMockManager($this);

        $oManager->setReturnValueAt(0, 'task1', true, ['foo']);
        $oManager->expectCallCount('task1', 1);
        $oManager->setReturnValueAt(0, 'task2', true, ['bar']);
        $oManager->expectCallCount('task2', 1);

        $aTaskList[] = [
                            'method' => 'task1',
                            'params' => ['foo'],
                            ];
        $aTaskList[] = [
                            'method' => 'task2',
                            'params' => ['bar'],
                            ];

        $this->assertTrue($oManager->_runTasks('testPlugin', $aTaskList));
        $oManager->tally();
    }

    public function test_runTasks_Fail()
    {
        Mock::generatePartial(
            'Mock_OX_Plugin_ComponentGroupManager',
            $oMockManager = 'OX_Plugin_ComponentGroupManager' . rand(),
            [
                                      'task1',
                                      'task2'
                                     ]
        );
        $oManager = new $oMockManager($this);

        $oManager->setReturnValueAt(0, 'task1', false, ['foo']);
        $oManager->expectCallCount('task1', 1);
        $oManager->setReturnValueAt(0, 'task2', true, ['bar']);
        $oManager->expectCallCount('task2', 0);

        $aTaskList[] = [
                            'method' => 'task1',
                            'params' => ['foo'],
                            ];
        $aTaskList[] = [
                            'method' => 'task2',
                            'params' => ['bar'],
                            ];

        $this->assertFalse($oManager->_runTasks('testPlugin', $aTaskList));
        $oManager->tally();
    }

    public function test_runTasks_FailRollback()
    {
        Mock::generatePartial(
            'Mock_OX_Plugin_ComponentGroupManager',
            $oMockManager = 'OX_Plugin_ComponentGroupManager' . rand(),
            [
                                      'task1',
                                      'task2',
                                      'untask1',
                                      'untask2'
                                     ]
        );
        $oManager = new $oMockManager($this);

        $oManager->setReturnValueAt(0, 'task1', true, ['foo']);
        $oManager->expectCallCount('task1', 1);
        $oManager->setReturnValueAt(0, 'task2', false, ['bar']);
        $oManager->expectCallCount('task2', 1);

        $oManager->setReturnValueAt(0, 'untask1', true, ['foo']);
        $oManager->expectCallCount('untask1', 1);
        $oManager->setReturnValueAt(0, 'untask2', true, ['bar']);
        $oManager->expectCallCount('untask2', 1);


        $aTaskList[] = [
                            'method' => 'task1',
                            'params' => ['foo'],
                            ];
        $aTaskList[] = [
                            'method' => 'task2',
                            'params' => ['bar'],
                            ];
        $aUndoList[] = [
                            'method' => 'untask2',
                            'params' => ['bar'],
                            ];
        $aUndoList[] = [
                            'method' => 'untask1',
                            'params' => ['foo'],
                            ];

        $this->assertFalse($oManager->_runTasks('testPlugin', $aTaskList, $aUndoList));
        $oManager->tally();
    }

    public function test_runTasks_FailRollbackFail()
    {
        Mock::generatePartial(
            'Mock_OX_Plugin_ComponentGroupManager',
            $oMockManager = 'OX_Plugin_ComponentGroupManager' . rand(),
            [
                                      'task1',
                                      'task2',
                                      'untask1',
                                      'untask2'
                                     ]
        );
        $oManager = new $oMockManager($this);

        $oManager->setReturnValueAt(0, 'task1', true, ['foo']);
        $oManager->expectCallCount('task1', 1);
        $oManager->setReturnValueAt(0, 'task2', false, ['bar']);
        $oManager->expectCallCount('task2', 1);

        $oManager->setReturnValueAt(0, 'untask1', true, ['foo']);
        $oManager->expectCallCount('untask1', 0);
        $oManager->setReturnValueAt(0, 'untask2', false, ['bar']);
        $oManager->expectCallCount('untask2', 1);


        $aTaskList[] = [
                            'method' => 'task1',
                            'params' => ['foo'],
                            ];
        $aTaskList[] = [
                            'method' => 'task2',
                            'params' => ['bar'],
                            ];
        $aUndoList[] = [
                            'method' => 'untask2',
                            'params' => ['bar'],
                            ];
        $aUndoList[] = [
                            'method' => 'untask1',
                            'params' => ['foo'],
                            ];

        $this->assertFalse($oManager->_runTasks('testPlugin', $aTaskList, $aUndoList));
        $oManager->tally();
    }

    public function test_parseXML()
    {
        Mock::generatePartial(
            'Mock_stdClass',
            $oMockParser = 'stdClass' . rand(),
            [
                                      'setInputFile',
                                      'parse'
                                     ]
        );
        $oParser = new $oMockParser($this);
        $oParser->setReturnValue('setInputFile', true);
        $oParser->expectOnce('setInputFile');
        $oParser->setReturnValue('parse', true);
        $oParser->expectOnce('parse');
        $oParser->aPlugin = [
                                  1 => 'test1',
                                  2 => 'test2',
                                 ];

        Mock::generatePartial(
            'OX_Plugin_ComponentGroupManager',
            $oMockManager = 'OX_Plugin_ComponentGroupManager' . rand(),
            [
                                      '_instantiateClass'
                                     ]
        );
        $oManager = new $oMockManager($this);
        $oManager->setReturnValue('_instantiateClass', $oParser);
        $oManager->expectOnce('_instantiateClass');

        // Test 1 - missing xml file
        $this->assertFalse($oManager->parseXML('test.xml', ''));

        // Test 2 - success
        $fileFoo = MAX_PATH . $this->testpathData . 'foo.xml';
        $result = $oManager->parseXML($fileFoo, '');
        $this->assertIsA($result, 'array');
        $this->assertEqual(count($result), 2);
        $this->assertEqual($result[1], 'test1');
        $this->assertEqual($result[2], 'test2');

        $oManager->tally();
        $oParser->tally();
    }

    public function test_getComponentGroupSettingsArray()
    {
        $oManager = new OX_Plugin_ComponentGroupManager();

        $GLOBALS['_MAX']['CONF']['test'] = [
                                                  'foo' => 1,
                                                  'bar' => 0,
                                                 ];
        $aComponentGroups = $oManager->getComponentGroupSettingsArray('test');
        $this->assertIsA($aComponentGroups, 'array');
        $this->assertEqual(count($aComponentGroups), 2);
        $this->assertEqual($aComponentGroups['foo'], 1);
        $this->assertEqual($aComponentGroups['bar'], 0);
        unset($GLOBALS['_MAX']['CONF']['test']);
        $aComponentGroups = $oManager->getComponentGroupSettingsArray('test');
        $this->assertIsA($aComponentGroups, 'array');
        $this->assertEqual(count($aComponentGroups), 0);
    }

    public function test_getComponentGroupVersion()
    {
        Mock::generatePartial(
            'Mock_stdClass',
            $mockVerCtrl = 'stdClass' . rand(),
            [
                                      'getApplicationVersion'
                                     ]
        );
        $oVerCtrl = new $mockVerCtrl($this);
        $oVerCtrl->setReturnValueAt(0, 'getApplicationVersion', '0.1', ['foo']);
        $oVerCtrl->expectCallCount('getApplicationVersion', 1);

        Mock::generatePartial(
            'OX_Plugin_ComponentGroupManager',
            $oMockManager = 'OX_Plugin_ComponentGroupManager' . rand(),
            [
                                      '_getVersionController'
                                     ]
        );
        $oManager = new $oMockManager($this);

        $oManager->setReturnValue('_getVersionController', $oVerCtrl);
        $oManager->expectOnce('_getVersionController');

        $result = $oManager->getComponentGroupVersion('foo');
        $this->assertEqual($result, '0.1');

        $oManager->tally();
        $oVerCtrl->tally();
    }

    /*    function test_getPluginsArray()
        {
            Mock::generatePartial(
                                    'OX_Plugin_ComponentGroupManager',
                                    $oMockManager = 'OX_Plugin_ComponentGroupManager'.rand(),
                                    array('getComponentGroupVersion')
                                 );
            $oManager = new $oMockManager($this);

            $oManager->setReturnValueAt(0,'getComponentGroupVersion', '0.1', 'foo');
            $oManager->setReturnValueAt(1,'getComponentGroupVersion', '0.2', 'bar');
            $oManager->expectCallCount('getComponentGroupVersion',2);

            $GLOBALS['_MAX']['CONF']['pluginGroupComponents'] = array('foo'=>1,'bar'=>0);
            $aComponentGroups = $oManager->getPluginsArray();
            $this->assertIsA($aComponentGroups,'array');
            $this->assertEqual(count($aComponentGroups),2);
            $this->assertEqual($aComponentGroups['foo']['enabled'],1);
            $this->assertEqual($aComponentGroups['foo']['version'],'0.1');
            $this->assertEqual($aComponentGroups['bar']['enabled'],0);
            $this->assertEqual($aComponentGroups['bar']['version'],'0.2');

            $oManager->tally();
        }*/

    public function test_setPlugin()
    {
        Mock::generatePartial(
            'OA_Admin_Settings',
            $oMockConfig = 'OA_Admin_Settings' . rand(),
            [
                                      'settingChange',
                                      'writeConfigChange'
                                     ]
        );
        $oConfig = new $oMockConfig($this);
        $oConfig->setReturnValue('settingChange', true);
        $oConfig->expectOnce('settingChange');
        $oConfig->setReturnValue('writeConfigChange', true);
        $oConfig->expectOnce('writeConfigChange');

        Mock::generatePartial(
            'OX_Plugin_ComponentGroupManager',
            $oMockManager = 'OX_Plugin_ComponentGroupManager' . rand(),
            [
                                      '_instantiateClass'
                                     ]
        );
        $oManager = new $oMockManager($this);
        $oManager->setReturnValue('_instantiateClass', $oConfig);
        $oManager->expectOnce('_instantiateClass');

        $oManager->_setPlugin('test');
        $oManager->tally();
    }

    public function test_enableComponentGroup()
    {
        Mock::generatePartial(
            'OX_Plugin_ComponentGroupManager',
            $oMockManager = 'OX_Plugin_ComponentGroupManager' . rand(),
            [
                                      '_setPlugin'
                                     ]
        );
        $oManager = new $oMockManager($this);
        $oManager->setReturnValueAt(0, '_setPlugin', true);
        $oManager->setReturnValueAt(1, '_setPlugin', false);
        $oManager->expectCallCount('_setPlugin', 2);

        $this->assertTrue($oManager->enableComponentGroup('test', 'test'));
        $this->assertFalse($oManager->enableComponentGroup('test', 'test'));
        $oManager->tally();
    }

    public function test_disableComponentGroup()
    {
        Mock::generatePartial(
            'OX_Plugin_ComponentGroupManager',
            $oMockManager = 'OX_Plugin_ComponentGroupManager' . rand(),
            [
                                      '_setPlugin'
                                     ]
        );
        $oManager = new $oMockManager($this);
        $oManager->setReturnValueAt(0, '_setPlugin', true);
        $oManager->setReturnValueAt(1, '_setPlugin', false);
        $oManager->expectCallCount('_setPlugin', 2);

        $this->assertTrue($oManager->disableComponentGroup('test', 'test'));
        $this->assertFalse($oManager->disableComponentGroup('test', 'test'));
        $oManager->tally();
    }

    public function test_getSchemaInfo()
    {
        Mock::generatePartial(
            'Mock_stdClass',
            $mockVerCtrl = 'stdClass' . rand(),
            [
                                      'getSchemaVersion'
                                     ]
        );
        $oVerCtrl = new $mockVerCtrl($this);
        $oVerCtrl->setReturnValueAt(0, 'getSchemaVersion', '999', ['foo']);
        $oVerCtrl->expectCallCount('getSchemaVersion', 1);

        Mock::generatePartial(
            'OX_Plugin_ComponentGroupManager',
            $oMockManager = 'OX_Plugin_ComponentGroupManager' . rand(),
            [
                                      '_getVersionController'
                                     ]
        );
        $oManager = new $oMockManager($this);

        $oManager->setReturnValue('_getVersionController', $oVerCtrl);
        $oManager->expectOnce('_getVersionController');

        $this->assertEqual($oManager->getSchemaInfo('foo'), '999');
        $oVerCtrl->tally();
        $oManager->tally();
    }

    public function test_getComponentGroupInfo()
    {
        Mock::generatePartial(
            'OX_Plugin_ComponentGroupManager',
            $oMockManager = 'OX_Plugin_ComponentGroupManager' . rand(),
            [
                                      'getSchemaInfo',
                                      'parseXML'
                                     ]
        );
        $oManager = new $oMockManager($this);

        (new ReflectionMethod(OX_Plugin_ComponentGroupManager::class, '__construct'))->invoke($oManager);
        $oManager->pathPackages = $this->testpathPackages;
        $oManager->pathPluginsAdmin = $this->testpathPluginsAdmin;

        $aComponentGroup1 = [
                        '1' => 'foo',
                        '2' => 'bar',
                        'install' => ['schema' => ['mdb2schema' => 'fooschema']],
                        ];
        $oManager->setReturnValueAt(0, 'parseXML', $aComponentGroup1);
        $oManager->setReturnValueAt(0, 'getSchemaInfo', '999', ['fooschema']);

        $aResult = $oManager->getComponentGroupInfo('testPlugin');
        $this->assertIsA($aResult, 'array');
        $this->assertEqual(count($aResult), 9);
        $this->assertEqual($aResult[1], 'foo');
        $this->assertEqual($aResult[2], 'bar');
        $this->assertEqual($aResult['schema_name'], 'fooschema');
        $this->assertEqual($aResult['schema_version'], '999');

        $aComponentGroup2 = [
                        '1' => 'foo',
                        '2' => 'bar',
                        'install' => ['conf' => ['settings' => [['visible' => 1, 1, 2]], 'preferences' => [0, 1, 2]]],
                        ];
        $oManager->setReturnValueAt(1, 'parseXML', $aComponentGroup2);
        $oManager->setReturnValueAt(1, 'getSchemaInfo', false);

        $aResult = $oManager->getComponentGroupInfo('testPlugin');
        $this->assertIsA($aResult, 'array');
        $this->assertEqual(count($aResult), 7);
        $this->assertEqual($aResult[1], 'foo');
        $this->assertEqual($aResult[2], 'bar');
        $this->assertFalse(isset($aResult['schema_name']));
        $this->assertFalse(isset($aResult['schema_version']));
        $this->assertTrue($aResult['settings']);
        $this->assertTrue($aResult['preferences']);

        $oManager->expectCallCount('parseXML', 2);
        $oManager->expectCallCount('getSchemaInfo', 2);

        $oManager->tally();
    }

    public function test_registerPluginVersion()
    {
        Mock::generatePartial(
            'Mock_stdClass',
            $mockVerCtrl = 'stdClass' . rand(),
            [
                                      'putApplicationVersion'
                                     ]
        );
        $oVerCtrl = new $mockVerCtrl($this);
        $oVerCtrl->setReturnValueAt(0, 'putApplicationVersion', '0.1', ['0.1', 'test']);
        $oVerCtrl->setReturnValueAt(1, 'putApplicationVersion', 0.1, ['0.1', 'test']);
        $oVerCtrl->expectCallCount('putApplicationVersion', 2);

        Mock::generatePartial(
            'OX_Plugin_ComponentGroupManager',
            $oMockManager = 'OX_Plugin_ComponentGroupManager' . rand(),
            [
                                      '_getVersionController'
                                     ]
        );
        $oManager = new $oMockManager($this);

        $oManager->setReturnValue('_getVersionController', $oVerCtrl);
        $oManager->expectCallCount('_getVersionController', 2);

        $this->assertTrue($oManager->_registerPluginVersion('test', '0.1'));
        $this->assertFalse($oManager->_registerPluginVersion('test', '0.1'));

        $oVerCtrl->tally();
        $oManager->tally();
    }

    public function test_registerSchemaVersion()
    {
        Mock::generatePartial(
            'Mock_stdClass',
            $mockVerCtrl = 'stdClass' . rand(),
            [
                                      'putSchemaVersion'
                                     ]
        );
        $oVerCtrl = new $mockVerCtrl($this);
        $oVerCtrl->setReturnValueAt(0, 'putSchemaVersion', '999', ['test', '999']);
        $oVerCtrl->setReturnValueAt(1, 'putSchemaVersion', true, ['test', '999']);
        $oVerCtrl->expectCallCount('putSchemaVersion', 2);

        Mock::generatePartial(
            'OX_Plugin_ComponentGroupManager',
            $oMockManager = 'OX_Plugin_ComponentGroupManager' . rand(),
            [
                                      '_getVersionController'
                                     ]
        );
        $oManager = new $oMockManager($this);

        $oManager->setReturnValue('_getVersionController', $oVerCtrl);
        $oManager->expectCallCount('_getVersionController', 2);

        $this->assertTrue($oManager->_registerSchemaVersion('test', '999'));
        $this->assertFalse($oManager->_registerSchemaVersion('test', '999'));

        $oVerCtrl->tally();
        $oManager->tally();
    }

    public function test_unregisterSchemaVersion()
    {
        Mock::generatePartial(
            'Mock_stdClass',
            $mockVerCtrl = 'stdClass' . rand(),
            [
                                      'removeVariable'
                                     ]
        );
        $oVerCtrl = new $mockVerCtrl($this);
        $oVerCtrl->setReturnValueAt(0, 'removeVariable', true, ['test']);
        $oVerCtrl->setReturnValueAt(0, 'removeVariable', false, ['test']);
        $oVerCtrl->expectCallCount('removeVariable', 2);

        Mock::generatePartial(
            'OX_Plugin_ComponentGroupManager',
            $oMockManager = 'OX_Plugin_ComponentGroupManager' . rand(),
            [
                                      '_getVersionController'
                                     ]
        );
        $oManager = new $oMockManager($this);

        $oManager->setReturnValue('_getVersionController', $oVerCtrl);
        $oManager->expectCallCount('_getVersionController', 2);

        $this->assertTrue($oManager->_unregisterSchemaVersion('test'));
        $this->assertFalse($oManager->_unregisterSchemaVersion('test'));

        $oVerCtrl->tally();
        $oManager->tally();
    }

    public function test_unregisterPluginVersion()
    {
        Mock::generatePartial(
            'Mock_stdClass',
            $mockVerCtrl = 'stdClass' . rand(),
            [
                                      'removeVersion'
                                     ]
        );
        $oVerCtrl = new $mockVerCtrl($this);
        $oVerCtrl->setReturnValueAt(0, 'removeVersion', true, ['test']);
        $oVerCtrl->setReturnValueAt(1, 'removeVersion', false, ['test']);
        $oVerCtrl->expectCallCount('removeVersion', 2);

        Mock::generatePartial(
            'OX_Plugin_ComponentGroupManager',
            $oMockManager = 'OX_Plugin_ComponentGroupManager' . rand(),
            [
                                      '_getVersionController'
                                     ]
        );
        $oManager = new $oMockManager($this);

        $oManager->setReturnValue('_getVersionController', $oVerCtrl);
        $oManager->expectCallCount('_getVersionController', 2);

        $this->assertTrue($oManager->_unregisterPluginVersion('test'));
        $this->assertFalse($oManager->_unregisterPluginVersion('test'));

        $oVerCtrl->tally();
        $oManager->tally();
    }

    public function test_registerSettings()
    {
        Mock::generatePartial(
            'OA_Admin_Settings',
            $oMockConfig = 'OA_Admin_Settings' . rand(),
            [
                                      'settingChange',
                                      'writeConfigChange'
                                     ]
        );
        $oConfig = new $oMockConfig($this);
        $oConfig->setReturnValue('settingChange', true);
        $oConfig->expectCallCount('settingChange', 4);
        $oConfig->setReturnValueAt(0, 'writeConfigChange', true);
        $oConfig->setReturnValueAt(1, 'writeConfigChange', false);
        $oConfig->expectCallCount('writeConfigChange', 2);

        Mock::generatePartial(
            'OX_Plugin_ComponentGroupManager',
            $oMockManager = 'OX_Plugin_ComponentGroupManager' . rand(),
            [
                                      '_instantiateClass'
                                     ]
        );
        $oManager = new $oMockManager($this);
        $oManager->setReturnValue('_instantiateClass', $oConfig);

        $aSettings[] = [
                             'section' => 'foo',
                             'key' => 'foo1',
                             'data' => 'bar1',
                            ];
        $aSettings[] = [
                             'section' => 'foo',
                             'key' => 'foo2',
                             'data' => 'bar2',
                            ];
        $this->assertTrue($oManager->_registerSettings('testPlugin', $aSettings));
        $this->assertFalse($oManager->_registerSettings('testPlugin', $aSettings));

        $oManager->expectCallCount('_instantiateClass', 2);
        $oConfig->tally();
        $oManager->tally();
    }

    public function test_unregisterSettings()
    {
        Mock::generatePartial(
            'OA_Admin_Settings',
            $oMockConfig = 'OA_Admin_Settings' . rand(),
            [
                                      'writeConfigChange'
                                     ]
        );
        $oConfig = new $oMockConfig($this);
        $oConfig->setReturnValueAt(0, 'writeConfigChange', true);
        $oConfig->setReturnValueAt(1, 'writeConfigChange', false);
        $oConfig->expectCallCount('writeConfigChange', 2);
        $oConfig->aConf['pluginGroupComponents'] = ['test' => 1];
        $oConfig->aConf['test'] = ['key' => 'val'];

        Mock::generatePartial(
            'OX_Plugin_ComponentGroupManager',
            $oMockManager = 'OX_Plugin_ComponentGroupManager' . rand(),
            [
                                      '_instantiateClass'
                                     ]
        );
        $oManager = new $oMockManager($this);
        $oManager->setReturnValue('_instantiateClass', $oConfig);
        $oManager->expectCallCount('_instantiateClass', 2);

        $aSettings[] = [
                             'section' => 'foo',
                             'key' => 'foo1',
                             'data' => 'bar1',
                            ];
        $aSettings[] = [
                             'section' => 'foo',
                             'key' => 'foo2',
                             'data' => 'bar2',
                            ];
        $this->assertEqual(count($oConfig->aConf['pluginGroupComponents']), 1);
        $this->assertEqual(count($oConfig->aConf['test']), 1);
        $this->assertTrue($oManager->_unregisterSettings('test'));
        $this->assertEqual(count($oConfig->aConf['pluginGroupComponents']), 0);
        $this->assertFalse(isset($oConfig->aConf['test']));
        $this->assertFalse($oManager->_unregisterSettings('test'));
        $oConfig->tally();
        $oManager->tally();
    }

    public function test_checkSystemEnvironment()
    {
        Mock::generatePartial(
            'OA_Environment_Manager',
            $oMockEnvMgr = 'OA_Environment_Manager' . rand(),
            [
                                      'getPHPInfo',
                                      '_checkCriticalPHP',
                                     ]
        );
        $oEnvMgr = new $oMockEnvMgr($this);
        $oEnvMgr->setReturnValueAt(0, 'getPHPInfo', ['version' => '4.3.12']);
        $oEnvMgr->setReturnValueAt(1, 'getPHPInfo', ['version' => '4.3.10']);
        $oEnvMgr->setReturnValueAt(0, '_checkCriticalPHP', OA_ENV_ERROR_PHP_NOERROR);
        $oEnvMgr->setReturnValueAt(1, '_checkCriticalPHP', OA_ENV_ERROR_PHP_NOERROR + 10);

        Mock::generatePartial(
            'OX_Plugin_ComponentGroupManager',
            $oMockManager = 'OX_Plugin_ComponentGroupManager' . rand(),
            [
                                      '_instantiateClass'
                                     ]
        );
        $oManager = new $oMockManager($this);
        $oManager->setReturnValue('_instantiateClass', $oEnvMgr);

        $aPhp[] = [
                        'name' => 'version',
                        'value' => '4.3.11',
                       ];
        $this->assertTrue($oManager->_checkSystemEnvironment('testPlugin', $aPhp));
        $this->assertFalse($oManager->_checkSystemEnvironment('testPlugin', $aPhp));

        $oManager->expectCallCount('_instantiateClass', 2);
        $oEnvMgr->expectCallCount('getPHPInfo', 2);
        $oEnvMgr->expectCallCount('_checkCriticalPHP', 2);

        $oEnvMgr->tally();
        $oManager->tally();
    }

    public function test_checkDatabaseEnvironment()
    {
        // make sure that there is a global database connection object
        $oDbh = OA_DB::singleton();
        $phptype = $oDbh->phptype;

        Mock::generatePartial(
            'OX_Plugin_ComponentGroupManager',
            $oMockManager = 'OX_Plugin_ComponentGroupManager' . rand(),
            [
                                     ]
        );
        $oManager = new $oMockManager($this);

        // Test 1 - plugin has no specific database requirements
        $aDbms = [];
        $this->assertTrue($oManager->_checkDatabaseEnvironment('testPlugin', $aDbms));

        // Test 2 - plugin does not support user database
        $aDbms[0] = [
                         'name' => $phptype,
                         'supported' => 0,
                         ];
        $this->assertFalse($oManager->_checkDatabaseEnvironment('testPlugin', $aDbms));

        // Test 3 - plugin does support user database
        $aDbms[0] = [
                         'name' => $phptype,
                         'supported' => 1,
                         ];
        $this->assertTrue($oManager->_checkDatabaseEnvironment('testPlugin', $aDbms));
    }

    public function test_checkDependenciesForInstallOrEnable()
    {
        Mock::generatePartial(
            'OX_Plugin_ComponentGroupManager',
            $oMockManager = 'OX_Plugin_ComponentGroupManager' . rand(),
            [
                                        'getComponentGroupVersion',
                                     ]
        );
        $oManager = new $oMockManager($this);


        $oManager->setReturnValueAt(0, 'getComponentGroupVersion', '0.1');
        $oManager->setReturnValueAt(0, 'getComponentGroupVersion', '2.0');


        // Test 1 - fails because testPlugin depends on foo which is not installed
        unset($GLOBALS['_MAX']['CONF']['pluginGroupComponents']['foo']);
        $aDepends[0] = ['name' => 'foo', 'version' => '1.0', 'enabled' => 0];
        $this->assertFalse($oManager->_checkDependenciesForInstallOrEnable('testPlugin', $aDepends, false));

        // Test 2 - fails because testPlugin depends although foo which is installed it is an earlier version
        $GLOBALS['_MAX']['CONF']['pluginGroupComponents']['foo'] = 0;
        $aDepends[0] = ['name' => 'foo', 'version' => '1.0', 'enabled' => 0];
        $oManager->setReturnValueAt(0, 'getComponentGroupVersion', '0.1');
        $this->assertFalse($oManager->_checkDependenciesForInstallOrEnable('testPlugin', $aDepends, false));

        // Test 3 - success
        $GLOBALS['_MAX']['CONF']['pluginGroupComponents']['foo'] = 0;
        $aDepends[0] = ['name' => 'foo', 'version' => '1.0', 'enabled' => 0];
        $oManager->setReturnValueAt(1, 'getComponentGroupVersion', '1.0');
        $this->assertTrue($oManager->_checkDependenciesForInstallOrEnable('test', $aDepends, false));

        $oManager->expectCallCount('getComponentGroupVersion', 2);
        $oManager->tally();
        unset($GLOBALS['_MAX']['CONF']['pluginGroupComponents']['foo']);
    }


    public function test_hasDependencies()
    {
        Mock::generatePartial(
            'OX_Plugin_ComponentGroupManager',
            $oMockManager = 'OX_Plugin_ComponentGroupManager' . rand(),
            [
                                      '_loadDependencyArray'
                                     ]
        );
        $oManager = new $oMockManager($this);
        $aDepends['foo']['isDependedOnBy'][0] = 'bar';
        $oManager->setReturnValue('_loadDependencyArray', $aDepends);

        // user wants to disable or uninstall plugin foo
        // foo needs to find out if other plugins rely on it being installed

        // Test 1 - no plugins are dependent on bar
        $this->assertFalse($oManager->_hasDependencies('bar'));

        // Test 2 - bar relies on foo being installed; bar is not installed
        $this->assertFalse($oManager->_hasDependencies('foo'));

        // Test 3 - bar relies on foo being installed; bar is actually installed
        $GLOBALS['_MAX']['CONF']['pluginGroupComponents']['bar'] = 1;
        $this->assertTrue($oManager->_hasDependencies('foo', false));

        $oManager->expectCallCount('_loadDependencyArray', 3);
        $oManager->tally();

        unset($GLOBALS['_MAX']['CONF']['pluginGroupComponents']['bar']);
    }

    /**
     * see integration test
     */
    /*function test_checkMenus()
    {
        $oManager = new OX_Plugin_ComponentGroupManager();
        $this->assertTrue($oManager->_checkMenus('test'));
    }*/

    public function test_registerSchema()
    {
        Mock::generatePartial(
            'OX_Plugin_ComponentGroupManager',
            $oMockManager = 'OX_Plugin_ComponentGroupManager' . rand(),
            [
                                      '_createTables',
                                      '_dropTables',
                                       '_registerSchemaVersion',
                                       '_putDataObjects',
                                      '_cacheDataObjects',
                                      '_verifyDataObjects'
                                     ]
        );
        $oManager = new $oMockManager($this);
        $oManager->setReturnValue('_dropTables', true);

        // Test 1 - no tables to create
        $this->assertTrue($oManager->_registerSchema('test', ['mdb2schema' => false]));

        // Test 2 - failure to create tables
        $oManager->setReturnValueAt(0, '_createTables', false);
        $this->assertFalse($oManager->_registerSchema('test', ['mdb2schema' => true]));

        // Test 3 - success creating tables, schema registration fails
        $oManager->setReturnValueAt(1, '_createTables', true);
        $oManager->setReturnValueAt(0, '_registerSchemaVersion', false);
        $this->assertFalse($oManager->_registerSchema('test', ['mdb2schema' => true]));

        // Test 4 - success creating tables, failed to copy dataobjects
        $oManager->setReturnValueAt(2, '_createTables', true);
        $oManager->setReturnValueAt(1, '_registerSchemaVersion', true);
        $oManager->setReturnValueAt(0, '_putDataObjects', false);
        $this->assertFalse($oManager->_registerSchema('test', ['mdb2schema' => true]));

        // Test 5 - success copying dataobjects, failed to cache dataobjects
        $oManager->setReturnValueAt(3, '_createTables', true);
        $oManager->setReturnValueAt(2, '_registerSchemaVersion', true);
        $oManager->setReturnValueAt(1, '_putDataObjects', true);
        $oManager->setReturnValueAt(0, '_cacheDataObjects', false);
        $this->assertFalse($oManager->_registerSchema('test', ['mdb2schema' => true]));

        // Test 6 - success caching dataobjects, failed to verify dataobjects
        $oManager->setReturnValueAt(4, '_createTables', true);
        $oManager->setReturnValueAt(3, '_registerSchemaVersion', true);
        $oManager->setReturnValueAt(2, '_putDataObjects', true);
        $oManager->setReturnValueAt(1, '_cacheDataObjects', true);
        $oManager->setReturnValueAt(0, '_verifyDataObjects', false);
        $this->assertFalse($oManager->_registerSchema('test', ['mdb2schema' => true]));

        // Test 5 - success
        $oManager->setReturnValueAt(5, '_createTables', true);
        $oManager->setReturnValueAt(4, '_registerSchemaVersion', true);
        $oManager->setReturnValueAt(3, '_putDataObjects', true);
        $oManager->setReturnValueAt(2, '_cacheDataObjects', true);
        $oManager->setReturnValueAt(1, '_verifyDataObjects', true);
        $this->assertTrue($oManager->_registerSchema('test', ['mdb2schema' => true]));

        $oManager->expectCallCount('_createTables', 6);
        $oManager->expectCallCount('_dropTables', 5);
        $oManager->expectCallCount('_registerSchemaVersion', 5);
        $oManager->expectCallCount('_putDataObjects', 4);
        $oManager->expectCallCount('_cacheDataObjects', 3);
        $oManager->expectCallCount('_verifyDataObjects', 2);

        $oManager->tally();
    }

    public function test_unregisterSchema()
    {
        Mock::generatePartial(
            'OX_Plugin_ComponentGroupManager',
            $oMockManager = 'OX_Plugin_ComponentGroupManager' . rand(),
            [
                                      '_dropTables',
                                      '_unregisterSchemaVersion',
                                     ]
        );
        $oManager = new $oMockManager($this);

        $this->assertTrue($oManager->_unregisterSchema('test', ['mdb2schema' => false]));

        $oManager->setReturnValueAt(0, '_dropTables', false);
        $this->assertFalse($oManager->_unregisterSchema('test', ['mdb2schema' => true]));

        $oManager->setReturnValueAt(1, '_dropTables', true);
        $oManager->setReturnValueAt(0, '_unregisterSchemaVersion', false);
        $this->assertFalse($oManager->_unregisterSchema('test', ['mdb2schema' => true]));

        $oManager->setReturnValueAt(2, '_dropTables', true);
        $oManager->setReturnValueAt(1, '_unregisterSchemaVersion', true);
        $this->assertTrue($oManager->_unregisterSchema('test', ['mdb2schema' => true]));

        $oManager->expectCallCount('_dropTables', 3);
        $oManager->expectCallCount('_unregisterSchemaVersion', 2);
        $oManager->tally();
    }

    public function test_createTables()
    {
        Mock::generatePartial(
            'OA_DB_Table',
            $oMockTable = 'OA_DB_Table' . rand(),
            [
                                      'init',
                                      'createTable',
                                      'dropAllTables',
                                     ]
        );
        $oTable = new $oMockTable($this);
        $oTable->aDefinition = [
                                     'name' => 'testPlugin',
                                     'version' => 001,
                                     'tables' => ['testplugin_table' => []],
                                     ];
        Mock::generatePartial(
            'OX_Plugin_ComponentGroupManager',
            $oMockManager = 'OX_Plugin_ComponentGroupManager' . rand(),
            [
                                        '_instantiateClass',
                                        '_dropTables',
                                        '_auditInit',
                                        '_auditSetKeys',
                                        '_auditStart',
                                     ]
        );
        ;
        $oManager = new $oMockManager($this);

        $oManager->setReturnValue('_instantiateClass', $oTable);
        /*        $oManager->setReturnValue('_auditSetKeys', true);
                $oManager->setReturnValue('_auditStart', true);*/
        $oManager->setReturnValue('_dropTables', true);

        $aSchema = ['mdb2schema' => 'foo'];

        // Test 1 - initialise schema fails
        $oTable->setReturnValueAt(0, 'init', false);
        $this->assertFalse($oManager->_createTables('test', $aSchema));

        // Test 2 - table creation fails
        $oTable->setReturnValueAt(1, 'init', true);
        $oTable->setReturnValueAt(0, 'createTable', false);
        $oManager->setReturnValueAt(0, '_dropTables', true);
        $this->assertFalse($oManager->_createTables('test', $aSchema));

        // Test 3 - success
        $oTable->setReturnValueAt(2, 'init', true);
        $oTable->setReturnValueAt(1, 'createTable', true);
        $this->assertTrue($oManager->_createTables('test', $aSchema));

        $oTable->expectCallCount('init', 3);
        $oTable->expectCallCount('createTable', 2);

        $oManager->expectCallCount('_instantiateClass', 3);
        $oManager->expectCallCount('_dropTables', 1);
        //$oManager->expectCallCount('_auditDatabaseAction',4);

        $oTable->tally();
        $oManager->tally();
    }

    public function test_dropTables()
    {
        Mock::generatePartial(
            'OA_DB_Table',
            $oMockTable = 'OA_DB_Table' . rand(),
            [
                                      'init',
                                      'dropTable',
                                     ]
        );
        $oTable = new $oMockTable($this);
        $oTable->aDefinition = [
                                     'name' => 'testPlugin',
                                     'version' => 001,
                                     'tables' => ['testplugin_table' => []]
                                     ];
        Mock::generatePartial(
            'OX_Plugin_ComponentGroupManager',
            $oMockManager = 'OX_Plugin_ComponentGroupManager' . rand(),
            [
                                        '_instantiateClass',
                                        '_auditInit',
                                        '_auditSetKeys',
                                        '_auditStart',
                                        '_tableExists',
                                     ]
        );
        ;
        $oManager = new $oMockManager($this);

        $oManager->setReturnValue('_instantiateClass', $oTable);
        //$oManager->setReturnValue('_auditDatabaseAction', true);

        // Test 1 - initialise schema fails
        $oTable->setReturnValueAt(0, 'init', false);

        $aSchema = ['mdb2schema' => 'foo'];

        $this->assertFalse($oManager->_dropTables('test', $aSchema));

        // Test 2 - table drop fails and table still exists
        $oTable->setReturnValueAt(1, 'init', true);
        $oTable->setReturnValueAt(0, 'dropTable', false);
        $oManager->setReturnValueAt(0, '_tableExists', true);

        $this->assertFalse($oManager->_dropTables('test', $aSchema));

        // Test 3 - table drop fails because table did not exist, so thats ok :)
        $oTable->setReturnValueAt(2, 'init', true);
        $oTable->setReturnValueAt(1, 'dropTable', false);
        $oManager->setReturnValueAt(1, '_tableExists', false);

        $this->assertTrue($oManager->_dropTables('test', $aSchema));

        // Test 3 - success
        $oTable->setReturnValueAt(3, 'init', true);
        $oTable->setReturnValueAt(2, 'dropTable', true);

        $this->assertTrue($oManager->_dropTables('test', $aSchema));

        $oTable->expectCallCount('init', 4);
        $oTable->expectCallCount('dropTable', 3);
        $oManager->expectCallCount('_tableExists', 2);
        //$oManager->expectCallCount('_auditDatabaseAction',6);
        $oManager->expectCallCount('_instantiateClass', 4);

        $oTable->tally();
        $oManager->tally();
    }

    public function test_auditInit()
    {
        Mock::generatePartial(
            'OA_UpgradeAuditor',
            $oMockAuditor = 'OA_UpgradeAuditor' . rand(),
            [
                                        'init',
                                     ]
        );
        $oAuditor = new $oMockAuditor($this);
        $oAuditor->setReturnValue('init', true);

        Mock::generatePartial(
            'OX_Plugin_ComponentGroupManager',
            $oMockManager = 'OX_Plugin_ComponentGroupManager' . rand(),
            [
                                        '_instantiateClass',
                                     ]
        );
        ;
        $oManager = new $oMockManager($this);
        $oManager->setReturnValue('_instantiateClass', $oAuditor);
        $oManager->expectCallCount('_instantiateClass', 1);

        $oManager->_auditInit();
        $this->assertIsA($oManager->oAuditor, get_class($oAuditor));

        $oManager->_auditInit();

        $oAuditor->expectCallCount('init', 1);

        $oAuditor->tally();
        $oManager->tally();
    }

    public function test_checkDatabase()
    {
        /**
         * NOT YET IMPLEMENTED
         *
         * method is a demo utility only
         */
    }

    public function test_canUpgradeComponentGroup()
    {
        Mock::generatePartial(
            'OX_Plugin_UpgradeComponentGroup',
            $oMockUpgrade = 'OX_Plugin_UpgradeComponentGroup' . rand(),
            [
                                      'canUpgrade'
                                     ]
        );
        $oUpgrade = new $oMockUpgrade($this);
        $oUpgrade->expectCallCount('canUpgrade', 5);

        Mock::generatePartial(
            'OX_Plugin_ComponentGroupManager',
            $oMockManager = 'OX_Plugin_ComponentGroupManager' . rand(),
            [
                                      '_getOX_Plugin_UpgradeComponentGroup',
                                     ]
        );
        $oManager = new $oMockManager($this);

        $oManager->setReturnValue('_getOX_Plugin_UpgradeComponentGroup', $oUpgrade);

        $aComponentGroup = ['name' => 'foo',
                         'version' => '1.0.0',
                        ];

        // Test 1 - can upgrade
        $oUpgrade->existing_installation_status = OA_STATUS_PLUGIN_CAN_UPGRADE;
        $this->assertTrue($oManager->_canUpgradeComponentGroup($aComponentGroup));
        $this->assertEqual($aComponentGroup['status'], OA_STATUS_PLUGIN_CAN_UPGRADE);

        // Test 2 - current version, returns true as it is not technically a potential failure to upgrade
        $oUpgrade->existing_installation_status = OA_STATUS_PLUGIN_CURRENT_VERSION;
        $this->assertTrue($oManager->_canUpgradeComponentGroup($aComponentGroup));
        $this->assertEqual($aComponentGroup['status'], OA_STATUS_PLUGIN_CURRENT_VERSION);

        // Test 3 - not installed, returns true as it is not technically a potential failure to upgrade
        $oUpgrade->existing_installation_status = OA_STATUS_PLUGIN_NOT_INSTALLED;
        $this->assertTrue($oManager->_canUpgradeComponentGroup($aComponentGroup));
        $this->assertEqual($aComponentGroup['status'], OA_STATUS_PLUGIN_NOT_INSTALLED);

        // Test 4 - bad version (version lower than current or current version not obtained)
        $oUpgrade->existing_installation_status = OA_STATUS_PLUGIN_VERSION_FAILED;
        $this->assertFalse($oManager->_canUpgradeComponentGroup($aComponentGroup));
        $this->assertEqual($aComponentGroup['status'], OA_STATUS_PLUGIN_VERSION_FAILED);

        // Test 5 - database integrity check failed
        $oUpgrade->existing_installation_status = OA_STATUS_PLUGIN_DBINTEG_FAILED;
        $this->assertFalse($oManager->_canUpgradeComponentGroup($aComponentGroup));
        $this->assertEqual($aComponentGroup['status'], OA_STATUS_PLUGIN_DBINTEG_FAILED);
    }

    public function test_upgradeComponentGroup()
    {
        Mock::generatePartial(
            'OX_Plugin_UpgradeComponentGroup',
            $oMockUpgrade = 'OX_Plugin_UpgradeComponentGroup' . rand(),
            [
                                      'canUpgrade',
                                      'upgrade',
                                     ]
        );
        $oUpgrade = new $oMockUpgrade($this);
        $oUpgrade->setReturnValue('canUpgrade', true);
        $oUpgrade->setReturnValueAt(0, 'upgrade', false);
        $oUpgrade->setReturnValueAt(1, 'upgrade', true);
        $oUpgrade->expectCallCount('upgrade', 2);

        Mock::generatePartial(
            'OX_Plugin_ComponentGroupManager',
            $oMockManager = 'OX_Plugin_ComponentGroupManager' . rand(),
            [
                                      '_getOX_Plugin_UpgradeComponentGroup',
                                     ]
        );
        $oManager = new $oMockManager($this);

        $oManager->setReturnValue('_getOX_Plugin_UpgradeComponentGroup', $oUpgrade);

        $aComponentGroup = ['name' => 'foo',
                         'version' => '1.0.0',
                        ];

        $this->assertEqual($oManager->upgradeComponentGroup($aComponentGroup), UPGRADE_ACTION_UPGRADE_FAILED);

        $this->assertEqual($oManager->upgradeComponentGroup($aComponentGroup), UPGRADE_ACTION_UPGRADE_SUCCEEDED);
    }
}

class Mock_OX_Plugin_ComponentGroupManager extends OX_Plugin_ComponentGroupManager
{
    public function task1()
    {
    }
    public function task2()
    {
    }
    public function untask1()
    {
    }
    public function untask2()
    {
    }
}

class Mock_stdClass
{
    public $error;

    public function setInputFile()
    {
    }
    public function parse()
    {
    }

    public function getApplicationVersion()
    {
    }
    public function getSchemaVersion()
    {
    }
    public function putApplicationVersion()
    {
    }
    public function putSchemaVersion()
    {
    }
    public function removeVersion()
    {
    }
    public function removeVariable()
    {
    }
}
