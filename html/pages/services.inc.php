<?php

$pagetitle[] = 'Services';

print_optionbar_start();

echo "<span style='font-weight: bold;'>Services</span> &#187; ";

$menu_options = array(
    'basic'   => 'Basic',
);
if (!$vars['view']) {
    $vars['view'] = 'basic';
}

$status_options = array(
    'all'       => 'All',
    'ok'        => 'Ok',
    'warning'   => 'Warning',
    'critical'  => 'Critical',
);
if (!$vars['state']) {
    $vars['state'] = 'all';
}

// The menu option - on the left
$sep = '';
foreach ($menu_options as $option => $text) {
    if (empty($vars['view'])) {
        $vars['view'] = $option;
    }

    echo $sep;
    if ($vars['view'] == $option) {
        echo "<span class='pagemenu-selected'>";
    }

    echo generate_link($text, $vars, array('view' => $option));
    if ($vars['view'] == $option) {
        echo '</span>';
    }

    $sep = ' | ';
}
unset($sep);

// The status option - on the right
echo '<div class="pull-right">';
$sep = '';
foreach ($status_options as $option => $text) {
    if (empty($vars['state'])) {
        $vars['state'] = $option;
    }

    echo $sep;
    if ($vars['state'] == $option) {
        echo "<span class='pagemenu-selected'>";
    }

    echo generate_link($text, $vars, array('state' => $option));
    if ($vars['state'] == $option) {
        echo '</span>';
    }

    $sep = ' | ';
}
unset($sep);
echo '</div>';
print_optionbar_end();

$sql_param = array();
if (isset($vars['state'])) {
    if ($vars['state'] == 'ok') {
        $state = '1';
    }
    elseif ($vars['state'] == 'critical') {
        $state = '0';
    }
    elseif ($vars['state'] == 'warning') {
        $state = '2';
    }
}
if (isset($state)) {
    $where      .= " AND service_status= ? AND service_disabled='0' AND `service_ignore`='0'";
    $sql_param[] = $state;
}

?>
<div class="row col-sm-12" id="nagios-services">
    <table class="table table-hover table-condensed table-striped">
        <tr>
            <th>Device</th>
            <th>Service</th>
            <th>Changed</th>
            <th>Message</th>
            <th>Description</th>
        </tr>
<?php
if ($_SESSION['userlevel'] >= '5') {
    $host_sql = 'SELECT * FROM devices AS D, services AS S WHERE D.device_id = S.device_id GROUP BY D.hostname ORDER BY D.hostname';
    $host_par = array();
}
else {
    $host_sql = 'SELECT * FROM devices AS D, services AS S, devices_perms AS P WHERE D.device_id = S.device_id AND D.device_id = P.device_id AND P.user_id = ? GROUP BY D.hostname ORDER BY D.hostname';
    $host_par = array($_SESSION['user_id']);
}

$shift = 1;
foreach (dbFetchRows($host_sql, $host_par) as $device) {
    $device_id       = $device['device_id'];
    $device_hostname = $device['hostname'];
    $devlink = generate_device_link($device);
    if ($shift == 1) {
        array_unshift($sql_param, $device_id);
        $shift = 0;
    }
    else {
        $sql_param[0] = $device_id;
    }

    foreach (dbFetchRows("SELECT * FROM `services` WHERE `device_id` = ? $where", $sql_param) as $service) {
        if ($service['service_status'] == '0') {
            $status = "<span class='red'><b>".$service['service_type']."</b></span>";
        }
        else if ($service['service_status'] == '1') {
            $status = "<span class='green'><b>".$service['service_type']."</b></span>";
        }
        else {
            $status = "<span class='grey'><b>".$service['service_type']."</b></span>";
        }
?>
        <tr>
            <td><?=$devlink?></td>
            <td><?=$status?></td>
            <td><?=formatUptime(time() - $service['service_changed'])?></td>
            <td><span class='box-desc'><?=nl2br(trim($service['service_message']))?></span></td>
            <td><span class='box-desc'><?=nl2br(trim($service['service_desc']))?></span></td>
        </tr>
<?php
    }//end foreach

    unset($samehost);
}//end foreach
?>
    </table>
</div>
