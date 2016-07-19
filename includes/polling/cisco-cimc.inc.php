<?php
/*
 * LibreNMS module to hardware details from Cisco Integrated Management Controllers (CIMC)
 *
 * Copyright (c) 2016 Aaron Daniels <aaron@daniels.id.au>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if ($device['os'] == 'cimc') {

    $module = 'Cisco-CIMC';
    echo $module.': ';

    require_once 'includes/component.php';
    $component = new component();
    $components = $component->getComponents($device['device_id'],array('type'=>$module));

    // We only care about our device id.
    $components = $components[$device['device_id']];

    // Only collect SNMP data if we have enabled components
    if (count($components > 0)) {
        // Let's gather some data..
        $tblUCSObjects = snmpwalk_array_num($device, '.1.3.6.1.4.1.9.9.719.1', 0);

        // First, let's extract any active faults, we will use them later.
        $faults = array();
        foreach ($tblUCSObjects as $oid => $data) {
            if (strstr($oid, '1.3.6.1.4.1.9.9.719.1.1.1.1.5.')) {
                $id = substr($oid, 30);
                $fobj = $tblUCSObjects['1.3.6.1.4.1.9.9.719.1.1.1.1.5.'.$id];
                $fobj = preg_replace('/^sys/','/sys',$fobj);
                $faults[$fobj] = $tblUCSObjects['1.3.6.1.4.1.9.9.719.1.1.1.1.11.'.$id];
            }
        }

        foreach ($components as &$array) {

            // Because our discovery module was nice and stored the OID we need to poll for status, this is quite straight forward.
            if ($tblUCSObjects[$array['statusoid']] != 1) {
                // Yes, report an error
                $array['status'] = 2;
                $array['error'] = "Error Operability Code: ".$tblUCSObjects[$array['statusoid']]."\n";
            }
            else {
                // No, unset any errors that may exist.
                $array['status'] = 0;
                $array['error'] = '';
            }

            // if the type is chassis, we may have to add generic chassis faults
            if ($array['hwtype'] == 'chassis') {
                // See if there are any errors on this chassis.
                foreach ($faults as $key => $value) {
                    if (strstr($key,$array['label'])) {
                        // The fault is on this chassis.
                        $array['status'] = 2;
                        $array['error'] .= $value."\n";
                    }
                }
            }

            // Print some debugging
            if ($array['status'] == 0) {
                d_echo($array['label']." - Ok\n");
            }
            else {
                d_echo($array['label']." - ".$array['error']."\n");
            }
        }

        // Write the Components back to the DB.
        $component->setComponentPrefs($device['device_id'],$components);
        echo "\n";

    } // End if not error

}
