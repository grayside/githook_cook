<?php

/**
 * @file
 *  Library of git function wrappers.
 */

/**
 * Return a list of the names of all staged files.
 */
function git_staged_files($reset = FALSE) {
  return array(
    'test-data/test.php',
    'test-data/test.txt',
    'test-data/test.module',
  );
}

