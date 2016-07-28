<?php require("session.php"); ?>
<?php
$title = "Cluster Checks";
$additionalStylesheet = array(  'css/jquery.dataTables.min.css',
                                'css/bootstrap-datetimepicker.css');
$additionalScript = array(  'js/jquery.dataTables.min.js',
                            'js/jszip.min.js',
                            'js/dataTables.autoFill.min.js',
                            'js/dataTables.bootstrap.min.js',
                            'js/dataTables.buttons.min.js',
                            'js/autoFill.bootstrap.min.js',
                            'js/buttons.bootstrap.min.js',
                            'js/buttons.colVis.min.js',
                            'js/buttons.html5.min.js',
                            'js/file-size.js',
                            'js/moment.js',
                            'js/bootstrap-datetimepicker.js');
require("header.php");
require("helper.php");

try {
  # Main class loading
  $check = new SexiCheck();
  # Header generation
  $check->displayHeader($_SERVER['SCRIPT_NAME']);
} catch (Exception $e) {
  # Any exception will be ending the script, we want exception-free run
  exit('  <div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span><span class="sr-only">Error:</span> ' . $e->getMessage() . '</div>');
}

if($check->getModuleSchedule('clusterConfigurationIssues') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'sqlQuery' => "SELECT c.name, c.dasenabled, c.lastconfigissue, c.lastconfigissuetime, v.name as vcenter FROM clusters c INNER JOIN vcenters v ON c.vcenter = v.id WHERE c.active = 1 AND c.lastconfigissue NOT LIKE '0'",
                          "id" => "CLUSTERCONFIGURATIONISSUES",
                          'thead' => array('Cluster Name', 'HA Status', 'Last Config Issue', 'Time', 'vCenter'),
                          'tbody' => array('"<td>".$entry["name"]."</td>"', '"<td>".(($entry["dasenabled"] == "1") ? "<i class=\"glyphicon glyphicon-ok-sign text-success\"></i>" : "<i class=\"glyphicon glyphicon-remove-sign text-danger\"></i>")."</td>"', '"<td>".$entry["lastconfigissue"]."</td>"', '"<td>".$entry["lastconfigissuetime"]."</td>"', '"<td>".$entry["vcenter"]."</td>"'),
                          'columnDefs' => '{ "orderable": false, className: "dt-body-center", "targets": [ 4 ] }']);
}

if($check->getModuleSchedule('alarms') != 'off') {
  $check->displayCheck([  'sqlQuery' => "SELECT a.name, a.status, a.name, a.time, a.entityMoRef, v.name as vcenter, c.name as entity FROM alarms a INNER JOIN vcenters v ON a.vcenter = v.id INNER JOIN clusters c ON a.entityMoRef = c.moref WHERE a.active = 1 AND a.entityMoRef LIKE 'ClusterComputeResource%'",
                          "id" => "ALARMSCLUSTER",
                          'thead' => array('Status', 'Alarm', 'Date', 'Name', 'vCenter'),
                          'tbody' => array('"<td>" . $this->alarmStatus[(string) $entry["status"]] . "</td>"', '"<td>" . $entry["name"] . "</td>"', '"<td>" . $entry["time"] . "</td>"', '"<td>" . $entry["entity"] . "</td>"', '"<td>" . $entry["vcenter"] . "</td>"'),
                          'order' => '[ 1, "asc" ]',
                          'columnDefs' => '{ "orderable": false, className: "dt-body-right", "targets": [ 0 ] }']);
}

if($check->getModuleSchedule('clusterHAStatus') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'sqlQuery' => "SELECT c.name, v.name as vcenter FROM clusters c INNER JOIN vcenters v ON c.vcenter = v.id WHERE c.active = 1 AND c.dasenabled NOT LIKE '1'",
                          "id" => "CLUSTERHASTATUS",
                          'thead' => array('Cluster Name', 'HA Status', 'vCenter'),
                          'tbody' => array('"<td>".$entry["name"]."</td>"', '"<td class=\"text-danger\"><i class=\"glyphicon glyphicon-remove-sign\"></i> no HA</td>"', '"<td>".$entry["vcenter"]."</td>"')]);
}
?>
    <h2>clusterAdmissionControl</h2>
    <h2>clusterDatastoreConsistency</h2>
    <h2>clusterMembersOvercommit</h2>

<?php
if($check->getModuleSchedule('clusterMembersVersion') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'sqlQuery' => "SELECT c.name, COUNT(DISTINCT h.esxbuild) as multipleBuild, GROUP_CONCAT(DISTINCT h.esxbuild SEPARATOR ',') as esxbuilds, v.name as vcenter FROM clusters c INNER JOIN hosts h ON c.id = h.cluster INNER JOIN vcenters v ON c.vcenter = v.id WHERE c.active = 1 GROUP BY c.name",
                          "id" => "CLUSTERMEMBERSVERSION",
                          'mismatchProperty' => 'esxbuild',
                          'thead' => array('Cluster Name', 'Compliance', 'Build Number', 'vCenter'),
                          'tbody' => array('"<td>" . $entry["name"] . "</td>"', '"<td>" . (($entry["multipleBuild"] == 1) ? "<i class=\"glyphicon glyphicon-ok-sign text-success\"></i>" : "<i class=\"glyphicon glyphicon-remove-sign text-danger\"></i>") . "</td>"', '"<td>" . $entry["esxbuilds"] . "</td>"', '"<td>" . $entry["vcenter"] . "</td>"'),
                          'columnDefs' => '{ "orderable": false, className: "dt-body-center", "targets": [ 1 ] }']);
}

if($check->getModuleSchedule('clusterMembersLUNPathCountMismatch') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'sqlQuery' => "SELECT c.id as clusterId, c.name as cluster, h.name, h.lunpathcount, v.name as vcenter FROM hosts h INNER JOIN clusters c ON h.cluster = c.id INNER JOIN vcenters v ON h.vcenter = v.id WHERE h.active = 1",
                          "id" => "CLUSTERMEMBERSLUNPATHCOUNTMISMATCH",
                          'typeCheck' => 'majorityPerCluster',
                          'majorityProperty' => 'lunpathcount',
                          'thead' => array('Cluster Name', 'Majority Path Count', 'Host Name', 'LUN Path Count', 'vCenter'),
                          'tbody' => array('"<td>" . $entry["cluster"] . "</td>"', '"<td>" . ($hMajority[$entry["clusterId"]]) . "</td>"', '"<td>" . $entry["name"] . "</td>"', '"<td>" . $entry["lunpathcount"] . "</td>"', '"<td>" . $entry["vcenter"] . "</td>"')]);
}

if($check->getModuleSchedule('clusterCPURatio') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'sqlQuery' => "SELECT c.name as name, c.id as clus, (SELECT SUM(h.numcpucore) FROM hosts h WHERE h.active = 1 AND h.cluster = clus) as pcpu, (SELECT SUM(vms.numcpu) FROM vms INNER JOIN hosts h ON vms.host = h.id WHERE vms.active = 1 AND h.cluster = clus) as vcpu, ROUND((SELECT SUM(vms.numcpu) FROM vms INNER JOIN hosts h ON vms.host = h.id WHERE vms.active = 1 AND h.cluster = clus)/(SELECT SUM(h.numcpucore) FROM hosts h WHERE h.active = 1 AND h.cluster = clus)) as vp_cpuratio, v.name as vcenter FROM clusters c INNER JOIN vcenters v ON c.vcenter = v.id WHERE active = 1 HAVING vp_cpuratio > ". $check->getConfig('thresholdCPURatio'),
                          "id" => "CLUSTERCPURATIO",
                          'thead' => array('Cluster Name', 'pCPU', 'vCPU', 'CPU ratio', 'vCenter'),
                          'tbody' => array('"<td>".$entry["name"]."</td>"', '"<td>".$entry["pcpu"]."</td>"', '"<td>".$entry["vcpu"]."</td>"', '"<td>".$entry["vp_cpuratio"]." : 1</td>"', '"<td>".$entry["vcenter"]."</td>"')]);
}

?>
    <h2>clusterTPSSavings</h2>
    <h2>clusterAutoSlotSize</h2>
    <h2>clusterProfile</h2>
  </div>
<?php require("footer.php"); ?>
