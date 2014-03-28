
<?php $this->Html->css('viewTemplate', array('inline' => false));?>

<script src="http://maps.googleapis.com/maps/api/js?key=AIzaSyDY0kkJiTPVd2U7aTOAwhc9ySH6oHxOIYM&sensor=false"></script>


<h1>Flight <?php echo $flight['Flight']['id'] ?></h1>
<?php echo $this->Html->link('Back to all flights', 
      array('controller' => 'flights', 'action' => 'index'))?>
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
    <button type="button" class="btn btn-default" id="Other">Other Stuff</button>
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
          labels: [ "x", "Temp", "RPM" ],
          RPM : {
            axis : {}
          }
        }
        );
    }
  }

  var AirspeedButton = document.getElementById("Airspeed");
  var EngineButton = document.getElementById("Engine");
  var OtherButton = document.getElementById("Other");
  AirspeedButton.addEventListener("click", changeGraph);
  EngineButton.addEventListener("click", changeGraph);
  OtherButton.addEventListener("click", changeGraph);
</script>

