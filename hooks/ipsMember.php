//<?php

class rules_hook_ipsMember extends _HOOK_CLASS_
{

	/**
	 * Get logged in member
	 *
	 * @return	\IPS\Member
	 */
	public static function loggedIn()
	{
		try
		{
			if ( static::$loggedInMember === NULL )
			{
			/**
			 * Rules may trigger core system functions that use \IPS\Member::loggedIn(), (i.e \IPS\Content\Item::setTags )
			 * This prevents the script from crashing in API mode because the parent method attempts to start a session.
			 */
				if ( ( ! isset( $_SERVER[ 'SERVER_SOFTWARE' ] ) && ( in_array( php_sapi_name(), array( 'cli', 'cgi', 'cgi-fcgi' ) ) || ( is_numeric( $_SERVER[ 'argc' ] ) && $_SERVER[ 'argc' ] > 0 ) ) ) )
				{
					static::$loggedInMember = static::load( NULL );
				}
			}
			
			return parent::loggedIn();
		}
		catch ( \RuntimeException $e )
		{
			if ( method_exists( get_parent_class(), __FUNCTION__ ) )
			{
				return \call_user_func_array( 'parent::' . __FUNCTION__, \func_get_args() );
			}
			else
			{
				throw $e;
			}
		}
	}
	
	/**
	 * Member Sync
	 *
	 * @param	string	$method	Method
	 * @param	array	$params	Additional parameters to pass
	 * @return	void
	 */
	public function memberSync( $method, $params=array () )
	{
		try
		{
			$event = \IPS\rules\Event::load( 'rules', 'Members', 'memberSync_' . $method );
			call_user_func_array( array( $event, 'trigger' ), array_merge( array( $this ), $params ) );
			
			return call_user_func_array( 'parent::memberSync', func_get_args() );
		}
		catch ( \RuntimeException $e )
		{
			if ( method_exists( get_parent_class(), __FUNCTION__ ) )
			{
				return \call_user_func_array( 'parent::' . __FUNCTION__, \func_get_args() );
			}
			else
			{
				throw $e;
			}
		}
	}
	
	/**
	 * Recounts content for this member
	 *
	 * @return void
	 */
	public function recountContent()
	{
		try
		{
			parent::recountContent();
			
			\IPS\rules\Event::load( 'rules', 'Members', 'content_recounted' )->trigger( $this, $this->member_posts );
		}
		catch ( \RuntimeException $e )
		{
			if ( method_exists( get_parent_class(), __FUNCTION__ ) )
			{
				return \call_user_func_array( 'parent::' . __FUNCTION__, \func_get_args() );
			}
			else
			{
				throw $e;
			}
		}
	}
	
	/**
	 * Unflag as spammer
	 *
	 * @return	void
	 */
	public function unflagAsSpammer()
	{
		try
		{
			parent::unflagAsSpammer();
			
			\IPS\rules\Event::load( 'rules', 'Members', 'member_not_spammer' )->trigger( $this );
		}
		catch ( \RuntimeException $e )
		{
			if ( method_exists( get_parent_class(), __FUNCTION__ ) )
			{
				return \call_user_func_array( 'parent::' . __FUNCTION__, \func_get_args() );
			}
			else
			{
				throw $e;
			}
		}
	}
	
	/**
	 * Set banned
	 *
	 * @param	string	$value	Value
	 * @return	void
	 */
	public function set_temp_ban( $value )
	{
		try
		{
			if ( $this->temp_ban == 0 and $value != 0 )
			{
				/* Banned */
				parent::set_temp_ban( $value );
				\IPS\rules\Event::load( 'rules', 'Members', 'member_banned' )->trigger( $this );
				return;
			}
			else if ( $this->temp_ban != 0 and $value == 0 )
			{
				/* Unbanned */
				parent::set_temp_ban( $value );
				\IPS\rules\Event::load( 'rules', 'Members', 'member_unbanned' )->trigger( $this );
				return;
			}
			
			parent::set_temp_ban( $value );
		}
		catch ( \RuntimeException $e )
		{
			if ( method_exists( get_parent_class(), __FUNCTION__ ) )
			{
				return \call_user_func_array( 'parent::' . __FUNCTION__, \func_get_args() );
			}
			else
			{
				throw $e;
			}
		}
	}

	/**
	 * Add profile visitor
	 *
	 * @param   \IPS\Member $visitor	Member that viewed profile
	 * @return	void
	 */
	public function addVisitor( $visitor )
	{
		try
		{
			parent::addVisitor( $visitor );
			
			\IPS\rules\Event::load( 'rules', 'Members', 'profile_visit' )->trigger( $this, $visitor );
		}
		catch ( \RuntimeException $e )
		{
			if ( method_exists( get_parent_class(), __FUNCTION__ ) )
			{
				return \call_user_func_array( 'parent::' . __FUNCTION__, \func_get_args() );
			}
			else
			{
				throw $e;
			}
		}
	}
	
	/**
	 * Get Real Posts (Group Collaboration)
	 */
	public function get_real_member_posts()
	{
		try
		{
			return $this->_data[ 'member_posts' ];
		}
		catch ( \RuntimeException $e )
		{
			if ( method_exists( get_parent_class(), __FUNCTION__ ) )
			{
				return \call_user_func_array( 'parent::' . __FUNCTION__, \func_get_args() );
			}
			else
			{
				throw $e;
			}
		}
	}

	/**
	 * @brief	Member Profile Data Caching
	 */
	public static $rulesProfileDataCache = array();
	
	/**
	 * Get Stored Profile Data
	 *
	 * @param	int	$field_id	The field id to fetch data for
	 * @return	mixed
	 */
	public function rulesProfileData( $field_id )
	{
		try
		{
			if ( ! $this->member_id )
			{
				return NULL;
			}
			
			if ( ! isset( static::$rulesProfileDataCache[ $this->member_id ] ) )
			{
				try
				{
					static::$rulesProfileDataCache[ $this->member_id ] = \IPS\Db::i()->select( '*', 'core_pfields_content', array( 'member_id = ?', $this->member_id ) )->first();
				}
				catch( \UnderflowException $e )
				{
					static::$rulesProfileDataCache[ $this->member_id ] = array();
				}
			}
			
			return static::$rulesProfileDataCache[ $this->member_id ][ 'field_' . $field_id ];
		}
		catch ( \RuntimeException $e )
		{
			if ( method_exists( get_parent_class(), __FUNCTION__ ) )
			{
				return \call_user_func_array( 'parent::' . __FUNCTION__, \func_get_args() );
			}
			else
			{
				throw $e;
			}
		}
	}

}