<?php
/**
 * @file
 * Drush policy commandfile. Modify as desired.
 *
 * See example file: 
 *
 * Validates commands as they are issued and returns an error
 * or changes options when policy is violated.
 *
 * You can copy this file to any of the following
 *   1. A .drush folder in your HOME folder.
 *   2. Anywhere in a folder tree below an active module on your site.
 *   3. /usr/share/drush/commands (configurable)
 *   4. In an arbitrary folder specified with the --include option.
 *   5. Drupal's /drush or sites/all/drush folder, or in the /drush
 *        folder in the directory above the Drupal root (note: sql-sync
 *        validation won't work in any of these locations).
 */

/**
 * Implements hook_drush_sitealias_alter
 *
 * Alter alias record data in code to allow 'parent'element.
 * 
 * A duplicate of the old implementation of the 'parent' element.
 * Keep this if you want to keep using 'parent', but do not want
 * to be nagged (or worse, break when it is removed).
 * From example policy.drush.inc
 * https://github.com/drush-ops/drush/blob/master/examples/policy.drush.inc#L43-L69
 */
function policy_drush_sitealias_alter(&$alias_record) {
  if (isset($alias_record['parent'])) {
    // Fetch and merge in each parent
    foreach (explode(',', $alias_record['parent']) as $parent) {
      $parent_record = drush_sitealias_get_record($parent);
      unset($parent_record['#name']);
      unset($parent_record['#file']);
      unset($parent_record['#hidden']);
      $array_based_keys = array_merge(drush_get_special_keys(), array('path-aliases'));
      foreach ($array_based_keys as $array_based_key) {
        if (isset($alias_record[$array_based_key]) && isset($parent_record[$array_based_key])) {
          $alias_record[$array_based_key] = array_merge($parent_record[$array_based_key], $alias_record[$array_based_key]);
        }
      }
      $alias_record = array_merge($parent_record, $alias_record);
    }
    unset($alias_record['parent']);
  }
}
