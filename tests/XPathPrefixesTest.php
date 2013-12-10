<?php
use goetas\xml\XMLDom;

class XPathPrefixesTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     * @var XMLDom
     */
    protected $xml;
    public function setUp()
    {
        $this->xml = new XMLDom();
        $this->xml->loadXMLStrictFile(__DIR__."/xml/wsdl.wsdl");
    }
    public function testEvaluateGenericPrefixNs()
    {
        $root = $this->xml->documentElement;
        // use "s" as prefix!
        $nodes = $root->evaluate("//s:binding[@name='WeatherSoap12']", array("s"=>"http://schemas.xmlsoap.org/wsdl/"));
        $this->assertEquals(1, $nodes->length);
    }
}
