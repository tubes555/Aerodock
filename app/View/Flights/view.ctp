<script src="http://maps.googleapis.com/maps/api/js?key=AIzaSyDY0kkJiTPVd2U7aTOAwhc9ySH6oHxOIYM&sensor=false"></script>

<h1>Flight <?php echo $flight['Flight']['id'] ?></h1>

<div  class="col-md-6">
  <div id="googleMap" style="width:500px;height:380px;"></div>
</div>

<script>
  var map;
  var mapProp;
  function initialize()
  {
    mapProp = {
      center:new google.maps.LatLng(35.878, -86.3795624),
      zoom:12,
      mapTypeId:google.maps.MapTypeId.ROADMAP
    };

    var flightPlanCoordinates = [

    		<?php for ($i=0; $i < count($latLong['lat']); $i++): ?>
    		<?php if(!ctype_space($latLong['lat'][$i]) and !ctype_space($latLong['lat'][$i])): ?>
        new google.maps.LatLng(<?php echo floatval($latLong['lat'][$i])?>, <?php echo floatval($latLong['long'][$i])?>),
        <?php endif ?>
        <?php endfor ?>]
    map=new google.maps.Map(document.getElementById("googleMap")
    ,mapProp);
    var flightPath = new google.maps.Polyline({
      path: flightPlanCoordinates,
      geodesic: true,
      strokeColor: '#FF0000',
      strokeOpacity: 1.0,
      strokeWeight: 2
    });

    flightPath.setMap(map);
    
  }
  google.maps.event.addDomListener(window, 'load', initialize);
  </script>