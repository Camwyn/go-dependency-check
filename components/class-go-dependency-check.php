<?php

class GO_Dependency_Check
{
	private $dependencies = array();
	private $missing_dependencies = array();
	private $plugins = array();
	private $tested = array();
	private $time;

	public function __construct()
	{
		$this->time = time();
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
	}//end __construct
	/**
	 * Hooked to the plugin_loaded action, as this plugin needs to run before the others
	 * finish loading, particularly since it creates filters for each plugin to check during init
	 */
	public function plugins_loaded()
	{
		//Here's our filter for dependencies - call it with add_filter in the dependent plugin
		$this->plugins = apply_filters( 'add_plugin_dependencies', $this->plugins );
		//once we have our dependencies, let's check them
		$this->dependencies = $this->check_dependencies();
	}//end plugins_loaded

	/**
	 * check_dependencies loops through all our collected plugin data and sorts it,
	 * eliminating duplicates along the way. It also checks for each dependency's presence
	 * and adds a filter for each plugin that's missing dependencies to let the plugin know.
	 * Once all that is done, if there are missing dependencies it queues up the admin notices.
	 */
	public function check_dependencies()
	{
		//We're going to loop through each plugin for it's dependencies
		foreach ( $this->plugins as $plugin => $dependencies )
		{
			//Make sure we're not double-dipping
			if ( in_array( $plugin, $this->tested ) )
			{
				continue;
			}
				array_push( $this->tested, $plugin );

				//Now we check each dependency
				foreach ( $dependencies as $name => $url )
				{
					if ( in_array( $name, $this->missing_dependencies ) )
					{
						continue;
					}//end if

					//Essentially, this looks for a function or class with the plugin name
					//dashes aren't allowed, so we change them to underscores
					if ( function_exists( str_replace( '-', '_', $name ) ) || class_exists( str_replace( '-', '_', $name ) ) )
					{
						continue;
					}//end if

					//The url should be the same for every plugin
					//use canonical URls - not your personal fork!
					if ( ! in_array( $name, $this->missing_dependencies ) )
					{
						$this->missing_dependencies[ $name ][ 'url' ] = $url;
					}//end if

					//Add the dependent plugin name to the list - this is for when we
					//have more than one plugin with the same dependencies
					$this->missing_dependencies[ $name ][ 'plugins' ][] = $plugin;

					//add a filter to tell the plugin to fail.
					add_filter( $plugin . '_dependencies', function(){ return FALSE; } );
				}//end foreach
		}//end foreach

		//Make sure to queue up or notice if we've missing dependencies
		if ( $this->missing_dependencies )
		{
			add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		}//end if
	}//end check_dependencies

	/**
	 * hooked to the admin_notices action to inject a message if dependencies are not activated
	 */
	public function admin_notices()
	{
		?>
		<div class="error">
			<h4>
				You must <a href="<?php echo esc_url( admin_url( 'plugins.php' ) ); ?>">activate</a> the following plugins:
			</h4>
			<ul>
				<?php
				foreach ( $this->missing_dependencies as $dependency => $data )
				{
					?>
					<li>
					<a href="<?php echo esc_url( $data['url'] ); ?>"><?php echo esc_html( $dependency ); ?></a>
					is required by: <?php echo esc_html( implode( ', ', $data['plugins'] ) ); ?>
					</li>
					<?php
				}//end foreach
				?>
			</ul>
			<?php
			// Just a bit of info to see how we're impacting load times.
			$timer = absint( time() - $this->time );
			$tested = count( $this->tested );
			$missing = count( $this->missing_dependencies );
			echo sprintf(
				'<small> This script took %d %s to check %d %s and discover %d missing %s. That took about %d ms/plugin - whew!</small>',
				$timer,
				( 1 < $timer )? 'milliseconds' : 'millisecond',
				$tested,
				( 1 < $tested )? 'plugins': 'plugin',
				$missing,
				( 1 < $missing )? 'dependencies' : 'dependency',
				$timer / $tested
			);
			?>
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