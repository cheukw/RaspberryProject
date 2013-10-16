<?php
App::uses('Partner', 'Model');

/**
 * Partner Test Case
 *
 */
class PartnerTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.partner',
		'app.service',
		'app.user_partner'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Partner = ClassRegistry::init('Partner');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Partner);

		parent::tearDown();
	}

}
