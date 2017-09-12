<?php

namespace WeDevs\WeMail\Modules;

class Modules {

    /**
     * weMail module list
     *
     * @since 1.0.0
     *
     * @var array
     */
    private $modules = [];

    /**
     * Class constructor
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function __construct() {
        $this->include_modules();
    }

    /**
     * Magic gettter method to access the module class
     *
     * @since 1.0.0
     *
     * @param string $module_name Camelcased module name
     *
     * @return object
     */
    public function __get( $module_name ) {
        if ( array_key_exists( $module_name, $this->modules ) ) {
            return $this->modules[ $module_name ];
        }

        return $this->{$module_name};
    }

    /**
     * Include all modules
     *
     * This will include all modules found in includes/Modules directory.
     *
     * @since 1.0.0
     *
     * @return void
     */
    private function include_modules() {
        $module_dirs = glob( WEMAIL_MODULES . '/*', GLOB_ONLYDIR );

        foreach ( $module_dirs as $module_dir ) {
            $module       = str_replace( WEMAIL_MODULES . '/', '', $module_dir );
            $module_name  = lcfirst( $module );

            $module_class = "\\WeDevs\\WeMail\\Modules\\$module\\$module";

            $this->register_module( $module_name, $module_class );
        }
    }

    /**
     * Register a module to wemail module list
     *
     * @since 1.0.0
     *
     * @param string $module_name  Camelcased module name
     * @param string $module_class Module fully qualified name
     *
     * @return void
     */
    public function register_module( $module_name, $module_class ) {
        $this->modules[ $module_name ] = new $module_class;
    }

}
