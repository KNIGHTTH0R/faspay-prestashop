<?php
if (!defined('_PS_VERSION_'))
	exit;

class Mid extends ObjectModel
{
	public $mid;

	public $password;

	public $name;

	public $pymt_ind;

	public $pymt_crt;

	public $status;

	public static $definition = array(
		'table' => 'faspay_cc_config',
		'primary' => 'mid',
		'fields' => array(
			'mid' 		=>	array('type' => self::TYPE_STRING, 'validate' => 'isString'),
			'password' 	=>	array('type' => self::TYPE_STRING, 'validate' => 'isString'),
			'name' 		=>	array('type' => self::TYPE_STRING, 'validate' => 'isString'),
			'pymt_ind' 	=>	array('type' => self::TYPE_STRING, 'validate' => 'isString'),
			'pymt_crt' 	=>	array('type' => self::TYPE_STRING, 'validate' => 'isString'),
			'status' 	=>	array('type' => self::TYPE_INT, 'validate' => 'isMessage')
		)
	);

	public function __construct($id = null, $id_lang = null)
	{
		parent::__construct($id, $id_lang);
	}

	public static function truncate(){
		$sql = "TRUNCATE TABLE "._DB_PREFIX_."faspay_cc_config";
		Db::getInstance()->execute($sql); 
	}

	
}