<html>
	<head>
		<style type="text/css"></style>
		<link rel="stylesheet" type="text/css" href="css/bootstrap.css">
		<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
		<link rel="stylesheet" type="text/css" href="css/bootstrap-theme.min.css">
		<link rel="stylesheet" type="text/css" href="css/jajal.css">
		<script type="text/javascript" src='js/jquery-1.11.2.min.js'></script>
		<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js"></script>
		<script type="text/javascript" src='js/bootstrap.min.js'></script>
		<script type="text/javascript" src='js/bootstrap.js'></script>
		<script src="hc/js/highcharts.js"></script>
		<script src="hc/js/exporting.js"></script>
		<script type="text/javascript">
		function load() {
      var map = new google.maps.Map(document.getElementById("map"), {
        center: new google.maps.LatLng(-6.891942, 107.610850),
        zoom: 16,
        mapTypeId: 'roadmap'
      });
      var infoWindow = new google.maps.InfoWindow;

      // Change this depending on the name of your PHP file
      downloadUrl("phpsqlajax_genxml3.php", function(data) {
        var xml = data.responseXML;
        var markers = xml.documentElement.getElementsByTagName("marker");
        for (var i = 0; i < markers.length; i++) {
          var address = markers[i].getAttribute("address");
          var tanggal = markers[i].getAttribute("tanggal");
          var point = new google.maps.LatLng(
              parseFloat(markers[i].getAttribute("lat")),
              parseFloat(markers[i].getAttribute("lng")));
          var html = "<b>" + tanggal + "</b> <br/>" + address;
          var marker = new google.maps.Marker({
            map: map,
            position: point,
          });
          bindInfoWindow(marker, map, infoWindow, html);
        }
      });
    }

    function bindInfoWindow(marker, map, infoWindow, html) {
      google.maps.event.addListener(marker, 'click', function() {
        infoWindow.setContent(html);
        infoWindow.open(map, marker);
      });
    }

    function downloadUrl(url, callback) {
      var request = window.ActiveXObject ?
          new ActiveXObject('Microsoft.XMLHTTP') :
          new XMLHttpRequest;

      request.onreadystatechange = function() {
        if (request.readyState == 4) {
          request.onreadystatechange = doNothing;
          callback(request, request.status);
        }
      };

      request.open('GET', url, true);
      request.send(null);
    }

    function doNothing() {}
		</script>
	</head>
<body onload="load()">
	<nav class="navbar navother-color navbdr navbar-fixed-top clmnpad center ">
		  <div class= "container">
		  	<div class='navbar-header'>
		  	<a href="#" class='navbar-brand'>Lambang</a>
		  	<button class='navbar-toggle' data-toggle = 'collapse' data-target = '.navHeaderCollapse'>
		  		<span style='color:#FF8533' >-</span>
		  		<span style='color:#FF8533' >-</span>
		  		<span style='color:#FF8533' >-</span>
		  	</button>
		  </div>
		  <div class='collapse navbar-collapse navbar-right myfont3'>
		  	<ul class='nav navbar-nav'>
		  		<li><a href="#">FB</a></li>
		  	</ul>
		  </div>
		  <div class='collapse navbar-collapse navbar-right navHeaderCollapse myfont3'>
		  	<ul class='nav navbar-nav'>
		  		<li><a href="#">Home</a></li>
		  		<li><a href="../mamet/chart.php">Maps</a></li>
		  		<li><a href="#">Link</a></li>
		  	</ul>
		  </div>
		 </div>
	</nav>
		<div class='container padtop1'>
			<div class='row'>
				<div class='col-xs-8 col-xs-offset-2 padbtm3' id="map" style="height: 600px"></div>
			</div>
    	</div> 
	<div class='container'>
		<div class='row'>
			<div class='col-xs-8 col-xs-offset-2' id="container" style="height: 400px"></div>
		</div>
	</div>
	<?php
	include('koneksim.php');
    $toffset=7*60*60; //converting 7 hours to seconds.
    $txoffset=8*60*60; //converting 7 hours to seconds.
    $timeFormat="H:i";
    $dateFormat="d-m-Y"; //set the date format
    $time=gmdate($timeFormat, time()+$toffset); //get GMT date + 7
    $date=gmdate($dateFormat);
    $datea=date('Y-m-d', strtotime('+0 days'));
    $dateb=date('Y-m-d', strtotime('-1 days'));
    $datec=date('Y-m-d', strtotime('-2 days'));
    $dated=date('Y-m-d', strtotime('-3 days'));
    $datee=date('Y-m-d', strtotime('-4 days'));
    $datef=date('Y-m-d', strtotime('-5 days'));
    $dateg=date('Y-m-d', strtotime('-6 days'));

    $query = "SELECT * from kecelakaan where tanggal = '$datea'";
	$result = mysql_query($query);
	$data1 = mysql_num_rows($result);
	if (!$result) {
  		die('Invalid query: ' . mysql_error());
		}

	$query2 = "SELECT * from kecelakaan where tanggal = '$dateb'";
	$result2 = mysql_query($query2);
	$data2 = mysql_num_rows($result2);
	if (!$result2) {
	  die('Invalid query: ' . mysql_error());
	}

	$query3 = "SELECT * from kecelakaan where tanggal = '$datec'";
	$result3 = mysql_query($query3);
	$data3 = mysql_num_rows($result3);
	if (!$result3) {
	  die('Invalid query: ' . mysql_error());
	}

	$query4 = "SELECT * from kecelakaan where tanggal = '$dated'";
	$result4 = mysql_query($query4);
	$data4 = mysql_num_rows($result4);
	if (!$result4) {
	  die('Invalid query: ' . mysql_error());
	}

	$query5 = "SELECT * from kecelakaan where tanggal = '$datee'";
	$result5 = mysql_query($query5);
	$data5 = mysql_num_rows($result5);
	if (!$result5) {
	  die('Invalid query: ' . mysql_error());
	}

	$query6 = "SELECT * from kecelakaan where tanggal = '$datef'";
	$result6 = mysql_query($query6);
	$data6 = mysql_num_rows($result6);
	if (!$result6) {
	  die('Invalid query: ' . mysql_error());
	}

	$query7 = "SELECT * from kecelakaan where tanggal = '$dateg'";
	$result7 = mysql_query($query7);
	$data7 = mysql_num_rows($result7);
	if (!$result7) {
	  die('Invalid query: ' . mysql_error());
	}

    ?>



	<script type="text/javascript">

$(function () {
    $('#container').highcharts({
        title: {
            text: 'Grafik Kejadian Kecelakaan Per Minggu',
            x: -20 //center
        },
        subtitle: {
            text: 'Source: MaMET Database',
            x: -20
        },
        xAxis: {
            categories: ['<?php echo $datea;?>', '<?php echo $dateb;?>', '<?php echo $datec;?>', '<?php echo $dated;?>', '<?php echo $datee;?>', '<?php echo $datef;?>',
                '<?php echo $dateg;?>']
        },
        yAxis: {
            title: {
                text: 'Jumlah Kecelakaan'
            },
            plotLines: [{
                value: 0,
                width: 1,
                color: '#808080'
            }]
        },
        tooltip: {
            valueSuffix: ' Kejadian'
        },
        legend: {
            layout: 'vertical',
            align: 'right',
            verticalAlign: 'middle',
            borderWidth: 0
        },
        series: [{
            name: 'Jumlah',
            data: [<?php echo $data1;?>, <?php echo $data2;?>, <?php echo $data3;?>, <?php echo $data4;?>, <?php echo $data5;?>, <?php echo $data6;?>, <?php echo $data7;?>]
        }]
    });
});
	</script>
</body>
</html>