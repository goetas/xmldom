<?php
namespace goetas\xml;
class XMLDom extends \DOMDocument implements \Serializable, XMLAble {
	protected $xp;
	public function __construct($version = '1.0', $enc = 'UTF-8') {
		parent::__construct( $version, $enc );
		$this->registerNodeClass( '\DOMElement', __NAMESPACE__.'\XMLDomElement' );
	}
	public function insertAfter($new, $ref){
		if($ref->nextSibling){
			$this->insertBefore($new,$ref->nextSibling);
		}else{
			$this->appendChild($new);
		}
	}
	/**
	 * @return XMLDom
	 */
	public static function adapt(\DOMDocument $dom) {
		$new = new self();
		foreach ($dom->childNodes as $child){
			$new->appendChild($new->importNode($child, true));
		}
		return $new;
	}
	protected $prefixes = array();
	public function getPrefixFor($ns) {
		if(!isset($this->prefixes[$ns])){
			$part = trim($ns,"/");
			$part = strtolower(substr($part, strrpos($part, "/")+1,3));
			if(strlen($part) && !in_array($part, $this->prefixes) && $part!=='xml'){
				$this->prefixes[$ns] = $part;
			}else{
				$this->prefixes[$ns] = "ns".count($this->prefixes);
			}
			if ($this->documentElement){
				$this->documentElement->setAttribute("xmlns:".$this->prefixes[$ns], $ns);
			}
		}
		return $this->prefixes[$ns];
	}
	public function loadXMLStrict($string) {
		if(strlen(trim($string))==0){
			throw new \DOMException( "Errore caricamento stringa XML, stringa vuota.");
		}
		libxml_use_internal_errors( true );
		$res = $this->loadXML( $string );
		if(! $res ){
			$errors = libxml_get_errors();
			libxml_clear_errors();
			libxml_use_internal_errors( false );
			throw new \DOMException( "Errore caricamento stringa XML.\n" .self::libxml2string($errors));
		}
		libxml_use_internal_errors( false );
		return $res;
	}
	public function loadXMLStrictFile($fileName) {

		libxml_use_internal_errors( true );
		$res = $this->load ( $fileName );
		if(! $res ){
			$errors = libxml_get_errors();
			libxml_clear_errors();
			libxml_use_internal_errors( false );
			throw new \DOMException( "Errore caricamento file $fileName.\n" .self::libxml2string($errors));
		}
		libxml_use_internal_errors( false );
		return $res;
	}
	/**
	 * @return XMLDom
	 */
	public static function loadXMLString($s) {
		$x = new static( );
		libxml_use_internal_errors( true );
		if(! @$x->loadXML( $s )){
			$errors = libxml_get_errors();

			libxml_clear_errors();
			libxml_use_internal_errors( false );
			throw new \DOMException( "Errore caricamento stringa '".(strlen($s)>30?substr($s,0,27):$s)."'\n" .self::libxml2string($errors));
		}
		libxml_use_internal_errors( false );
		return $x;
	}
	/**
	 * @return XMLDom
	 */
	public static function loadXMLFile($file) {
		if(!is_file($file)){
			throw new \DOMException("Non trovo il file '$file'");
		}
		$x = new static( );
		libxml_use_internal_errors( true );
		if(! @$x->load( $file )){
			$errors = libxml_get_errors();

			libxml_clear_errors();
			libxml_use_internal_errors( false );

			throw new \DOMException( "Errore caricamento file '{$file}'.\n". self::libxml2string($errors));
		}
		libxml_use_internal_errors( false );
		return $x;
	}
	protected  static function libxml2string($errors){
		$s ='';
		foreach ($errors as $error) {
			$s.= trim($error->message)." (row:".$error->line.", col:".$error->column.");\n";
		}
		return $s;

	}
	/*
	 * @return XMLDom
	 */
	public function toDOM() {
		return $this;
	}
	public function addCdataChild($value) {
		$cdata = $this->createCDATASection( $value );
		$this->appendChild( $cdata );
	}
	/**
	 * @return \goetas\xml\XMLDomElement
	 */
	public function addChild($name, $value = null,$cdata=0) {
		if(! isset( $value ) || is_scalar( $value ) || is_null( $value )){
			if($cdata && $value!==null){
				$c=$this->createElement($name);
				$c->addCdataChild($value);
			}elseif($value!==null){
				$c=$this->createElement($name, $value);
			}else{
				$c=$this->createElement($name);
			}
		}elseif($value instanceof \DOMElement){
			$c = $this->createElement( $name );
			$c->appendChild( $value );
		}else{
			throw new \DOMException( 'unsppoorted type: ' . get_class( $value ) );
		}
		$this->appendChild( $c );
		return $c;
	}

	/**
	 * @return XPath
	 */
	public function getXpath() {
		return new XPath($this);
	}
	/**
	 * @return DOMNodeList
	 */
	public function xpath($xpath, array $ns = array()) {
		return $this->query( $xpath, $ns );
	}
	public function query($xpath, array $ns = array()) {
		$xp = new \DOMXpath( $this );
		foreach ( $ns as $prefix => $uri ){
			$xp->registerNamespace( $prefix, $uri );
		}
		return $xp->query( $xpath );
	}
	public function evaluate($xpath, array $ns = array()) {
		$xp = new \DOMXpath( $this );
		foreach ( $ns as $prefix => $uri ){
			$xp->registerNamespace( $prefix, $uri );
		}
		return $xp->evaluate( $xpath );
	}
	public function singleQuery($xpath, array $ns = array()) {
		$list = $this->evaluate( $xpath, $ns );
		if($list instanceof \DOMNodeList){
			return $list->length > 0?$list->item( 0 )->nodeValue:null;
		}elseif($list instanceof \DOMNode){
			return $list->nodeValue;
		}
		return $list;
	}


	public function appendExternalDocument($domable, $deep = true) {
		if($domable instanceof \DOMDocument){
			$d = $domable;
		}elseif($domable instanceof XMLAble){
			$d = $domable->toDOM();
		}else{
			throw new \InvalidArgumentException("Arg 1 passato a".__METHOD__." deve essere \\DOMDocument o ".__NAMESPACE__."\\XMLAble");
		}
		$xpath = new \DOMXpath( $d );

		$nodeList = $xpath->query( "/*" );

		foreach ( $nodeList as $node ){
			$newNode = $this->ownerDocument->importNode( $node, $deep );
			$last = $this->appendChild( $newNode );
		}
		return $last;
	}
	public function appendExternalElement(\DOMElement $n, $deep = true) {
		return $this->appendChild( $this->importNode( $n, $deep ) );
	}
	/**
	 * @return array
	 */
	public function allNamespaces() {
		return self::getNS($this->documentElement) ;
	}
	/**
	 *
	 * @return array
	 */
	private static function getNS(\DOMElement $node) {
		$namespaces = array();
		$namespaces[$node->namespaceURI]=$node->namespaceURI;
		foreach ($node->attributes as $attribute){
			$namespaces[$attribute->namespaceURI]=$attribute->namespaceURI;
		}
		foreach ($node->childNodes as $n){
			if($n->nodeType == \XML_ELEMENT_NODE){
				$namespaces=array_merge($namespaces, self::getNS($n));
			}
		}
		return $namespaces;
	}
	/**
	 * @return XMLDomElement
	 */
	public function addChildNS($ns, $name, $value = null) {
		$c=$this->createElementNS($ns, $name);
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
	public function serialize() {
		return serialize(array($this->saveXML(),$this->documentURI));
	}
	public function unserialize($data) {
		static::__construct();
		list($xml, $uri)=unserialize($data);
		$this->loadXML( $xml );
		$this->documentURI = $uri;
	}
	public function __toString() {
		return $this->saveXML();
	}
	public function renameNode(\DOMNode $nodo, $namespaceURI, $qualifiedName){
		if (0 && method_exists($this, 'renameNode')){
			return parent::renameNode($nodo, $namespaceURI, $qualifiedName);
		} else {
			$new_nodo = $this->createElementNS($namespaceURI, $qualifiedName);

			if ($nodo->attributes){
				foreach ($nodo->attributes as $a){
					$new_nodo->setAttributeNode($this->importNode( $a, true ));
				}
			}
			$tot = $nodo->childNodes->length;

			for ($k = 0; $k < $nodo->childNodes->length; $k++){
				$d = $this->importNode( $nodo->childNodes->item($k)->cloneNode(true), true );
				$new_nodo->appendChild($d);
			}
			$nodo->replaceMe($new_nodo);

			return $new_nodo;
		}
	}
}