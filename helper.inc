<?php

/**
 * @file
 *  Utility functions for githook_cook.
 */

/**
 * Check that the specified context is active.
 */
function cook_check_context($context) {
  return in_array($context, cook_get_context());
}

/**
 * Get the context.
 */
function cook_get_context() {
  return cook_set_context();
}

/**
 * Check command-line arguments to determine a testing state.
 */
function cook_set_context() {
  static $context;

  if (empty($context)) {
    global $argc, $argv;
    if ($argc > 1 && $argv[1] == 'test') {
      $context = array('COOK_TEST', 'COOK_DEBUG');
    }
    else {
      $context = array();
      if (DEBUG) {
        $context[] = 'COOK_DEBUG';
      }
      $context[] = 'COOK_NORMAL';
    }
  }
  return $context;
}

/**
 * Simple magic function callback to collect arrays of values into a big array.
 *
 * @param $name
 *  Name of the magic function. It should be prefixed by plugin name.
 */
function cook_plugin_invoke_all($name) {
  $results = array();
  $plugins = cook_load_plugins(cook_get_event());
  foreach ($plugins as $plugin) {
    $function = $plugin . '_' . $name;
    if (function_exists($function)) {
      $retn = $function();
      if (is_array($retn)) {
        $results = array_merge($results, $retn);
      }
      else {
        $results[] = $retn;
      }
    }
  }
  return $results;
}

/**
 * Identify whether the given term refers to a git hook.
 */
function cook_is_git_hook($term) {
  $events = array(
    'applypatch', 'post-receive', 'pre-commit',
    'commit-msg', 'post-update', 'prepare-commit-msg',
    'post-commit', 'pre-commit', 'update'
  );
  return in_array($term, $events);
}

/**
 * Cause an array to use it's values as keys.
 */
function cook_array_reflect($arr) {
  $result = array();
  foreach ($arr as $elem) {
    $result[$elem] = $elem;
  }
  return $result;
}

/**
 * Convert an array to a comma-delimited list.
 */
function cook_array_to_string($arr) {
  return implode(', ', $arr);
}
