#!/usr/bin/php
<?php
define('DEBUG', TRUE);

// Whether this file is copied as a git hook script or symlinked the first
// argument should reflect the event name that was used to invoke it.
$hook = cook_get_event();
if (DEBUG) {
  print "[DEBUG] Working with event hook '$hook'.\n";
}
cook_load_plugins();

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
    $parts = explode('/', $argv[0]);
    $event = end($parts);
  }
  return $event;
}

/**
 * Load all .hook files for cooking into git hooks.
 *
 * @param $dir
 *  What is the base directory in which files are stored.
 *
 * @return
 *  Array of files loaded.
 */
function cook_load_plugins($dir = './.git/hooks/cook', $reset = FALSE) {
  static $plugins = array();  
  $hook = cook_get_event();

  if (empty($plugins) || $reset) {
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
    if (DEBUG) {
      print '[DEBUG] Discovered plugins for the current event: '
      . implode(', ', $plugins[$hook]) . "\n";
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
  foreach (cook_load_plugins($hook) as $plugin) {
    $function = $plugin . '_' . $hook;
    if (function_exists($function)) {
      $implements[$plugin] = $function;
    }
  }
  return $implements;
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
 * Return a list of the names of all staged files.
 */
function staged_files($reset = FALSE) {
  static $staged_files;
  if (empty($staged_files) || $reset) {
    $output = array();
    $return = 0;
    exec('git rev-parse --verify HEAD 2> /dev/null', $output, $return);
    $against = $return == 0 ? 'HEAD' : '4b825dc642cb6eb9a060e54bf8d69288fbee4904';
    exec("git diff-index --cached --name-only {$against}", $output);
    $staged_files = $output;
  }
  return $staged_files;
}
