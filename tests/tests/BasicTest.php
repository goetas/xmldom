<?php
class BasicTest extends PHPUnit_Framework_TestCase
{
	public function testNewObject()
	{
		$obj = new \goetas\xml\XMLDom();
		$this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_OBJECT, $obj);
	}
}