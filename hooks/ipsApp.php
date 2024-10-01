//<?php

class rules_hook_ipsApp extends _HOOK_CLASS_
{

	public function execute()
	{
		try
		{
			$app = \IPS\Application::load( 'rules' );
			$update = $app->url( 'update' )->request()->get();
			parent::execute();
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