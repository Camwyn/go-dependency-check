#GO Dependency Check
A WordPress plugin to centralize our plugin dependency checking and allow for graceful failure on missing dependencies.

##Requirements
None. Really. OK, it does expect some logic in the naming of classes and functions, but that's about it!

##What it does
GO Dependency Check allows you to use a few filter calls and a short function to test plugin dependencies prior to `init`. By leveraging the earlier `plugins_loaded` action hook, we can check dependencies prior to plugin init. If a missing dependency is discovered, we create a plugin-specific filter so the plugin can test against it and shut down as to avoid fatal errors.

As an added bonus, you get a spiffy admin alert to notify you that you have to activate (or install) other plugins.

##Usage
Recommended setup:

####Class variable:
```
private $dependencies = array(
	'go-dependency-check' => 'https://github.com/GigaOM/go-dependency-check'
);
```

####In the constructor of the main class:
`add_filter( 'add_plugin_dependencies', array( $this, 'add_plugin_dependencies' ) );`

####Class function:
(pick your own class_name)

```
public function add_plugin_dependencies( $dependencies )
{
	//add our dependencies to the list
	$dependencies[ class_name ] = $this->dependencies;

	return $dependencies;
}//end add_plugin_dependencies
```

####Then, in `init` and/or `admin_init` ( or wherever you initialize your plugin ):
(use the same class_name as above)
```
$go = apply_filters( 'class_name_dependencies', TRUE );

if ( ! $go )
{
	return;
}//end if
```

Profit!