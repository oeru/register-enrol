<?php
global $wp_version;
$ore_data = get_option('ore_data');
if (!is_array($ore_data)) $ore_data = array();
// no DB changes necessary
// add notices
if (version_compare($wp_version, '4.3', '>=') && get_option('ore_notice')) {
    // upgrading from old version, first time we have ore_version too,
    // so must check for ore_notice presence
    if (empty($ore_data['notices'])) {
        $ore_data['notices'] = array();
    }
    $ore_data['notices']['password_link'] = 1;
    update_option('ore_data', $ore_data);
    delete_option('ore_notice');
    delete_option('ore_notification_override');
}
update_option('ore_version', ORE_VERSION);
