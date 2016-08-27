<?php
/**
 * rrdcached_queue_length.inc.php
 *
 * Generates a graph of the queue length for rrdcached
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2016 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

require 'rrdcached.inc.php';
require 'includes/graphs/common.inc.php';

$ds  = 'queue_length';

$colour_area = 'F37900';
$colour_line = 'FFA700';
$colour_area_max = 'F78800';

$unit_text = 'Queue Length';

require 'includes/graphs/generic_simplex.inc.php';
