<?php
namespace goetas\xml;
class XPath extends \DOMXPath{
	protected $dom;
	public function __construct(\DOMDocument $dom, $ns=array()) {
		$this->dom=$dom;
		parent::__construct($dom);
		$this->registerNamespaces($ns);
	}
	public function singleQuery($xpath, array $ns=array(), $ctx=NULL){
		foreach ($ns as $prefix=>$uri){
			$this->registerNamespace($prefix,$uri);
		}
		if($ctx instanceof  \DOMNode ){
			$list = $this->evaluate($xpath, $ctx);
		}else {
			$list = $this->evaluate($xpath);
		}
		if($list->length>0){
			return $list->item(0)->nodeValue;
		}
		return NULL;
	}
	public static function simpleXPath($xpath, \DOMDocument $dom, array $ns=array()){
		$xp=new self($dom);
		foreach ($ns as $prefix => $uri){
			$xp->registerNamespace($prefix,$uri);
		}
		return $xp->query($xpath);
	}
	public function registerNamespaces(array $ns) {
		foreach ($ns as $prefix => $uri){
			$this->registerNamespace($prefix,$uri);
		}
	}
	public static function simpleEvalXPath($xpath, \DOMDocument $dom, array $ns=array()){
		$xp=new self($dom);
		foreach ($ns as $prefix => $uri){
			$xp->registerNamespace($prefix,$uri);
		}
		return $xp->evaluate($xpath);
	}
}
