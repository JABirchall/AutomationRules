//<?php

class rules_hook_ipsDownloadsFile extends _HOOK_CLASS_
{

	/**
	 * Get available comment/review tabs
	 *
	 * @return	array
	 */
	public function commentReviewTabs()
	{
		try
		{
			$tabs = parent::commentReviewTabs();
			
			foreach( \IPS\rules\Log\Custom::roots( 'view', NULL, array( array( 'custom_log_class=? AND custom_log_enabled=1', $this::rulesDataClass() ) ) ) as $log )
			{
				if ( $log->display_empty or $log->logCount( $this ) )
				{
					$tab_id = 'custom_log_' . $log->id;			
					$tabs[ $tab_id ] = $log->title;
				}
			}
	
			return $tabs;
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
	 * Get comment/review output
	 *
	 * @param	string	$tab	Active tab
	 * @return	string
	 */
	public function commentReviews( $tab )
	{	
			try
			{
			foreach( \IPS\rules\Log\Custom::roots( 'view', NULL, array( array( 'custom_log_class=? AND custom_log_enabled=1', $this::rulesDataClass() ) ) ) as $log )
			{
				if ( $tab == 'custom_log_' . $log->id )
				{
					return (string) $log->logsTable( $this, $limit );
				}
			}	
		
			return parent::commentReviews( $tab );
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