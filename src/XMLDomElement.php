<?php
namespace goetas\xml;
class XMLDomElement extends \DOMElement implements XMLAble{
	private static $xp;
	public function appendExternalElement(\DOMNode $e, $deep=true){
		return $this->appendChild($this->ownerDocument->importNode($e, $deep));
	}
	public function prependChild($new){
		if($this->firstChild){
			$this->insertBefore($new,$this->firstChild);
		}else{
			$this->appendChild($new);
		}
	}
	public function insertAfter($new, $ref){
		if($ref->nextSibling){
			$this->insertBefore($new,$ref->nextSibling);
		}else{
			$this->appendChild($new);
		}
	}
	/**
	 * @var XSLTProcessor
	 */
	private static $xsl;
	public function saveXML($me=true) {
		$xml = new XMLDom();

		if(!(self::$xsl instanceof \XSLTProcessor )){
			self::$xsl = new \XSLTProcessor();
			self::$xsl->importStylesheet(XMLDom::loadXMLString('<?xml version="1.0" encoding="utf-8"?>
				<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
				<xsl:output omit-xml-declaration="yes" method="xml"/>
				<xsl:template match="node()|@*" priority="-4">
					<xsl:copy>
						<xsl:apply-templates  select="@*"/>
						<xsl:apply-templates />
					</xsl:copy>
				</xsl:template>
				<xsl:template match="/" priority="-4">
					<xsl:apply-templates />
				</xsl:template>
			</xsl:stylesheet>'));
		}
		if($me){
			$xml->appendChild($xml->importNode($this,true));
			return self::$xsl->transformToXml($xml);
		}else{
			foreach ($this->childNodes as $node){
				$xml->appendChild($xml->importNode($node,true));
			}
		}
		return self::$xsl->transformToXml($xml);
	}
	public function toDOM() {
		$xml = new XMLDom();
		$xml->appendChild($xml->importNode($this,true));
		return $xml;
	}
	public function allNamespaces(){
		$namespaces=array();

		$namespaces[$this->namespaceURI]=$this->namespaceURI;
		foreach ($this->attributes as $attribute){
			$namespaces[$attribute->namespaceURI]=$attribute->namespaceURI;
		}
		if($this->parentNode->nodeType == \XML_ELEMENT_NODE && $this->parentNode instanceof XMLDomElement ){
			foreach ($this->parentNode->allNamespaces() as $ns){
				if(!$namespaces[$ns]){
					$namespaces[$ns]=$ns;
				}
			}
		}


		return $namespaces;
	}
	/**
	 * @return \goetas\xml\XMLDomElement
	 */
	public function addChild($name, $value=null, $cdata=0){
		if (!isset($value) || is_scalar($value) || is_null($value)){
			$c=$this->ownerDocument->createElement($name);
			if($cdata && $value!==null){
				$c->addCdataChild($value);
			}elseif($value!==null){
				$c->addTextChild($value);
			}
		}elseif($value instanceof \DOMElement){
			$c=$this->ownerDocument->createElement($name);
			$c->appendChild($value);
		}else{
			throw new \DOMException('unsppoorted type: '.(is_object($value)?get_class($value):gettype($value)));
		}
		$this->appendChild($c);
		return $c;
	}
	public function addComment( $value){
		if (!isset($value) || is_scalar($value) || is_null($value)){
			$c=$this->ownerDocument->createComment (  $value  );
		}else{
			throw new \DOMException('unsppoorted type: '.(is_object($value)?get_class($value):gettype($value)));
		}
		$this->appendChild($c);
		return $c;
	}

	public function __toString(){
		return $this->nodeValue;
	}
	public function query($xpath, array $ns=array()){
		if(!(self::$xp instanceof \DOMXPath) || self::$xp->document!==$this->ownerDocument){
			self::$xp = new \DOMXpath($this->ownerDocument);
		}
		foreach ($ns as $prefix=>$uri){
			self::$xp->registerNamespace($prefix,$uri);
		}
		return self::$xp->query($xpath, $this);
	}
	public function xpath($xpath, array $ns=array()){
		return $this->query($xpath,$ns);
	}
	public function singleQuery($xpath, array $ns=array()){
		if(!(self::$xp instanceof \DOMXPath) || self::$xp->document!==$this->ownerDocument){
			self::$xp = new \DOMXpath($this->ownerDocument);
		}
		foreach ($ns as $prefix=>$uri){
			self::$xp->registerNamespace($prefix,$uri);
		}
		$list = self::$xp->evaluate($xpath, $this);
		if($list->length>0){
			return $list->item(0)->nodeValue;
		}
		return null;
	}
	public function evaluate($xpath, array $ns=array()){
		if(!(self::$xp instanceof \DOMXPath) || self::$xp->document!==$this->ownerDocument){
			self::$xp = new \DOMXpath($this->ownerDocument);
		}
		foreach ($ns as $prefix=>$uri){
			self::$xp->registerNamespace($prefix,$uri);
		}
		return self::$xp->evaluate($xpath, $this);
	}
	public function removeChilds() {
		while($this->hasChildNodes()){
			$this->removeChild($this->firstChild);
		}
	}
	public function getPrefixFor($ns) {
		$prefix = $this->lookupPrefix($ns);
		if(!$prefix){
			$prefix = $this->ownerDocument->getPrefixFor($ns);
		}
		return $prefix;
	}
	public function addPrefixedChild($ns, $name, $prefix = null, $value=null) {
		return $this->addChildNS($ns, ($prefix?:$this->getPrefixFor($ns)).":".$name, $value);
	}
	/**
	 * @param string $ns
	 * @param string $name
	 * @param mixed $value
	 * @throws \DOMException
	 * @return \goetas\xml\XMLDomElement
	 */
	public function addChildNS($ns, $name, $value=null){
		$c=$this->ownerDocument->createElementNS($ns, $name);		
		if ($value===null){
	
		}elseif($value instanceof \DOMElement){
			$c->appendChild($value);
		}elseif (is_scalar($value)){
			$c->appendChild($this->ownerDocument->createTextNode($value));
		}elseif (!is_null($value)){
			throw new \DOMException('unsupported type: '. (is_object($value)?get_class($value):gettype($value)) );
		}
		$this->appendChild($c);
		return $c;
	}
	public function addCdataChild($value){
		$cdata=$this->ownerDocument->createCDATASection($value);
		$this->appendChild($cdata);
	}
	public function addTextChild($value){
		$cdata=$this->ownerDocument->createTextNode($value);
		$this->appendChild($cdata);
	}
	/**
	 * @return XMLDomElement
	 */
	public function remove(){
		return $this->parentNode->removeChild($this);
	}
	/**
	 * @return XMLDomElement
	 */
	public function replaceMe(\DOMElement $new){
		if ($this->ownerDocument===null && !$this->parentNode){
			return null;
		} elseif ($this->isSameNode($this->ownerDocument->documentElement)){
			return $this->ownerDocument->replaceChild($new, $this->ownerDocument->documentElement);
		} else {
			return $this->parentNode->replaceChild($new, $this);
		}
	}
	/**
	 * @return XMLDomElement
	 */
	public function appendExternalDocument($domable, $deep=true){
		if($domable instanceof \DOMDocument){
			$d = $domable;
		}elseif($domable instanceof XMLAble){
			$d = $domable->toDOM();
		}else{
			throw new \InvalidArgumentException("Arg 1 passato a".__METHOD__." deve essere \\DOMDocument o ".__NAMESPACE__."\\XMLAble");
		}

		$xpath = new \DOMXpath($d);
		$nodeList = $xpath->query("/*");
		foreach ( $nodeList as $node ) {
			$newNode = $this->ownerDocument->importNode( $node, $deep );
			$last=$this->appendChild($newNode);
		}
		return $last;
	}
	/**
	 * @return XMLDomElement
	 */
	public function setAttr($name, $val){
		$this->setAttribute($name, $val);
		return $this;
	}
	/**
	 * @return XMLDomElement
	 */
	public function setAttrNS($ns, $name, $val){
		$this->setAttributeNS($ns, $name, $val);
		return $this;
	}
}
?>