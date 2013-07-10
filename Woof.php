<?php
	class Criteria
	{
		public $select;
		public $distinct;
		public $like, $or_like, $not_like, $or_not_like;
		public $where, $or_where, $where_in, $where_not_in, $or_where_not_in;
		public $join, $left, $right, $outer, $inner, $left_outer, $right_outer;
		public $group_by;
		public $having, $or_having;
		public $order_by;
		public $limit, $offset;
	}
?>

<?php
	class WResult
	{
		private $resultAttrs=array();
		private $assignAttrs=array();
		private $bindAttrs=array();
		
		function __construct($resultAttrs, $bindAttrs)
		{
			$this->resultAttrs = $resultAttrs;
			$this->bindAttrs = $bindAttrs;
		}
		
		/*
		 * Get attribute
		 */ 
		function __get($field)
		{		
			if (in_array($field, $this->resultAttrs, true))
				return $this->assignAttrs[$field];
			else
				throw new Exception('No attribute ' . $field . ' exist', 1);
		}
		
		/*
		 * Set attribute		 
		 */  
		function __set($field, $value)
		{
		    if (in_array($field, $this->resultAttrs, true))
				$this->assignAttrs[$field] = $value;
			else
				throw new Exception('No attribute ' . $field . ' exist', 1);
		}	
		
		/*
		 * Call Method
		 */  
		function __call($function, $arguments)
		{
			switch ($function)
			{
				/**
				 * Expand record from FK binding attribute
				 * 
				 * @access	public
				 * @param	string | array		Select fields
				 * @return	object
				 */
				default:
					{
						if (array_key_exists($function, $this->bindAttrs)){
														
							require_once APPPATH . '/models/' . $this->bindAttrs[$function]['path'] . '/' . $this->bindAttrs[$function]['class'] . '.php';

							// fields parameters
							if (!isset($arguments[0]))								
								$arguments[0] = null;							
							
							// Retrieve row
							$obj = new $this->bindAttrs[$function]['class'];										
							return $obj->find(array($this->bindAttrs[$function]['destAttr'] => $this->$function), $arguments[0]);
							
						} else 
							throw new Exception($function . ' isn\'t binding attribute', 1);						
					}
					break;
			}
		}
	}
?>

<?php	
	require_once dirname(BASEPATH) . '/system/core/Model.php';
	
	/*
	 * ActiveRecord Model Class
	 */  
	abstract class WActiveRecord extends CI_Model  {
	
		protected $tableName;		
				
		/**
		 * Get database name
		 * 
		 * @access	public
		 * @return	string
		 */
		public function databaseName(){
			return $this->db->database;			
		}
		/**
		 * Get table name
		 * 
		 * @access	public
		 * @return	string
		 */
		public function tableName(){
			return $this->tableName;
		}
		
		protected function ARinsert($data)
		{
			return $this->db->insert($this->tableName, $data);					
		}
		protected function ARupdate($data, $command=null)
		{
			if (!empty($command))
				$this->createCommand($command);
			
			$success = $this->db->update($this->tableName, $data);
			
			$this->db->flush_cache();
			return $success;
		}
		protected function ARdelete($command=null)
		{
			if (!empty($command))	
				$this->createCommand($command);
			
			$success = $this->db->delete($this->tableName);
			
			$this->db->flush_cache();
			return $success;		
		}
		protected function ARtruncate()
		{
			return $this->db->truncate($this->tableName);
		}
		protected function ARempty()
		{
			return $this->db->empty_table($this->tableName);
		}
		
		protected function ARfindAll($command=null, $fields=null)
		{
			if (!empty($fields))			
				$this->db->select($fields);
			if (!empty($command))			
				$this->createCommand($command);			
			
			$this->db->from($this->tableName);			
			$query = $this->db->get();
			
			$this->db->flush_cache();			
			return $query->result();
		}
		protected function ARfind($command=null, $fields=null)
		{			
			if (!empty($fields))			
				$this->db->select($fields);		
			if (!empty($command))			
				$this->createCommand($command);
			
			$this->db->from($this->tableName);			
			$query = $this->db->get();
			
			$this->db->flush_cache();
			if ($query->num_rows() > 0)
				return $query->row(0);
			else
				return null;			
		}
		protected function ARfindBySql($sqlcmd)
		{				
			$query = $this->db->query($sqlcmd);
			
			$this->db->flush_cache();			
			return $query->result();	
		}
		
		protected function ARmax($field, $command=null)
		{			
			$this->db->select_max($field);
			$this->db->from($this->tableName);
			
			if (!empty($command))
				$this->createCommand($command);
				
			$query = $this->db->get();
			
			$this->db->flush_cache();	
			if ($query->num_rows() > 0)			
				return $query->row(0)->$field;
			else
				return null;
		}
		protected function ARmin($field, $command=null)
		{
			$this->db->select_min($field);
			$this->db->from($this->tableName);
			
			if (!empty($command))
				$this->createCommand($command);
				
			$query = $this->db->get();
			
			$this->db->flush_cache();	
			if ($query->num_rows() > 0)			
				return $query->row(0)->$field;
			else
				return null;
		}
		protected function ARavg($field, $command=null)
		{
			$this->db->select_avg($field);
			$this->db->from($this->tableName);
			
			if (!empty($command))
				$this->createCommand($command);
				
			$query = $this->db->get();
			
			$this->db->flush_cache();	
			if ($query->num_rows() > 0)			
				return $query->row(0)->$field;
			else
				return null;
		}
		protected function ARsum($field, $command=null)
		{
			$this->db->select_sum($field);
			$this->db->from($this->tableName);
			
			if (!empty($command))
				$this->createCommand($command);
				
			$query = $this->db->get();
			
			$this->db->flush_cache();	
			if ($query->num_rows() > 0)			
				return $query->row(0)->$field;
			else
				return null;
		}
		protected function ARcount($command=null)
		{			
			if (!empty($conditions))				
				$this->createCommand($conditions);
			
			$count = $this->db->count_all_results($this->tableName);
			
			$this->db->flush_cache();
			return $count;
		}
		
		/**
		 * Format LIKE command to array if string input
		 * 
		 * @access	private
		 * @param	string		String command
		 * @return	array | null
		 */ 
		private function formatLikeCommand($str)
		{
			// Remove spaces
			preg_replace('!\s+!', ' ', $str);
			$str = str_replace(' ', '', $str);		
			
			$arr = explode(':', $str);			
						
			if (count($arr) >= 2)
			{
				$return[0] = $arr[0]; // field	
				$return[1] = $arr[1]; // value			
				
				// Find %
				$count = substr_count($return[1], '%');
				
				if ($count == 1){				
					if (substr($return[1], -1) == '%')
						$return[2] = 'after'; 
					else
						$return[2] = 'before';
					
					$return[1] = str_replace('%', '', $return[1]);
				} elseif ($count == 2){					
					$return[1] = str_replace('%', '', $return[1]);
				 	$return[2] = 'both'; //
				} else {
					$return[1] = $return[1];
					
					// Set operator
					if (isset($arr[2])){
						$oprs = array('before','after','both');
						if (in_array($arr[2], $oprs))
							$return[2] = $arr[2];
						else 
							$return[2] = 'both';						
					}											
				}
				
				return $return;
			} else
				return null;
		}
		
		/**
		 * Format IN command to array if string input
		 * 
		 * @access	private
		 * @param	string		String command
		 * @return	array | null
		 */
		private function formatInCommand($str)
		{
			// Remove spaces
			preg_replace('!\s+!', ' ', $str);
			$str = str_replace(' ', '', $str);
			
			$arr = explode(':', $str);
			
			if (count($arr) >= 2)
			{
				$return[0] = $arr[0]; // field
				
				$arr[1] = $arr[1];
				$arr[1] = str_replace('(', '', $arr[1]);
				$arr[1] = str_replace(')', '', $arr[1]);
				
				$return[1] = explode(',', $arr[1]); // value				
				return $return;
			} else
				return null;
		}
		
		/**
		 * Format HAVING command to array if string input
		 * 
		 * @access	private
		 * @param	string		String command
		 * @return	array | null
		 */ 
		private function formatHavingCommand($str)
		{
			// Remove spaces
			preg_replace('!\s+!', ' ', $str);
			$str = str_replace(' ', '', $str);
			
			$count = substr_count($str, ':');
			
			if ($count > 0){
				$arr = explode(':', $str);
				
				if (count($arr) >= 2){
					$return[0] = $arr[0]; // field;
					$return[1] = $arr[1]; // value;
					
					if (isset($arr[2])) // prevent escaping
						$return[2] = (bool)$arr[2];
					else
						$return[2] = null;
					
					return $return;
				} else
					return null;
			} else
				return null;
		}
		
		/**
		 * Format JOIN command to array if string input
		 * 
		 * @access	private
		 * @param	string		String command
		 * @return	array | null		 
		 */
		private function formatJoinCommand($joinType='join', $str)
		{
			// Remove spaces
			preg_replace('!\s+!', ' ', $str);
			$str = str_replace(' ', '', $str);
			$count = substr_count($str, ':');
			
			if ($count > 0){
				
				switch ($joinType)
				{
					case 'join':
						{																				
							$arr = explode(':', $str);
							if (isset($arr[2])){
								
								if ($arr[2] !== 'left' || $arr[2] !== 'right')
									$arr[2] = null;
							} else
								$arr[2] = null;
							
							return $arr;
						}
						break;
					case 'left': case 'left_join': case 'left join': case 'right': case 'right_join': case 'right join':
					case 'inner': case 'inner join': case 'inner join': case 'outer': case 'outer_join': case 'outer join':
					case 'left_outer': case 'left outer': case 'right_outer': case 'right outer':
						{
							return explode(':', $str);
						}
						break;
					default:						
						return null;
						break;
				}				
			} else
				return null;
		}
		
		/**
		 * Convert object to array
		 * 
		 * @access	private
		 * @param	object
		 * @return	array
		 */ 
		protected function parseObjectToArray($object)
		{
			try {
				// PHP 5.2 or above
				return json_decode(json_encode($object), true);	
			} catch (Exception $e){
				return (array)$object;
			}
		}
		
		/**
		 * Assign command to db object 
		 * 
		 * @access	private
		 * @param	criteria | array	Query command
		 * @return	null
		 */ 
		private function createCommand($command)
		{			
			if ($command instanceof Criteria)
				$command = $this->parseObjectToArray($command);			
				
			if (is_array($command)){
				foreach ($command as $key=>$value){
					
					if (empty($value))
						continue;
					
					// Set default value
					if (is_array($value)){
						if (isset($value[0]) && empty($value[0]))
							$value[0] = null;
						if (isset($value[1]) && empty($value[1]))
							$value[1] = null;
						if (isset($value[2]) && empty($value[2]))
							$value[2] = null;
					}	
					
					switch (strtolower($key)){
						// select
						case 'select':								
							$this->db->select($value);
							
							break;
						case 'distinct':
							{
								if ((bool)$value)
									$this->db->distinct();
							}
							break;
						// like condition
						case 'like':
							{
								if (!is_array($value))
									$value = $this->formatLikeCommand($value);								
								if (!is_null($value))
									$this->db->like($value[0], $value[1], $value[2]);
							}							
							break;
						case 'or_like': case 'or like':
							{
								if (!is_array($value))
									$value = $this->formatLikeCommand($value);
								if (!is_null($value))								
									$this->db->or_like($value[0], $value[1], $value[2]);
							}
							break;
						case 'not_like': case 'not like':
							{
								if (!is_array($value))
									$value = $this->formatLikeCommand($value);
								if (!is_null($value))
									$this->db->not_like($value[0], $value[1], $value[2]);
							}
							break;
						case 'or_not_like': case 'or not like':
							{
								if (!is_array($value))
									$value = $this->formatLikeCommand($value);
								if (!is_null($value))
									$this->db->or_not_like($value[0], $value[1], $value[2]);
							}
							break;
						// where condition	
						case 'where':						
							$this->db->where($value);							
							break;
						case 'or_where': case 'or where': case 'or':
							$this->db->or_where($value);							
							break;
						case 'where_in': case 'where in': case 'in':
							{
								if (!is_array($value))								
									$value = $this->formatInCommand($value);					
								if (!is_null($value))
									$this->db->where_in($value[0], $value[1]);								
							}
							break;
						case 'or_where_in': case 'or where in': case 'or_in': case 'or in':
							{
								if (!is_array($value))
									$value = $this->formatInCommand($value);
								if (!is_null($value))
									$this->db->or_where_in($value[0], $value[1]);
							}							
							break;
						case 'where_not_in': case 'where not in': case 'not_in': case 'not in':
							{
								if (!is_array($value))
									$value = $this->formatInCommand($value);
								if (!is_null($value))															
									$this->db->where_not_in($value[0], $value[1]);
							}							
							break;
						case 'or_where_not_in': case 'or where not in': case 'or_not_in': case 'or not in':
							{
								if (!is_array($value))
									$value = $this->formatInCommand($value);
								if (!is_null($value))								
									$this->db->or_where_not_in($value[0], $value[1]);
							}
							break;
						// join
						case 'join':
							{
								if (!is_array($value))
									$value = $this->formatJoinCommand('join', $value);
								if (!is_null($value))
									$this->db->join($value[0], $value[1], $value[2]);
							}
							break;
						case 'left': case 'left_join': case 'left join':
							{
								if (!is_array($value))
									$value = $this->formatJoinCommand('left', $value);
								if (!is_null($value))
									$this->db->join($value[0], $value[1], 'left');
							}
							break;
						case 'right': case 'right_join': case 'right join':
							{
								if (!is_array($value))
									$value = $this->formatJoinCommand('right', $value);
								if (!is_null($value))
									$this->db->join($value[0], $value[1], 'right');
							}
							break;
						case 'outer':
							{
								if (!is_array($value))
									$value = $this->formatJoinCommand('outer', $value);
								if (!is_null($value))
									$this->db->join($value[0], $value[1], 'outer');
							}
							break;
						case 'inner':
							{
								if (!is_array($value))
									$value = $this->formatJoinCommand('inner', $value);
								if (!is_null($value))
									$this->db->join($value[0], $value[1], 'inner');
							}
							break;
						case 'left_outer': case 'left outer':
							{
								if (!is_array($value))
									$value = $this->formatJoinCommand('left_outer', $value);
								if (!is_null($value))
									$this->db->join($value[0], $value[1], 'left_outer');
							} 
							break;
						case 'right_outer': case 'right outer':
							{
								if (!is_array($value))
									$value = $this->formatJoinCommand('right_outer', $value);
								if (!is_null($value))
									$this->db->join($value[0], $value[1], 'right_outer');
							}
							break;
						// optional
						case 'group_by': case'group by': case 'group':
							$this->db->group_by($value);
							break;						
						case 'having':
							{
								if (!is_array($value)){
									$pams = $this->formatHavingCommand($value);
																		
									if (!is_null($pams))								
										$this->db->having($pams[0], $pams[1], $pams[2]);
									else
										$this->db->having($value);
								} else
									$this->db->having($value);
							}
							break;
						case 'or_having': case 'or having':
							{
								if (!is_array($value)){
									$pams = $this->formatHavingCommand($value);
									
									if (!is_null($pams))								
										$this->db->or_having($pams[0], $pams[1], $pams[2]);
									else
										$this->db->or_having($value);
								} else
									$this->db->or_having($value);
							}
							break;
						case 'order_by': case 'order by': case 'order':
							{
								if (is_array($value))
								{
									if (isset($value[1]))
										$this->db->order_by($value[0], $value[1]);
									else
										$this->db->order_by($value[0]);
								} else
									$this->db->order_by($value);
							}
							break;					
						case 'limit':
							{						
								if (is_array($value) && count($value) >= 2)								
									$this->db->limit($value[0], $value[1]);
								else {
									// start index
									$offset = 0;
									if (isset($command['offset']))
										$offset = $command['offset'];
									
									// array input
									if (is_array($value)){
										
										if (!empty($value[0]))										
											$this->db->limit($value[0], $offset);
									} else {
									
										// string input	
										$pams = explode(',', $value);
										
										if (count($pams) >= 2)											
											$this->db->limit($pams[0], $pams[1]);
										else {																															
											if (!empty($value))
												$this->db->limit($value, $offset);
										}
									}
								}								
							}
							break;
						default:
							$this->db->where($key, $value);
							break;
					}		
				} // end foreach
			}
		}		
		
	}
?>

<?php	
	/*
	 * Model Class
	 */	
	abstract class WModel extends WActiveRecord
	{				
		private $ruleAttrs=array(); // Keep define attributes 
		private $assignAttrs=array(); // Keep assigned value attributes
		private $bindAttrs=array(); // Keep bind attributes
									
		function __construct($useDefaultResult=TRUE){
			parent::__construct();
			
			$this->setAttribute();
			$this->initialize();
			$this->useDefaultResult = $useDefaultResult;
		}
		
		/*
		 * Primary key field
		 */		
		protected $pkField;
		/*
		 * Use original result object
		 * true - return result original format
		 * false - return result default format
		 */ 
		public $useDefaultResult;
						
		/**
		 * Return all database attributes
		 * 
		 * @access	protected
		 * @return	array
		 */ 
		protected function attributeRule() { return array(); }
		
		/**
		 * Initialize
		 * 
		 * @access	protected
		 * @return	void
		 */ 
		protected function initialize() {}
					
		/**
		 * Bind Foreign key record
		 * 
		 * @access	protected
		 * @param	string		Source Attribute 
		 * @param	string		Destination Attribute 
		 * @param	string		Class name
		 * @param	string		If model has input in sub folder, then specific folder name
		 * @return	void 
		 */  
		protected function bindFK($sourceAttr, $destAttr, $class, $path=null)
		{			
			$this->bindAttrs[$sourceAttr] = 
				array('sourceAttr'	=> $sourceAttr,
					  'destAttr' 	=> $destAttr,
					  'class'		=> $class,
					  'path'		=> $path);
		}	
		
		/*
		 * Get attribute
		 */ 
		function __get($field)
		{			
			// Return attribute with value					
			if (array_key_exists($field, $this->assignAttrs))															
				return $this->assignAttrs[$field];
			// Return attribute with null value
			elseif (in_array($field, $this->ruleAttrs)){
				
				if (isset($this->$field))								
					return $this->$field;
				else
					return null;		
			} else {
				// Return CI core attribute											
				$CI =& get_instance();
				return $CI->$field;
			}			
		}
		
		/*
		 * Set attribute		 
		 */  
		function __set($field, $value)
		{				
		    $CI =& get_instance();	    
		    
		    // Check if attribute is core attribute 
		    if (array_key_exists($field, get_object_vars($CI)))
		    {			    		    	
		    	if ($field == 'db')	    		
		    		$this->db = $this->load->database($value, true);	    	
				else		    
		        	$this->$field = $value;				
		    }		    
		    else	    
		    {					
			    if (in_array($field, $this->ruleAttrs))	    		   	
			        $this->assignAttrs[$field] = $value;
				else
					// No attribute defined
					throw new Exception($field . ' is not defined attribute.', 1);									
		    }    
		}		
				
		protected static $instance = NULL;
		
		/**
		 * Call self
		 * 
		 * @access	public
		 * @return	instance
		 */ 		
		public static function model()
		{
			if(null !== static::$instance){
				return static::$instance;
			}
			
			static::$instance = new static();
			return static::$instance;
		}
		
		/**
		 * Insert record
		 * 
		 * @access	public
		 * @return	boolean
		 */
		function insert()
		{
			return $this->ARinsert($this->assignAttrs);		
		}
		
		/**
		 * Set value to attribute
		 * 
		 * @access	public
		 * @param	string				Field name
		 * @param	string | numeric	Field value
		 * @return  void
		 */ 
		function set($fieldName, $value)
		{
			if (array_key_exists($arguments[0], $this->ruleAttrs))
				$this->assignAttrs[$arguments[0]] = $arguments[1];
			else
				throw new Exception($arguments[0] . ' is not defined attribute.', 1);	
		}
		
		/**
		 * Update records by conditions
		 * 
		 * @access	public
		 * @param	criteria | array	Query commands		
		 * @return	boolean	 
		 */
		function update($command=null)
		{
			return $this->ARupdate($this->assignAttrs, $command);
		}		
		
		/**
		 * Update record by Primary key value
		 * 
		 * @access	public
		 * @param	numeric | string	Primary key value
		 * @return	boolean
		 */
		function updateByPk($value)
		{
			$condition[$this->pkField] = $value;						
			return $this->ARupdate($this->assignAttrs, $condition);
		}
		 
		/**		  
		 * Delete records by conditions
		 * 
		 * @access	public
		 * @param	criteria | array	Query commands
		 * @return	boolean
		 */
		function delete($command=null)
		{
			return $this->ARdelete($command);
		}
		
		/**
		 * Delete record by Primary key value
		 * 
		 * @access	public
		 * @param	numeric | string	Primary key value
		 * @return	boolean
		 */
		function deleteByPk($value)
		{
			$condition[$this->pkField] = $value;						
			return $this->ARdelete($condition);
		}
		
		/**
		 * Truncate
		 * 
		 * @access	public
		 * @return	boolean		  
		 */ 
		function truncate()
		{
			return $this->ARtruncate();
		}		  
		
		/**
		 * Empty table
		 * 
		 * @access	public
		 * @return	boolean
		 */ 		  
		function emptyAll()
		{
			return $this->ARempty();
		}
		
		/**
		 * Get single record
		 * 
		 * @access	public
		 * @param	criteria | array	Query commands
		 * @param	string | array		select fields
		 * @return	object
		 */
		function find($command=null, $fields=null)
		{
			$row = $this->ARfind($command, $fields);
						
			if (!is_null($row)){
				if ($this->useDefaultResult)														
					return $row;
				else
					return $this->formatObject($row);
			} else
				// Return blank class							
				return array();
		}
		
		/**
		 * Get multiple records
		 * 
		 * @access	public
		 * @param	criteria | array	Query commands
		 * @param	string | array		select fields
		 * @return	array	 		 
		 */
		function findAll($command=null, $fields=null)
		{					
			$result = $this->ARfindAll($command, $fields);

			if ($this->useDefaultResult)
				return $result;
			else
				return $this->formatObject($result);
		}
		
		/**
		 * Get single record by Primary key value
		 * 
		 * @access	public
		 * @param	numeric | string	Primary key value
		 * @param	string | array		Select fields				 
		 * @return	object
		 */ 
		function findByPk($value, $fields=null)
		{
			$condition[$this->pkField] = $value;						
						
			$row = $this->ARfind($condition, $fields);

			if (!is_null($row)){
				if ($this->useDefaultResult)
					return $row;
				else
					return $this->formatObject($row);
			} else		
				// Return blank class									
				return array();
		}
		
		/**
		 * Get data by custom sql command
		 * 
		 * @access	public
		 * @param	string		String sql command
		 * @return	array
		 */
		function findBySql($sqlCommand)
		{
			$result = $this->ARfindBySql($sqlCommand);
			if ($this->useDefaultResult)
				return $result;							
			else
				return $this->formatObject($result);
		} 
		
		/**
		 * Get record count
		 * 
		 * @access	public
		 * @param 	criteria | array	Query commands
		 * @return	numeric
		 */
		function count($command=null)
		{
			return $this->ARcount($command);
		}
		
		/**
		 * Get max value of field
		 * 
		 * @access	public
		 * @param	string				Query fields
		 * @param	criteria | array	Query commands
		 * @return	numeric
		 */
		function max($field, $command=null)
		{
			return $this->ARmax($field, $command);
		}
		
		/**
		 * Get min value of field
		 * 
		 * @access	public
		 * @param	string				Query fields
		 * @param	criteria | array	Query commands
		 * @return	numeric
		 */
		function min($field, $command)
		{
			return $this->ARmin($field, $command);
		}
		
		/**
		 * Get average value of field
		 * 
		 * @access	public
		 * @param	string				Query fields
		 * @param	criteria | array	Query commands
		 * @return	numeric
		 */
		function avg($field, $command)
		{
			return $this->ARavg($field, $command);
		}
		
		/**
		 * Get summary value of field
		 * 
		 * @access	public
		 * @param	string				Query fields
		 * @param	criteria | array	Query commands
		 * @return	numeric
		 */
		function sum($field, $command)
		{
			return $this->ARsum($field, $command);
		}
		
		/**
		 * Get lastest insert id
		 * 
		 * @access	public
		 * @return	string
		 */
		function insertId()
		{
			return $this->db->insert_id();
		}
		
		/**
		 * Get last query
		 * 
		 * @access	public
		 * @return	numeric | string
		 */
		function lastQuery()
		{
			return $this->db->last_query();
		}			
				
		/**
		 * Set relevant attributes
		 * 
		 * @access	private
		 * @return	void
		 */
		private function setAttribute()
		{
			$attrs = $this->attributeRule();
			
			if (count($attrs) > 0){
				foreach ($attrs as $attr){
					array_push($this->ruleAttrs, $attr['name']);
					
					// Set Primary key
					if (isset($attr['pk']) && $attr['pk'] == TRUE)
						$this->pkField = $attr['name'];
					// Set Foreign key
					if (isset($attr['fk'])){
						$path = '';
						if (isset($attr['fk']['path']))
							$path = $attr['fk']['path'];
											
						$this->bindFK($attr['name'], $attr['fk']['destAttr'], $attr['fk']['class'], $path);
					}
					
				}
			}	
				
		}		
		
		/**
		 * Convert result object
		 * 
		 * @access	private
		 * @param	object		Row of result
		 * @return	object | array
		 */
		private function formatObject($object)
		{			
			if (is_array($object)){
				
				// multiple record
				$items = array();
				foreach ($object as $obj){
					$setAttrs = array_keys($this->parseObjectToArray($obj));
					$item = new WResult($setAttrs, $this->bindAttrs);
					
					for ($i=0; $i<count($setAttrs); $i++){
						$attr = $setAttrs[$i];
						
						if (isset($obj->$attr))
							$item->$attr = $obj->$attr;
					}
					
					array_push($items, $item);
				}
				
				return $items;
			} else {
				// single record
				$setAttrs = array_keys($this->parseObjectToArray($object));
				$item = new WResult($setAttrs, $this->bindAttrs);
				
				for ($i=0; $i<count($setAttrs); $i++){
					$attr = $setAttrs[$i];
					
					if (isset($object->$attr))
						$item->$attr = $object->$attr;									
				}
				
				return $item;
			}
				
		}
	}
?>

<?php
	/**
	 * CodeIgniter Woof Model Class
	 * 
	 * This class is custom Model for implement another, which improve efficiency to use model.	 
	 *
	 * @package		CodeIgniter	 
	 * @author		KKK
	 * @category	Libraries	 
	 * @copyright	Copyright (c) 2013, KKK.
	 * @version		1.0	 
	 **/
	class Woof extends WModel
	{
		function __construct($useDefaultResult=TRUE)
		{			
			parent::__construct($useDefaultResult);
		}
	}
?>