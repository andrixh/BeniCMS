<?php
class mlString{
	public static $languages=[];
	public static $languages_ext=[];
	protected $values = [];
	protected $originalValues = [];
	public $strID = '';
	protected $originalStrID = '';
	protected $_usedTable = '';
	protected $_usedID = '';
	protected $_postName = '';
    protected $_index = false;
	protected $existsInDb = false;
	protected $changed = false;
	
	public static function Create($strID=''){
		return new mlString($strID);
	}

    protected static function populateLanguages(){
        $allLanguages = include($_SERVER['DOCUMENT_ROOT'].'/Config/Languages/languages.php');

        $langCodes = DB::col(Query::Select('languages')->fields('langID')->desc('main')->asc('rank'),DB::ASSOC);

        foreach ($langCodes as $lc){
            self::$languages[] = $lc;
            self::$languages_ext[$lc] = $allLanguages[$lc];
            self::$languages_ext[$lc]['langID'] = $lc;
            self::$languages_ext[$lc]['readonly'] = false;
        }
    }


	public function __construct($strID=''){
		if (!self::$languages){
            self::populateLanguages();
		}
		foreach (self::$languages as $language){
			$this->values[$language] = '';
			$this->originalValues[$language] = '';
		}
		if ($strID == ''){
			$this->strID = self::generateDynamicStrID();
		} else {
			$this->load($strID);
		}
		return $this;
	}
	
	public function getValues(){
		return $this->values;
	}
	
	public function setValues($values){
		$this->values = $values;
		if ($this->isEmpty()){
			$this->strID = '';
		}
		return $this;
	}
	
	public function usedTable($usedTable=''){
		if ($usedTable == ''){
			return $this->_usedTable;
		} else {
			$this->_usedTable = $usedTable;
			return $this;
		}
	}
	
	public function usedID($usedID=''){
		if ($usedID == ''){
			return $this->_usedID;
		} else {
			$this->_usedID = $usedID;
			return $this;
		}
	}

    public function index($index=''){
        if ($index == ''){
            return $this->_index;
        } else {
            $this->_index = $index;
            return $this;
        }
    }
	
	
	public function postName($postName=''){
		if ($postName == ''){
			return $this->_postName;
		} else {
			$this->_postName = $postName;
			return $this;
		}
	}
	
	public function defaultValue($value=null){
        if ($value === null) {
            return $this->values[mlString::$languages[0]];
        } else {
            $this->values[mlString::$languages[0]] = $value;
            $this->changed = true;
            return $this;
        }
    }
	
	public function load($strID=''){
		_d($strID);
		if ($strID == ''){
			$strID = $this->strID;
		}
		global $db;
        $query = Query::Select('mlstrings')->fields(self::$languages)->fields('strID','usedTable','usedID')->eq('strID',$strID)->limit(1);
		_d($query);
		$result = DB::row($query);
		_d($result);
		if ($result) {
			$this->existsInDb = true;
			$this->_usedTable = $result->usedTable;
			$this->_usedID = $result->usedID;
			$this->strID = $result->strID;
			$this->originalStrID = $result->strID;
			foreach(mlString::$languages as $language){
				$this->values[$language] = $result->{$language};
				$this->originalValues[$language] = $result->{$language};
			}
		}
		return $this;
	}
	
	public function fromPost($postName=''){
		if ($postName!=''){
			$this->postName($postName);
		}
		foreach (mlString::$languages as $language){
			if (isset($_POST[$this->_postName.'_'.$language])){
				
				$this->values[$language] = $_POST[$this->_postName.'_'.$language];
				if ($this->changed == false && $this->values[$language] != $this->originalValues[$language]){
					$this->changed = true;
				}
			}
		}
		if ($this->isEmpty()){
			$this->strID = '';
		}
		return $this;
	}
	
	public function isEmpty(){
		$empty = true;
		foreach ($this->values as $value){
			if ($value != ''){
				$empty = false;
				break;
			}
		}
		return $empty;
	}
	
	public function save($force=false){
		_gc(__FUNCTION__);
		_d($this);
		if ($this->changed == true){
			_d('has changed');
			if ($this->strID != '' || $force==true){
				if ($force == true){
					_d('forced save');
					$this->strID = $this->originalStrID;
				}
				_d('non-empty strID');
				$queryFields = $this->getValues();
				$queryFields['usedID']=$this->_usedID;
				$queryFields['usedTable']=$this->_usedTable;
                $queryFields['index']=$this->_index;
				if ($this->originalStrID == ''){
					_d('original strID is empty -- adding');
					$queryFields['strID']=$this->strID;
                    $query = Query::Insert('mlstrings')->pairs($queryFields);
				} else {
					_d('original strID is not empty -- updating');
                    $query = Query::Update('mlstrings')->pairs($queryFields)->eq('strID',$this->originalStrID);
				}
				_d($query,'query');
				DB::query($query);
			} else {
				_d('empty strID -- deleting');
				$this->delete();
			}
		}
		_u();
	}
	
	public function delete(){
		if ($this->originalStrID != ''){
            DB::query(Query::Delete('mlstrings')->eq('strID',$this->originalStrID));
		}
	}
	
	
	public static function generateDynamicStrID(){
		$length=20;
		$result = uniqid();
		$possible = "0123456789abcdefghijklmnopqrstuvwxyz";
		$start =strlen($result)-1;
		for($i= $start; $i<$length; $i++) { 
		  	$char = substr($possible, mt_rand(0, strlen($possible)-1), 1);   
	  		$result = $char.$result;
	  	}
	  	return $result;
	}
	
	public static function excerpt($string, $length=35){
		if (mb_strlen($string)>($length+2)){
			return mb_substr($string,0,$length).'&#8230;';
		}else{
			return $string;
		}
	}
	
   
   public function __toString() {
      return $this->strID;
   }
}
