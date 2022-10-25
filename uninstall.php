<?php

defined('WP_UNINSTALL_PLUGIN') or die("Bad request!");

delete_option('key');
delete_option('secret');
delete_option('woeid');
delete_option('count');
delete_site_option('key');
delete_site_option('secret');
delete_site_option('woeid');
delete_site_option('count');



