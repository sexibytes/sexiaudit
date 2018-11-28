<?php require("helper.php"); ?>
<!DOCTYPE HTML>
<html>
<head>
  <meta charset="utf-8">
  <title>Read-Only VM Inventory</title>
  <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
  <link rel="stylesheet" type="text/css" href="css/BootstrapXL.css">
  <link rel="stylesheet" type="text/css" href="css/sexiauditor.css">
  <link rel="stylesheet" type="text/css" href="css/jquery.dataTables.min.css">
  <script type="text/javascript" src="js/jquery-3.1.0.min.js"></script>
  <script type="text/javascript" src="js/bootstrap.min.js"></script>
  <script type="text/javascript" src="js/jquery.dataTables.min.js"></script>
  <script type="text/javascript" src="js/jszip.min.js"></script>
  <script type="text/javascript" src="js/dataTables.autoFill.min.js"></script>
  <script type="text/javascript" src="js/dataTables.bootstrap.min.js"></script>
  <script type="text/javascript" src="js/dataTables.buttons.min.js"></script>
  <script type="text/javascript" src="js/autoFill.bootstrap.min.js"></script>
  <script type="text/javascript" src="js/buttons.bootstrap.min.js"></script>
  <script type="text/javascript" src="js/buttons.colVis.min.js"></script>
  <script type="text/javascript" src="js/buttons.html5.min.js"></script>
  <script type="text/javascript" src="js/file-size.js"></script>
  <link rel="icon" type="image/png" sizes="32x32" href="images/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="96x96" href="images/favicon-96x96.png">
  <link rel="icon" type="image/png" sizes="16x16" href="images/favicon-16x16.png">
</head>
<body>
  <div id="wrapper">
    <nav class="navbar navbar-default navbar-fixed-top" role="navigation" style="margin-bottom: 0">
      <div class="navbar-brand"><a href="#">SexiAuditor</a></div>
    </nav>
  </div>
<?php
$sexihelper = new SexiHelper();

if ($sexihelper->getConfig('anonymousROInventory') == 'disable')
{

  try
  {
    
    throw new Exception('Option to allow anonymous access to VM inventory has been disable from your appliance. Please contact your administrator to enable it.');
    
  }
  catch (Exception $e)
  {
    
    # Any exception will be ending the script, we want exception-free run
    # CSS hack for navbar margin removal
    echo '  <style>#wrapper { margin-bottom: 0px !important; }</style>'."\n";
    require("exception.php");
    exit;

  } # END try

} # END if ($sexihelper->getConfig('anonymousROInventory') == 'disable')

?>
  
  <div style="padding-top: 10px; padding-bottom: 10px;" class="container" id="wrapper-container">
    <div class="row">
      <div class="col-lg-12 alert alert-info" style="padding: 6px; margin-top: 20px; text-align: center;">
        <h1 style="margin-top: 10px;">Global Inventory <small>on <?php echo date("j F Y"); ?> <br><small><a href="/latest-hosts.csv">ESX dump csv file <i class="glyphicon glyphicon-share"></i></a> | <a href="/latest-vms.csv">VM dump csv file <i class="glyphicon glyphicon-share"></i></a></small></small></h1>
      </div>
    </div>

    <div style="width:98%; padding:10px;">
      <div>Show/Hide:
        <button type="button" class="btn btn-success btn-xs toggle-vis" style="outline: 5px auto;" name="1" data-column="1">VM</button>
        <button type="button" class="btn btn-success btn-xs toggle-vis" style="outline: 5px auto;" name="2" data-column="2">vCenter</button>
        <button type="button" class="btn btn-success btn-xs toggle-vis" style="outline: 5px auto;" name="3" data-column="3">Cluster</button>
        <button type="button" class="btn btn-success btn-xs toggle-vis" style="outline: 5px auto;" name="4" data-column="4">Host</button>
        <button type="button" class="btn btn-danger btn-xs toggle-vis" style="outline: 5px auto;" name="5" data-column="5">vmx Path</button>
        <button type="button" class="btn btn-danger btn-xs toggle-vis" style="outline: 5px auto;" name="6" data-column="6">Portgroup</button>
        <button type="button" class="btn btn-danger btn-xs toggle-vis" style="outline: 5px auto;" name="7" data-column="7">IP</button>
        <button type="button" class="btn btn-danger btn-xs toggle-vis" style="outline: 5px auto;" name="8" data-column="8">NumCPU</button>
        <button type="button" class="btn btn-danger btn-xs toggle-vis" style="outline: 5px auto;" name="9" data-column="9">MemoryMB</button>
        <button type="button" class="btn btn-danger btn-xs toggle-vis" style="outline: 5px auto;" name="10" data-column="10">CommitedGB</button>
        <button type="button" class="btn btn-danger btn-xs toggle-vis" style="outline: 5px auto;" name="11" data-column="11">ProvisionnedGB</button>
        <button type="button" class="btn btn-success btn-xs toggle-vis" style="outline: 5px auto;" name="12" data-column="12">Datastore</button>
        <button type="button" class="btn btn-danger btn-xs toggle-vis" style="outline: 5px auto;" name="13" data-column="13">VMPath</button>
        <button type="button" class="btn btn-danger btn-xs toggle-vis" style="outline: 5px auto;" name="14" data-column="14">MAC</button>
        <button type="button" class="btn btn-danger btn-xs toggle-vis" style="outline: 5px auto;" name="15" data-column="15">PowerState</button>
        <button type="button" class="btn btn-danger btn-xs toggle-vis" style="outline: 5px auto;" name="16" data-column="16">OS</button>
        <button type="button" class="btn btn-danger btn-xs toggle-vis" style="outline: 5px auto;" name="17" data-column="17">Group</button>
      </div>
  		<hr />
      <table id="inventory" class="table display" cellspacing="0" width="100%">
        <thead><tr>
          <th>id</th>
          <th>VM</th>
          <th>vCenter</th>
          <th>Cluster</th>
          <th>Host</th>
          <th>vmx Path</th>
          <th>Portgroup</th>
          <th>IP</th>
          <th>NumCPU</th>
          <th>MemoryMB</th>
          <th>CommitedGB</th>
          <th>ProvisionnedGB</th>
          <th>Datastore</th>
          <th>VM Path</th>
          <th>MAC</th>
          <th>PowerState</th>
          <th>GuestOS</th>
          <th>Group</th>
        </tr></thead>
        <tfoot><tr>
          <th>id</th>
          <th>VM</th>
          <th>vCenter</th>
          <th>Cluster</th>
          <th>Host</th>
          <th>vmx Path</th>
          <th>Portgroup</th>
          <th>IP</th>
          <th>NumCPU</th>
          <th>MemoryMB</th>
          <th>CommitedGB</th>
          <th>ProvisionnedGB</th>
          <th>Datastore</th>
          <th>VM Path</th>
          <th>MAC</th>
          <th>PowerState</th>
          <th>GuestOS</th>
          <th>Group</th>
        </tr></tfoot>
        <tbody>
        </tbody>
      </table>
    </div>
  </div>
  <div id="modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="plan-info" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-body"><!-- /# content will goes here after ajax calls --></div>
        <div class="modal-footer">
          <a href="#" id="modal-previous" rel="modal" style="display:none;" class="btn btn-primary">Go back</a>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <script type="text/javascript">
    $(document).ready( function () {
      // Setup - add a text input to each footer cell
      $('#inventory tfoot th').each( function () {
          var title = $(this).text();
          $(this).html( '<input type="text" placeholder="Search '+title+'" />' );
      } );
      var table = $('#inventory').DataTable( {
        "language": { "infoFiltered": "" },
        "processing": true,
        "serverSide": true,
        "ajax": "server_processing.php?c=ROVMINVENTORY",
        "deferRender": true,
        "search": {
          "smart": false,
          "regex": true
        },
        "columnDefs": [ { "targets": [ 0, 5, 6, 7, 8, 9, 10, 11, 13, 14, 15, 16, 17 ], "visible": false } ],
        "lengthMenu": [ [10, 25, 50, -1], [10, 25, 50, "All"] ]
      } );
      // Apply the search
      table.columns().every( function () {
        var that = this;
        $( 'input', this.footer() ).on( 'keyup change', function () {
          if ( that.search() !== this.value ) {
            that
              .search( this.value )
              .draw();
          }
        } );
      } );
      new $.fn.dataTable.Buttons( table, { buttons: [ 'csv' ] } );
      table.buttons().container().appendTo( '#inventory_wrapper .col-sm-6:eq(0)' );
      $('#inventory').on('click', 'a[rel=modal]', function(evt) {
        evt.preventDefault();
        var modal = $('#modal').modal();
        modal.find('.modal-body').load($(this).attr('href'), function (responseText, textStatus) {
          if ( textStatus === 'success' || textStatus === 'notmodified') {
            modal.show();
          }
        });
      });
      $('button.toggle-vis').on( 'click', function (e) {
        e.preventDefault();
        var column = table.column( $(this).attr('data-column') );
        column.visible( ! column.visible() );
        var nodeList = document.getElementsByName( $(this).attr('data-column') );
        var regexMatch = new RegExp("btn-success","g");
        if (nodeList[0].className.match(regexMatch)) {
          nodeList[0].className = "btn btn-danger btn-xs toggle-vis btn-no-outline";
        } else {
          nodeList[0].className = "btn btn-success btn-xs toggle-vis btn-no-outline";
        }
      } );
    });
    $('#modal').on('click', 'a[rel=modal]', function(evt) {
      evt.preventDefault();
      var modal = $('#modal').modal();
      modal.find('.modal-body').load($(this).attr('href'), function (responseText, textStatus) {
        if ( textStatus === 'success' || textStatus === 'notmodified') {
          modal.show();
        }
      });
    });
    $("#modal").on("shown.bs.modal",function(){
       $(this).hide().show(); 
    });
  </script>
</body>
</html>
