<?php

/**
 * @author Zauberfisch
 */
class RelationChipsField extends FormField {
	/**
	 * @var Boolean
	 */
	protected $multiSelect = true;
	/**
	 * @var Boolean
	 */
	protected $highlight = true;
	/**
	 * @var string
	 */
	protected $itemClass = '';
	/**
	 * @var string
	 */
	protected $idField = 'ID';
	/**
	 * @var array
	 */
	protected $fieldList = array();
	/**
	 * @var array
	 */
	protected $searchFieldList = array();
	/**
	 * @var string
	 */
	protected $resultFormat = '';
	/**
	 * @var string
	 */
	protected $toStringSeperator = ',';
	/**
	 * @var int|string|array
	 */
	protected $limit = 50;
	/**
	 * @var DataObjectSet
	 */
	protected $valueObjects;
	
	/**
	 * allow/deny selecting of multiple elements
	 * @param Boolean $multiSelect
	 */
	public function setMultiSelect($multiSelect) { $this->multiSelect = $multiSelect; }
	/**
	 * @return Boolean
	 */
	public function getMultiSelect() { return $this->multiSelect; }
	/**
	 * enable/dissable highliighting of searched string
	 * @param Boolean $highlight
	 */
	public function setHighlight($highlight) { $this->highlight = $highlight; }
	/**
	 * @return stBooleanring
	 */
	public function getHighlight() { return $this->highlight; }
	/**
	 * name of the class to be searched
	 * @param string $itemClass
	 */
	public function setItemClass($itemClass) { $this->itemClass = $itemClass; }
	/**
	 * @return string
	 */
	public function getItemClass() { return $this->itemClass; }
	/**
	 * field used as ID on objects of $itemClass (default: 'ID')
	 * @param string $idField
	 */
	public function setIdField($idField) { $this->idField = $idField; }
	/**
	 * @return string
	 */
	public function getIdField() { return $this->idField; }
	/**
	 * list of fields on the itemClass to be displayed
	 * @param array $fieldList
	 */
	public function setFieldList($fieldList) { 
		$this->fieldList = $fieldList; 
		if (!$this->getResultFormat()) {
			$format = '%s';
			for ($i = 1; $i < count($fieldList); $i++)
				$format .= ' %s';
			$this->setResultFormat($format);
		}
	}
	/**
	 * @return array
	 */
	public function getFieldList() { return $this->fieldList; }
	/**
	 * list of fields on the itemClass to be searched
	 * @param array $fieldList
	 */
	public function setSearchFieldList($fieldList) { $this->searchFieldList = $fieldList; }
	/**
	 * @return array
	 */
	public function getSearchFieldList() { return $this->searchFieldList; }
	/**
	 * Format used to generate displayed list
	 * @param string $format for sprintf (eg: "%s: %s - %s")
	 * @uses sprintf
	 */
	public function setResultFormat($format) { $this->resultFormat = $format; }
	/**
	 * @return string
	 */
	public function getResultFormat() { return $this->resultFormat; }	
	/**
	 * @param string $toStringSeperator used as seperator when saving into a Textfield (when using $form->saveInto())
	 */
	public function setToStringSeperator($toStringSeperator) { $this->toStringSeperator = $toStringSeperator; }
	/**
	 * @return string
	 */
	public function getToStringSeperator() { return $this->toStringSeperator; }
	/**
	 * limit for DataObject::get() to set how many entrys the live search returns 
	 * @param int|string|array $limit
	 */
	public function setLimit($limit) { $this->limit = $limit; }
	/**
	 * @return int|string|array
	 */
	public function getLimit() { return $this->limit; }
	/**
	 * @param array|int $set
	 */
	public function setValue($value) {
		if (!$this->getMultiSelect() && is_array($value))	
			$value = current($value);
		$this->value = $value;
	}
	/**
	 * @return array|int 
	 */
	public function getValue() {
		return $this->Value();
	}
	/**
	 * sets the DataObjectSet and the value
	 * @param DataObjectSet|array|int $set
	 * @uses RelationChipsField->setValue()
	 */
	public function setValueObjects($set) {
		$oldSet = is_a($this->valueObjects, 'DataObjectSet') ? $this->valueObjects : new DataObjectSet();
		$this->valueObjects = new DataObjectSet();
		if (is_int($set)) {
			$item = $oldSet->find($this->getIdField(), $set);
			if (!$item) $item = DataObject::get_by_id($this->getItemClass(), $set);
				if ($item && $item->exists())
					$this->valueObjects->push($item);
		} elseif (is_array($set) && is_int(current($set))) {
			$this->valueObjects = new DataObjectSet();
			foreach($set as $id) {
				$item = $oldSet->find($this->getIdField(), $id);
				if (!$item) $item = DataObject::get_by_id($this->getItemClass(), $id);
					if ($item && $item->exists())
						$this->valueObjects->push($item);
			}
		} elseif (is_a($set, 'DataObjectSet')) {
			$this->valueObjects = $set;
		} else {
			$this->valueObjects = new DataObjectSet($set);
		}
		if (!$this->getMultiSelect() && $this->valueObjects->Count() > 1)
			$this->valueObjects = new DataObjectSet($this->valueObjects->First());
		$this->setValue($this->valueObjects->column($this->getIdField()));
	}
	/**
	 * returns a set of all selected objects
	 * @return DataObjectSet
	 */
	public function getValueObjects() {
		if (
			!$this->valueObjects || 
			(!$this->valueObjects->exists()) || 
			(count(array_diff($this->Value(), $this->valueObjects->column($this->getIdField())))) ||
			(count(array_diff($this->valueObjects->column($this->getIdField()), $this->Value())))
		) {
			$this->setValueObjects($this->Value());
		}
		return $this->valueObjects;
	}
	
	/**
	 * @param string $name
	 * @param string $title
	 * @param string $itemClass name of the class to be searched
	 * @param array $fieldList list of fields on the itemClass to be displayed
	 * @param array $searchFieldList list of fields on the itemClass to be searched
	 * @param DataObjectSet|array $value
	 * @param Form $form
	 */
	public function __construct(string $name, string $title = null, string $itemClass, array $fieldList, array $searchFieldList = null, $value = null, Form $form = null) {
		parent::__construct($name, $title, array(), $form);
		$this->setItemClass($itemClass);
		$this->setFieldList($fieldList);
		$this->setSearchFieldList(empty($searchFieldList) ? $fieldList : $searchFieldList);
		$this->setValueObjects($value);
	}
	
	public static $allowed_actions = array(
		'suggest'
	);
	
	/**
	 * action, used via ajax to load live suggestions
	 * @param SS_HTTPRequest $request
	 * @return string
	 */
	public function suggest(SS_HTTPRequest $request) {
		$search = Convert::raw2sql($request->getVar('s'));
		$not = Convert::raw2sql($request->getVar('n'));
		return json_encode($this->search($search, $not));
	}
	
	/**
	 * @param string $search
	 * @return array
	 */
	protected function search($search, $not) {
		$filter = '';
		if ($search) {
			$filter = $this->getSearchFieldList();
			foreach ($filter as $class => &$field) {
				$field = "\"$field\" LIKE '%$search%'";	
				if (is_string($class))
					$field = "\"$class\".$field"; 
			}
			$filter = '('.implode(' OR ', $filter).')';
		}
		if ($not) {
			if (is_array($not)) $not = implode(', ', $not);
			if ($filter) $filter .= ' AND ';
			$filter .= "\"{$this->getItemClass()}\".\"{$this->getIdField()}\" NOT IN ($not)";
		}
		$resutlSet = DataObject::get($this->itemClass, $filter, null, null, $this->getLimit());
		if($resutlSet)
			foreach($resutlSet as $item)
				if($item->hasMethod('canView') && !$item->canView(Member::currentUser()))
					$resutlSet->remove($item);
		$result = $this->resutlSetToArray($resutlSet, $search);
		return $result;
	} 
	
	/**
	 * @param DataObjectSet $resutlSet
	 * @return array
	 */
	protected function resutlSetToArray(DataObjectSet $resutlSet, string $search = null) {
		$result = array();
		$highlightTag = $this->createTag('span', array('class' => 'highlight'), '$1');
		if ($resutlSet && $resutlSet->exists()) {
			foreach($resutlSet as $item) {
				$values = array();
				foreach ($this->getFieldList() as $field) {
					$getter = 'get'.$field;
					if ($item->hasMethod($field))
						$value = $item->$field();
					elseif ($item->hasMethod($field))
						$value = $item->$getter();
					else
						$value = $item->$field;
					$values[] = $this->getHighlight() && $search ? preg_replace("/($search)/i", $highlightTag, $value) : $value;
				}
				$result[] = array(
					'ID' => $item->ID,
					'Text' => vsprintf($this->getResultFormat(), $values)
				);
			}
		}
		return $result;
	}

	public function Field() {
		$config = array(
			'idPrefix' => $this->attrName()."_item_",
			'url' => $this->Link('suggest')
		);
		$removed = false;
		$resutlSet = $this->getValueObjects();
		if ($resutlSet)
			foreach ($resutlSet as $item)
				if ($item->hasMethod('canView') && !$item->canView(Member::currentUser())) {
					$resutlSet->remove($item);
					$removed = true;
				}
		$result = $this->resutlSetToArray($resutlSet);
		$options = '';
		$values = $removed ? $this->createTag('li', array('class' => 'warning'), _t('RelationChipsField.CANVIEWWARNING', 'You are not allowed to view all selected items, they will be overwritten!')) : '';
		foreach ($result as $item) {
			$id = $config['idPrefix'].$item['ID'];
			$options .= $this->createTag('option', array('selected' => 'selected', 'value' => $item['ID'], 'class' => $id), $item['ID']) . PHP_EOL;
			$values .= $this->createTag('li', array('ID' => $id, 'class' => "selected"), $item['Text']) . PHP_EOL;
		}
		
		$select = array(
			'id' => $this->attrName().'_select',
			'name' => $this->attrName()
		);
		if ($this->getMultiSelect()) {
			$select['name'] .= '[]';
			$select['multiple'] = 'multiple';
		}
		$select = $this->createTag('select', $select, $options);
		
		$hint = $this->createTag('label', array(
			'id' => $this->attrName().'_hint',
			'class' => 'hint',
			'for' => $this->attrName().'_search'
		), _t('RelationChipsField.HINT', 'start typing ...'));
		
		$reset = $this->createTag('label', array(
			'id' => $this->attrName().'_reset',
			'class' => 'reset',
			'for' => $this->attrName().'_search',
		), _t('RelationChipsField.RESET', 'X'));
		
		$input = $this->createTag('input', array(
			'id' => $this->attrName().'_search',
			'name' => $this->attrName().'_search',
			'type' => 'text',
			'class' => 'text '.str_replace('"', '\'', Convert::raw2json($config)),
			'value' => '',
			'autocomplete' => 'off'
		));
		
		$ul = $this->createTag('ul', array(
			'id' => $this->attrName().'_list',
			'class' => str_replace('"', '\'', Convert::raw2json(array('multiSelect' => $this->getMultiSelect())))
		), $values);

		Requirements::javascript(THIRDPARTY_DIR . '/jquery/jquery.js');
		Requirements::javascript(SAPPHIRE_DIR . '/javascript/jquery_improvements.js');	
		Requirements::javascript(THIRDPARTY_DIR . "/jquery-metadata/jquery.metadata.js");
		Requirements::javascript(MAGICFORMFIELDS_DIR."/javascript/RelationChipsField.js");
		Requirements::themedCSS('RelationChipsField');

		return $select . PHP_EOL . $hint . PHP_EOL . $reset . PHP_EOL . $input . PHP_EOL . $ul . PHP_EOL;
	}
	
	function saveInto(DataObject $record) {
		$fieldname = $this->name;
		$value = $this->Value();
		if($fieldname && $record) {
			if ($record->has_many($fieldname) || $record->many_many($fieldname)) {
				// HACK We can't save relationship tables without having an ID
				if(!$record->isInDB()) $record->write();
				$idList = array();
				if($value) 
					foreach((array)$value as $id)
						$idList[] = (int)$id;
				$record->$fieldname()->setByIDList($idList);
			} elseif($record->has_one($fieldname)) {
				if (is_array($value))
					$value = current($value);
				if($value)
					$record->{$fieldname.'ID'} = (int)$value;
			} elseif(substr($fieldname, -2) === 'ID' && $record->has_one(substr($fieldname, 0, -2))) {
				if (is_array($value))
					$value = current($value);
				if($value)
					$record->$fieldname = (int)$value;
			} else {
				$items = $this->resutlSetToArray($this->getValueObjects());
				$str = array();
				foreach ($items as $item)
					$str[] = $item['Text'];
				$record->$fieldname = implode($this->getToStringSeperator(), $str);
			}	
		}
	}
}