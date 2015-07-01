<?php

/**
 * Plugin Name: IPBlock
 * Plugin URI: https://wordpress.org/plugins/ipblock
 * Description: Powerful login throttling plugin for Your Wordpress site
 * Version: 1.1
 * Author: Maciej Krawczyk
 * Author URI: https://profiles.wordpress.org/helium-3
 * License: GPLv2 or later
=====================================================================================
Copyright (C) 2015 Maciej Krawczyk

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with WordPress; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
=====================================================================================
*/


defined('ABSPATH') or die();


if (basename($_SERVER['PHP_SELF'])==='wp-login.php') require(dirname(__FILE__).'/ipblock.php');

elseif (is_admin()) require(dirname(__FILE__).'/settings.php');


?>
