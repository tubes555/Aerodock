<?php $this->Html->css('viewTemplate', array('inline' => false));?>

<script src="http://maps.googleapis.com/maps/api/js?key=AIzaSyDY0kkJiTPVd2U7aTOAwhc9ySH6oHxOIYM&sensor=false"></script>
<div class="row">
  <div  class="col-md-7">
    <div id="googleMap" style="width:500px;height:380px;"></div>
  </div>
  <div class="col-md-4">
        <table class="table table-striped">
      <tr>
        <td id="t1">Turn One</td>
      </tr>
      <tr>
        <td id="t2">Turn Two</td>
      </tr>
      <tr>
        <td id="t3">Turn Three</td>
      </tr>
      <tr>
        <td id="t4">Turn Four</td>
      </tr>
    </table>
  </div>
</div>
<div class="row text-center graph-selector">
  <div class="btn-group">
    <button type="button" class="btn btn-default" id="Airspeed">Airspeed</button>
    <button type="button" class="btn btn-default" id="Engine">Engine Block</button>
    <button type="button" class="btn btn-default" id="Tracking">Tracking</button>
  </div>
</div>
<div class="row">
  <div class="col-md-9">
    <div id="graph" style="width:900px; height:300px;">
    </div>
  </div>
</div>
<?php echo $this->Html->script($jspath) ?>
<?php echo $this->Html->script($jslatlng) ?>
<?php echo $this->Html->script('dygraph-combined');?>
<script>
  var map;
  var mapProp;

  function initialize()
  {
    mapProp = {
      center: new google.maps.LatLng(<?php echo $center['lat']; ?>, <?php echo $center['long'];?>),
      zoom: <?php echo $zoomLevel ?>,
      scrollwheel: false,
      mapTypeId:google.maps.MapTypeId.ROADMAP
    };

    map=new google.maps.Map(document.getElementById("googleMap"),mapProp);
    var flightPath = new google.maps.Polyline({
      path: flightCoords,
      geodesic: true,
      strokeColor: '#FF0000',
      strokeOpacity: 1.0,
      strokeWeight: 2
    });

    flightPath.setMap(map);
    
  }

  google.maps.event.addDomListener(window, 'load', initialize);

  g3 = new Dygraph(
    document.getElementById("graph"),
      altAirspeed,
    {
      labels: [ "x", "altitude", "airspeed" ],
      airspeed : {
        axis : {}
      }
    }
  );

  function changeGraph(eventObject, argumentsObject)
  {
    if(eventObject.srcElement.id == "Airspeed"){
      g3 = new Dygraph(
        document.getElementById("graph"),
          altAirspeed,
        {
          labels: [ "x", "altitude", "airspeed" ],
          airspeed : {
            axis : {}
          }
        }
        );
    }
    if(eventObject.srcElement.id == "Engine"){
      g3 = new Dygraph(
        document.getElementById("graph"),
          engine,
        {
          labels: [ "Time", "Temp", "RPM" ],
          RPM : {
            axis : {}
          }
        }
        );
    }
    if(eventObject.srcElement.id == "Tracking"){
      g3 = new Dygraph(
        document.getElementById("graph"),
          tracking,
          {
            labels: [ "Time", "Degrees" ]
          }
        );
    }
  }

  var AirspeedButton = document.getElementById("Airspeed");
  var EngineButton = document.getElementById("Engine");
  var TrackingButton = document.getElementById("Tracking");
  AirspeedButton.addEventListener("click", changeGraph);
  EngineButton.addEventListener("click", changeGraph);
  TrackingButton.addEventListener("click", changeGraph);
/*
  var boxBound = new google.maps.LatLngBounds(
    new google.maps.LatLng(35.6556-0.001,-86.2666-0.007),
        new google.maps.LatLng(35.6597+0.001,-86.2725+0.007));
  var rectangle1 = new google.maps.Rectangle({
      strokeColor: '#0000FF',
      strokeOpacity: 0.8,
      strokeWeight: 2,
      fillColor: '#0000FF',
      fillOpacity: 0.2,
      map: map,
      bounds : boxBound
  });

  var boxBound = new google.maps.LatLngBounds(
    new google.maps.LatLng(35.6405-0.001,-86.268-0.007),
        new google.maps.LatLng(35.6463+0.001,-86.2751+0.007));
  var rectangle2 = new google.maps.Rectangle({
      strokeColor: '#0000FF',
      strokeOpacity: 0.8,
      strokeWeight: 2,
      fillColor: '#0000FF',
      fillOpacity: 0.2,
      map: map,
      bounds : boxBound
  });

  var boxBound = new google.maps.LatLngBounds(
    new google.maps.LatLng(35.6141-0.001,-86.2616-0.007),
        new google.maps.LatLng(35.6188+0.001,-86.2679+0.007));
  var rectangle3 = new google.maps.Rectangle({
      strokeColor: '#0000FF',
      strokeOpacity: 0.8,
      strokeWeight: 2,
      fillColor: '#0000FF',
      fillOpacity: 0.2,
      map: map,
      bounds : boxBound
  });
  var rectangle = [];
  rectangle[0] = rectangle1;
  rectangle[1] = rectangle2;
  rectangle[2] = rectangle3;

  function changeT(){
    var turn = Number(this.id[1])-1;
    for (var i = 0; i < 3; i++) {
      if(i != turn){
        rectangle[i].setMap(null);
      }
      rectangle[turn].setMap(map);
    }
  }

  t1.addEventListener("mouseover", changeT);
  t2.addEventListener("mouseover", changeT);
  t3.addEventListener("mouseover", changeT);
  t1.addEventListener("mouseleave", changeT);
  t2.addEventListener("mouseout", changeT);
  t3.addEventListener("mouseout", changeT);
  */
</script>

