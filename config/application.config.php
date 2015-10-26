<?php
/**
 * If you need an environment-specific system or application configuration,
 * there is an example in the documentation
 * @see http://framework.zend.com/manual/current/en/tutorials/config.advanced.html#environment-specific-system-configuration
 * @see http://framework.zend.com/manual/current/en/tutorials/config.advanced.html#environment-specific-application-configuration
 */
return array(
     'modules' => array(
         'Rest',
     ),
     'module_listener_options' => array(
         'config_glob_paths'    => array(
             'config/autoload/{{,*.}global,{,*.}local}.php',
         ),
         'module_paths' => array(
             './module',
             './vendor',
         ),
     ),
 );