<?php
class BasicTest extends PHPUnit_Framework_TestCase
{
    public function testNewObject()
    {
        $obj = new \goetas\xml\XMLDom();
        $this->assertTrue(is_object($obj));
    }
    public function testNewLoadFile()
    {
        $obj = new \goetas\xml\XMLDom();
        $result = $obj->loadXMLStrictFile(__DIR__."/xml/no_ns.xml");
        $this->assertTrue($result);
    }
    public function testAddChilds()
    {
        $obj = new \goetas\xml\XMLDom();
        $obj->addChild("goetas");
        $this->assertContains("<goetas", $obj->saveXML());
    }
    public function testAppendExternalDocument()
    {
        $a = new \goetas\xml\XMLDom();
        $a->loadXMLStrictFile(__DIR__."/xml/no_ns.xml");

        $obj = new \goetas\xml\XMLDom();
        $root = $obj->addChild("goetas");
        $root->appendExternalDocument($a);

        $this->assertContains("<goetas>", $obj->saveXML());
    }
    public function testAppendExternalElement()
    {
        $a = new \goetas\xml\XMLDom();
        $a->loadXMLStrictFile(__DIR__."/xml/no_ns.xml");

        $obj = new \goetas\xml\XMLDom();
        $root = $obj->addChild("goetas");
        $root->appendExternalElement($a->documentElement, true);

        $this->assertContains("<testsuites>", $obj->saveXML());
        $this->assertContains("<goetas>", $obj->saveXML());
    }
    public function testAdapt()
    {
        $a = new \DOMDocument("1.0", "UTF-8");
        $a->load(__DIR__."/xml/no_ns.xml");

        $obj = \goetas\xml\XMLDom::adapt($a);
        $this->assertTrue($obj instanceof \goetas\xml\XMLDom);
        $this->assertContains("<testsuites>", $obj->saveXML());
    }
}
