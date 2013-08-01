<?php
use goetas\xml\XMLDom;

class XPathTest extends \PHPUnit_Framework_TestCase{
    /**
     *
     * @var XMLDom
     */
    protected $xml;
	public function setUp() {
		$this->xml = new XMLDom();
		$this->xml->loadXMLStrictFile(__DIR__."/xml/soap.xml");
	}
	public function testEvaluateGenericPrefixNs() {
		$root = $this->xml->documentElement;

		$nodes = $root->evaluate("//s:GetWeatherInformation", array("s"=>"http://ws.cdyne.com/WeatherWS/"));
		$this->assertEquals(1, $nodes->length);

		$nodes = $root->evaluate("//j:GetWeatherInformation", array("j"=>"http://ws.cdyne.com/WeatherWS/"));
		$this->assertEquals(1, $nodes->length);

		$nodes = $root->evaluate("//GetWeatherInformation");
		$this->assertEquals(0, $nodes->length);

	}
	public function testEvaluateNoPrefixNs() {
		$root = $this->xml->documentElement;
		// uses http://php.net/manual/en/domxpath.query.php $registerNodeNS = true
		$nodes = $root->evaluate("//weat:GetWeatherInformation");
		$this->assertEquals(1, $nodes->length);
	}
}