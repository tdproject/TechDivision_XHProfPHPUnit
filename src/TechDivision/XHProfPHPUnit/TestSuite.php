<?php

/**
 * License: GNU General Public License
 *
 * Copyright (c) 2009 TechDivision GmbH.  All rights reserved.
 * Note: Original work copyright to respective authors
 *
 * This file is part of TechDivision GmbH - Connect.
 *
 * faett.net is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * faett.net is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307,
 * USA.
 *
 * @package TechDivision_XHProfPHPUnit
 */

require_once 'TechDivision/XHProfPHPUnit/xhprof_lib/utils/xhprof_lib.php';
require_once 'TechDivision/XHProfPHPUnit/xhprof_lib/utils/xhprof_runs.php';

/**
 * This class extends the PHPUnit_Framework_TestSuite and adds profiling
 * functionality provided by XHProf.
 *
 * @package TechDivision_XHProfPHPUnit
 * @author Tim Wagner <t.wagner@techdivision.com>
 * @copyright TechDivision GmbH
 * @link http://www.techdivision.com
 * @license GPL
 */
class TechDivision_XHProfPHPUnit_TestSuite extends PHPUnit_Framework_TestSuite
{

    /**
     * The internal run ID to store the profiling result under.
     * @var string
     */
    protected $_runId = 0;

    /**
     * Sets the XHProf output directory.
     * @var string
     */
    protected $_outputDirectory = '';

    /**
     * Constructs a new TestSuite:
     *
     *   - PHPUnit_Framework_TestSuite() constructs an empty TestSuite.
     *
     *   - PHPUnit_Framework_TestSuite(ReflectionClass) constructs a
     *     TestSuite from the given class.
     *
     *   - PHPUnit_Framework_TestSuite(ReflectionClass, String)
     *     constructs a TestSuite from the given class with the given
     *     name.
     *
     *   - PHPUnit_Framework_TestSuite(String) either constructs a
     *     TestSuite from the given class (if the passed string is the
     *     name of an existing class) or constructs an empty TestSuite
     *     with the given name.
     *
     * @param $theClass
     * @param $name
     * @param string $runId The run ID used to store the profiling data
     * @return void
     * @see PHPUnit_Framework_TestSuite::__construct($theClass = '', $name = '')
     */
    public function __construct($theClass = '', $name = '', $runId)
    {
        // call the constructor of the parent class
        parent::__construct($theClass, $name);
        // initialize the run ID
        $this->_runId = $runId;
        // initialize the output directory with the value from the php.ini
        $this->_outputDirectory = ini_get('xhprof.output_dir');
    }

    /**
     * This method returns the XHProf output
     * directory.
     *
     * @return string The requested output directory
     */
    public function getOutputDirectory()
    {
        return $this->_outputDirectory;
    }

    /**
     * This method sets the XHProf output directory.
     *
     * @param string The requested output directory
     * @return void
     */
    public function setOutputDirectory($outputDirectory)
    {
        $this->_outputDirectory = $outputDirectory;
    }

    /**
     * This method extends the method of the parent class to create
     * and store the profiling result file.
     *
     * Runs the tests and collects their result in a TestResult.
     *
     * @param PHPUnit_Framework_TestResult $result
     * @param mixed $filter
     * @param array $groups
     * @param array $excludeGroups
     * @param boolean $processIsolation
     * @return PHPUnit_Framework_TestResult
     * @throws InvalidArgumentException
	 * @see PHPUnit_Framework_TestSuite::run(
	 * 	        PHPUnitFramework_TestResult $result = NULL,
	 * 			$filter = FALSE,
	 * 			array $groups = array(),
	 * 			array $excludeGroups = array(),
	 * 		)
     */
    public function run(
        PHPUnit_Framework_TestResult $result = NULL,
        $filter = FALSE,
        array $groups = array(),
        array $excludeGroups = array()) {
        // check if the XHProf extension is already loaded
        if (!extension_loaded('xhprof')) {
            // if not, run the TestSuite itself and return
            return parent::run($result, $filter, $groups, $excludeGroups);
        }
        // set the output directory
        ini_set('xhprof.output_dir', $this->getOutputDirectory());
        // if yes, start profiling
        xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
        // run the TestSuite itself
        $result = parent::run($result, $filter, $groups, $excludeGroups);
        // stop profiling
        $xhprof_data = xhprof_disable();
        // initialize the  runner
        $xhprof_runs = new XHProfRuns_Default();
        // save the profiling data
        $runId = $xhprof_runs->save_run(
            $xhprof_data,
            $nameSpace = $this->getName(),
            $this->_runId
        );
        // return the TestResult
        return $result;
    }
}