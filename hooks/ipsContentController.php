//<?php

class rules_hook_ipsContentController extends _HOOK_CLASS_
{

	/**
	 * View Item
	 *
	 * @return	\IPS\Content\Item|NULL
	 */
	protected function manage()
	{
		try
		{
			if ( $item = parent::manage() )
			{
				\IPS\rules\Event::load( 'rules', 'Content', 'content_item_viewed' )->trigger( $item );
				
				$classEvent = \IPS\rules\Event::load( 'rules', 'Content', 'content_item_viewed_' . md5( get_class( $item ) ) );
				if ( ! $classEvent->placeholder )
				{
					$classEvent->trigger( $item );
				}
			}
	
			return $item;
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