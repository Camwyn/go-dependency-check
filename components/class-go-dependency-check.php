<?php

class GO_Dependency_Check
{
	private $dependencies = array();
	private $missing_dependencies = array();
	private $plugin_name;

	public function __construct()
	{
	}//end __construct

	public function check( $plugin_name, $dependencies )
	{
		if ( empty( $plugin_name ) || empty( $dependencies ) )
		{
			return;
		}//end if

		$this->dependencies = $dependencies;
		$this->plugin_name = $plugin_name;

		add_action( 'admin_menu', array( $this, 'admin_menu_init' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
	}//end check

	public function admin_menu_init()
	{
		$this->check_dependencies();

		if ( $this->missing_dependencies )
		{
			return;
		}//end if
	}//end admin_menu_init

	public function admin_init()
	{
		$this->check_dependencies();

		if ( $this->missing_dependencies )
		{
			return;
		}//end if
	}//end admin_init

	/**
	 * check plugin dependencies
	 */
	public function check_dependencies()
	{
		foreach ( $this->dependencies as $dependency => $url )
		{
			if ( function_exists( str_replace( '-', '_', $dependency ) ) )
			{
				continue;
			}//end if

			$this->missing_dependencies[ $dependency ] = $url;
		}//end foreach

		if ( $this->missing_dependencies )
		{
			add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		}//end if
	}//end check_dependencies

	/**
	 * hooked to the admin_notices action to inject a message if depenencies are not activated
	 */
	public function admin_notices()
	{
		?>
		<div class="error">
			<p>
				You must <a href="<?php echo esc_url( admin_url( 'plugins.php' ) ); ?>">activate</a> the following plugins before using <code><?php echo esc_html( $this->plugin_name ); ?></code> plugin:
			</p>
			<ul>
				<?php
				foreach ( $this->missing_dependencies as $dependency => $url )
				{
					?>
					<li><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $dependency ); ?></a></li>
					<?php
				}//end foreach
				?>
			</ul>
		</div>
		<?php
	}//end admin_notices
}//end class

function go_dependency_check()
{
	global $go_dependency_check;

	if ( ! $go_dependency_check )
	{
		$go_dependency_check = new GO_Dependency_Check;
	}//end if

	return $go_dependency_check;
}//end go_dependency_check