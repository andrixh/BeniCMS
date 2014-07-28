<?php
class TemplateHelper {

	const COMPONENT = 1;
	const CONTENT = 2;

	protected $_template = '';
	protected $_scheme = array();

	protected $_schemeFields = array();
	protected $_instanceFields = array();
	protected $_fields = array();

	public function setPurpose($purpose){
		if ($purpose == static::COMPONENT){
			$this->_schemeFields = array('typeID','label','icon');
			$this->_instanceFields = array('ID','componentID');
		} else if ($purpose == static::CONTENT){
			$this->_schemeFields = array('typeID','label','icon');
			$this->_instanceFields = array('ID','contentID');
		}
		return $this;
	}

	public function setTemplate($template){
		$this->_template = $template;
		return $this;
	}

	public function setScheme($scheme){
		$result = array();
		foreach ($scheme as $schemePart){
			$result[$schemePart->name]= $schemePart;
		}
		$this->_scheme = $result;
		return $this;
	}

	public function calculateFields(){
		$pattern = "/({[A-Za-z0-9-_\.]+})/";
		$matches = array();
		preg_match_all($pattern,$this->_template,$matches,PREG_PATTERN_ORDER);

		$fields = array();
		foreach ($matches[0] as $match){
			$fieldParts = explode('.',$match);
			$fieldName = str_replace('{','',str_replace('}','',$fieldParts[0]));
			_d($fieldName);
			if (!in_array($fieldName,$fields)){
				_d($fields,'not found currently in fields');
				if (!in_array($fieldName,$this->_schemeFields)){
					_d($this->_schemeFields,'not found currently in schemeFields');
					_d('adding');
					$fields[] = $fieldName;
				}
			}

		}
		$this->_fields = array_unique(array_merge($this->_instanceFields, $fields));
		_d($this->_fields);
	}

	public function getFields(){
		_d($this->_fields, 'getFields: ');
		return $this->_fields;
	}

	/*Temlate Notes
	  boolean, string, number, date - No Change

	  select - get selection values from providers

	  mlstring - get primary language and trim

	  mlhtml - get primary language and strip tags, then trim

	  mlgallery = get primary language and
		  gallery - get first images: physicalname, type

	  mlfiles = get primary language and
		  files - get files label and extension
  */

	public function extractInformation($row){
		$result = array();
		global $db;
		_g('ExtractInformation');
		_d($row);

		foreach ($row as $name=>$data){
			$currData = $data;
			if (array_key_exists($name,$this->_scheme)){
				if (in_array($this->_scheme[$name]->type,array('mlstring','mlgallery','mlhtml','mlfiles'))){
					$str = mlString::Create($data)->defaultValue();
					_d($str,'ml result');
					if ($this->_scheme[$name]->type == 'mlhtml'){
						$currData = mb_substr(strip_tags($str),0,30);
					} else if  ($this->_scheme[$name]->type == 'mlstring'){
						$currData = mb_substr($str,0,30);
					} else {
						$elements = json_decode($str);
						if (is_array($elements) && count($elements)>0){
							$currElement = $elements[0];
							if ($currElement->resourceType == 'image'){
								$currData = array('physicalName'=>$currElement->physicalName,'type'=>$currElement->type);
							} else if ($currElement->resourceType == 'video'){
								$currData = array('physicalName'=>$currElement->thumbnail,'type'=>$currElement->thumbnailType);
							} else if ($currElement->resourceType == 'file'){
								$currData = array('fileName'=>$currElement->fileName,'extension'=>$currElement->extension);
							}
						}
					}
				} else if (in_array($this->_scheme[$name]->type,array('gallery','files'))){
					$elements = json_decode($data);
					if (is_array($elements) && count($elements)>0){
						$currElement = $elements[0];
						if ($currElement->resourceType == 'image'){
							$currData = array('physicalName'=>$currElement->physicalName,'type'=>$currElement->type);
						} else if ($currElement->resourceType == 'video'){
							$currData = array('physicalName'=>$currElement->thumbnail,'type'=>$currElement->thumbnailType);
						} else if ($currElement->resourceType == 'file'){
							$currData = array('fileName'=>$currElement->fileName,'extension'=>$currElement->extension);
						}
					}
				} else if (in_array($this->_scheme[$name]->type,array('content'))){
					$currData = '(content)';
				} else if (in_array($this->_scheme[$name]->type,array('select'))){
					$provider = $this->_scheme[$name]->options->select_provider;
                    $options = json_decode(DB::val(Query::Select('selectproviders')->fields('options')->eq('providerID',$provider)->limit(1)));
					$currData = $options[$data];
				}
				$result[$name]=$currData;
			} else if (in_array($name,$this->_instanceFields)) {
				$result[$name]=$data;
			}

		}
		_d($result);
		_u();
		return $result;
	}


}