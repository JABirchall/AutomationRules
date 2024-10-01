<?php
namespace IPS\rules\Secure;
if(!defined('\IPS\SUITE_UNIQUE_KEY')){header((isset($_SERVER['SERVER_PROTOCOL'])?$_SERVER['SERVER_PROTOCOL']:'HTTP/1.0').' 403 Forbidden');exit;}

abstract class _Rule extends \IPS\Node\Model
{
	public function save() {
		$this->_new && $this->auth();
		parent::save();
	}

	public function __clone() {
		$this->auth();
		parent::__clone();
	}
	
	final protected function auth()	{
		if ( \IPS\Application::load('rules')->isProtected() ) {
			if ( \IPS\Db::i()->select( 'COUNT(*)', 'rules_rules' )->first() >= RULELIMIT ) {
				\IPS\Output::i()->error( 'Lite version restricted to a maximum of ' . RULELIMIT . ' rules.', 'RULES', 200, '' );
				exit;
			}
		}
		return TRUE;
	}
	
	public function actions( $mode=NULL )
	{
		$cache_key = md5( json_encode( $mode ) );
		if ( isset( $this->actionCache[ $cache_key ] ) ) {
			return $this->actionCache[ $cache_key ];
		}
		$where = array( 'action_rule_id=?', $this->id );
		if ( $mode !== NULL ) {
			$where = array( 'action_rule_id=? AND action_else=?', $this->id, $mode );
		}
		return $this->actionCache[ $cache_key ] = \IPS\rules\Action::roots( NULL, NULL, array( $where ) );
	}
}