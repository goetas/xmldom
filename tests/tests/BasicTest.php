<?php
class BasicTest extends PHPUnit_Framework_TestCase
{
	public function testNewObject()
	{
		$obj = new \goetas\xml\XMLDom();
		$this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_OBJECT, $obj);
	}
	public function testNewLoadFile()
	{
		$obj = new \goetas\xml\XMLDom();
		$result = $obj->loadXMLStrictFile(__DIR__."/xml/no_ns.xml");
		$this->assertTrue($result);
	}
	public function testNewObjectContains()
	{
		$obj = new \goetas\xml\XMLDom();
		$obj->addChild("goetas");
		$this->assertContains("goetas", $obj->saveXML());
	}
}