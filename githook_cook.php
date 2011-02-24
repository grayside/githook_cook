#!/usr/bin/php
<?php
define('DEBUG', TRUE);

include_once('helper.inc');
cook_set_context();

if (cook_check_context('COOK_NORMAL')) {
  include_once('git.inc');
  $dir = './.git/hooks/cook';
}
else {
  include_once('test.git.inc');
  $dir = '.';
}

// Whether this file is copied as a git hook script or symlinked the first
// argument should reflect the event name that was used to invoke it.
$hook = cook_get_event();
if (cook_check_context('COOK_DEBUG')) {
  print "[DEBUG] Working with event hook '$hook'.\n";
}
cook_load_plugins($dir);

$status = 0;
foreach (cook_plugin_hooks($hook) as $function) {
  $status = $status | $function();
}
exit($status);

/**
 * Get the Git Event
 *
 * @param $reset
 *  (default: FALSE) Force a recomputation of the current event.
 *  This is only here as a matter of form, and will probably be removed.
 */
function cook_get_event($reset = FALSE) {
  static $event;
  if (empty($event) || $reset) {
    global $argv;
    if (cook_check_context('COOK_NORMAL')) {
      $parts = explode('/', $argv[0]);
      $event = end($parts);
    }
    else {
      $event = $argv[2];
    }
  }
  return $event;
}

/**
 * Load all .hook files for cooking into git hooks.
 *
 * This function will probably be separated into a plugin detection
 * mechanism for a plugin enable/disable mechanism, and a plugin loading
 * mechanism based on enabled plugins.
 *
 * @param $dir
 *  What is the base directory in which files are stored.
 *
 * @return
 *  Array of files loaded.
 */
function cook_load_plugins($dir = './.git/hooks/cook') {
  static $plugins;  
  $hook = cook_get_event();

  if (empty($plugins[$hook])) {
    if (empty($plugins)) {
      $plugins = array();
    }
    $plugins[$hook] = array();

    $dh = opendir($dir);
    while (($filename = readdir($dh)) !== FALSE) {
      $parts = explode('.', $filename);
      $ext = array_pop($parts);
      if ($ext != 'hook') {
        continue;
      }
      $file_hook = array_pop($parts);
      if (cook_is_git_hook($file_hook) && $hook != $file_hook) {
        continue;
      }
      else {
        array_unshift($parts, $file_hook);
      }
      include_once($dir . '/' . $filename);

      $plugins[$hook][$filename] = implode('_', $parts);
    }
    if (cook_check_context('COOK_DEBUG')) {
      print '[DEBUG] Discovered plugins for the current event: '
      . cook_array_to_string($plugins[$hook]) . "\n";
    }
  }

  return $plugins[$hook];
}

/**
 * Get all functions implementing a given event hook.
 *
 * @param $hook
 *  The specific git event to check for actions.
 *
 * @return Array
 *  Array of all functions implementing a given event hook.
 *  Keyed on plugin name. For example:
 *   Array('email' => 'email_pre_commit');
 */
function cook_plugin_hooks($hook) {
  $hook = strtr($hook, '-', '_');
  $implements = array();
  foreach (cook_load_plugins() as $plugin) {
    $function = $plugin . '_' . $hook;
    if (function_exists($function)) {
      $implements[$plugin] = $function;
    }
  }
  return $implements;
}
