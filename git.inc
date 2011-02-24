<?php

/**
 * @file
 *  Library of git function wrappers.
 */

/**
 * Return a list of the names of all staged files.
 */
function git_staged_files($reset = FALSE) {
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

