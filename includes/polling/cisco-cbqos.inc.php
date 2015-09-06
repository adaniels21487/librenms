<?php
/*
 * LibreNMS module to capture Cisco Class-Based QoS Details
 *
 * Copyright (c) 2015 Aaron Daniels <aaron@daniels.id.au>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if ($device['os_group'] == "cisco") {

    $MODULE = 'Cisco-CBQOS';

    require_once 'includes/component.php';
    $COMPONENT = new component();
    $options['filter']['type'] = array('=',$MODULE);
    $options['filter']['disabled'] = array('=',0);
    $COMPONENTS = $COMPONENT->getComponents($device['device_id'],$options);

    // Only collect SNMP data if we have enabled components
    if (count($COMPONENTS > 0)) {
        // Let's gather the stats..
        $tblcbQosClassMapStats = dual_indexed_snmp_array(snmp_walk($device, '.1.3.6.1.4.1.9.9.166.1.15.1.1', '-Osqn'));

        // Loop through the components and extract the data.
        foreach ($COMPONENTS as $KEY => $ARRAY) {
            $TYPE = $ARRAY['qos-type'];

            // Get data from the class-map table.
            if ($TYPE == 2) {
                // Let's make sure the RRD is setup for this component.
                $filename = "cisco-cbqos-".$ARRAY['sp-id']."-".$ARRAY['sp-obj'].".rrd";
                $rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename ($filename);

                if (!file_exists ($rrd_filename)) {
                    rrdtool_create ($rrd_filename, " DS:postbits:GAUGE:600:0:U DS:bufferdrops:GAUGE:600:0:U DS:qosdrops:GAUGE:600:0:U" . $config['rrd_rra']);
                }

                $TEMP['postbytes'] = $tblcbQosClassMapStats['1.3.6.1.4.1.9.9.166.1.15.1.1.10'][$ARRAY['sp-id']][$ARRAY['sp-obj']];
                $TEMP['bufferdrops'] = $tblcbQosClassMapStats['1.3.6.1.4.1.9.9.166.1.15.1.1.21'][$ARRAY['sp-id']][$ARRAY['sp-obj']];
                $TEMP['qosdrops'] = $tblcbQosClassMapStats['1.3.6.1.4.1.9.9.166.1.15.1.1.17'][$ARRAY['sp-id']][$ARRAY['sp-obj']];
                $RRD_ENTRY = "N:" . $TEMP['postbytes'] . ":" . $TEMP['bufferdrops'] . ":" . $TEMP['qosdrops'];

                // Update RRD
                rrdtool_update ($rrd_filename, $RRD_ENTRY);

                // Clean-up after yourself!
                unset($filename, $rrd_filename, $TEMP);
            }
        } // End foreach components

    } // end if count components

    // Clean-up after yourself!
    unset($TYPE, $COMPONENTS, $COMPONENT, $options, $MODULE);
}