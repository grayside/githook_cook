# GitHook Cook

GitHook Cook is my attempt to make implementing workflows with Git 
Hooks simpler for PHP developers. This code approaches git hooks with a 
Drupal sensibility of implementing hooks rather than creating files.

This project might be dropped (or simply take inspiration from) 
[https://github.com/icefox/git-hooks git-hooks], which I found after 
beginning work here.

## Installation

Installation is currently super primitive.

1. Place the cook directory inside the .git/hooks directory of your 
repository.
2. Create symlinks from existing git hooks to githook_cook.php. For example:

    $> ln -s cook/githook_cook.php pre-commit

## Testing Workflows
githook_cook.php can be run standalone.

    $> ./githook_cook.php test <git-event>

Can be used to run the routines of, say, pre-commit against a directory of mostly empty test files.

This test mode replaces the git.inc library with a test.git.inc library with faked functionality.

## Future Plans
1. Have Drupal Coder validation.
2. Implement some kind of configuration of Cook Plugins. Possibly using 
Git configuration. Definitely needs to support system defaults and 
local overrides.
3. Provide some wrapper functions for other common git workflow tasks, 
such as sending emails, getting other common git data.
