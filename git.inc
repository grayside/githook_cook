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

/**
 * Retrieve the name of the last tag on this branch.
 *
 * @param $reset
 *  (default: FALSE) Reset static cache.
 *
 * @see _git_describe
 */
function git_last_tag($reset = FALSE) {
  return _git_describe('tag');
}

/**
 * Retrieve the number of commits since the last tag.
 *
 * @param $reset
 *  (default: FALSE) Reset static cache.
 *
 * @see _git_describe
 */
function git_commits_since_last_tag($reset = FALSE) {
  return _git_describe('since_last_tag');
}

/**
 * Retrieve the name of the last commit.
 *
 * @param $reset
 *  (default: FALSE) Reset static cache.
 *
 * @see _git_describe
 */
function git_last_commit($reset = FALSE) {
  return _git_describe('commit');
}

/**
 * Get the parsed results of git describe.
 *
 * @param $key
 *  (default: NULL) May be set to one of following to determine return.
 *  - tag: The most recent tag on the current branch.
 *  - since_last_tag: The number of commits since the most recent tag.
 *  - commit: The short commit name.
 * @param $reset
 *  (default: FALSE) Reset the static cache.
 */
function _git_describe($key = NULL, $reset = FALSE) {
  static $describe;

  if (empty($describe) || $reset) {
    $describe = $output = array();
    exec('git describe --long --always --tags', $output);
    if(preg_match('/(.*?)\-(\d+)\-([\d\w]+)$/', $output[0], $matches)) {
      $describe = array(
        'all' => $matches[0],
        'tag' => $matches[1],
        'since_last_tag' => $matches[2],
        'commit' => $matches[3],
      );
    }
    else {
      $describe = array(
        'all' => '-0-' . $output[0],
        'tag' => NULL,
        'since_last_tag' => '0',
        'commit' => $output[0],
      );
    }
  }

  return isset($key) ? $describe[$key] : $describe;
}
