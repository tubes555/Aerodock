<?php $this->Html->css('viewTemplate', array('inline' => false));?>
<?php echo $this->Html->script('jquery-2.1.0.min'); ?>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script src="http://maps.googleapis.com/maps/api/js?key=AIzaSyDY0kkJiTPVd2U7aTOAwhc9ySH6oHxOIYM&sensor=false"></script>
<div class="row">
  <div  class="col-md-7">
    <div id="googleMap" style="width:500px;height:380px;"></div>
  </div>
  
</div>
<div class="row text-center graph-selector">
  <div class="btn-group">
    <button type="button" class="btn btn-default" id="Airspeed">Alt/Airspeed</button>
    <button type="button" class="btn btn-default" id="Engine">Engine Temps</button>
    <button type="button" class="btn btn-default" id="RPM">Manifold/RPM</button>
    <button type="button" class="btn btn-default" id="Oil">Oil Temp/Pres</button>
    <button type="button" class="btn btn-default" id="Fuel">Fuel Flow/Pres</button>
  </div>
</div>
<div class="row">
  <div class="col-md-9">
    <div id="chart_div"></div>
    </div>
  </div>
</div>
<script>
  var interval;
  var rectangle = []; 
  var flightCoord = [];
  var events;
  var test;
  var graphData=[];
  var map;
  var mapProp;
  var chart;
  var marker;
  var currentGraph;
  var beginSlice = 0;
  var endSlice = <?php echo $endSlice; ?>;
  var altOn = true;
  var engineOn = false;
  var rpmOn = false;
  var oilOn = false;
  var fuelOn = false;
  var image = {
        url:  "https://maps.gstatic.com/intl/en_us/mapfiles/markers2/measle_blue.png",
        size: new google.maps.Size(7, 7),
        origin: new google.maps.Point(0,0),
        anchor: new google.maps.Point(3, 3)
  }
  var clicked = false;
  var AirspeedButton = document.getElementById("Airspeed");
  var EngineButton = document.getElementById("Engine");
  var OilButton = document.getElementById("Oil");
  var RPMButton = document.getElementById("RPM");
  var FuelButton = document.getElementById("Fuel");
  AirspeedButton.addEventListener("click", changeGraph);
  EngineButton.addEventListener("click", changeGraph);
  RPMButton.addEventListener("click", changeGraph);
  OilButton.addEventListener("click", changeGraph);
  FuelButton.addEventListener("click", changeGraph);
  // Set a callback to run when the Google Visualization API is loaded.
  google.maps.event.addDomListener(window, 'load', initialize);
  // Callback that creates and populates a data table, 
  // instantiates the pie chart, passes in the data and
  // draws it.
  google.setOnLoadCallback(initialize);
  // Load the Visualization API and the piechart package.
  google.load('visualization', '1.0', {'packages':['corechart']});

 


  function initialize()
  {
    mapProp = {
      center: new google.maps.LatLng(<?php echo $center['lat']; ?>, <?php echo $center['long'];?>),
      zoom: <?php echo $zoomLevel ?>,
      scrollwheel: false,
      mapTypeId:google.maps.MapTypeId.ROADMAP,
      streetViewControl: false,
      overviewMapControl: false,
      panControl: false
    };

    map=new google.maps.Map(document.getElementById("googleMap"),mapProp);
    getCoords();
    getEvents();
    getData();
    updateGraph();
  }

  function drawChart() {

    // Create the data table.
    var data = new google.visualization.DataTable();
    data.addColumn({type: 'string', role: 'domain'});
    data.addColumn('number', 'Altitude');
    data.addColumn('number', 'Airspeed');
    data.addRows(graphData);

      //data.addColumn('string', 'Time');

     

    // Set chart options
    var options =  {'width':900,
                    'height':300,
                    series:{0:{targetAxisIndex:0},
                            1:{targetAxisIndex:1}},
                    vAxes:[
                      {title: '' ,textStyle:{ color: 'red'}},
                      {title: '' ,textStyle:{color: 'blue'}}
                      ],
                    legend: { position: 'none'},
                    focusTarget: 'category',
                    vAxis:{ticks:[]}};

    // Instantiate and draw our chart, passing in some options.
    chart = new google.visualization.LineChart(document.getElementById('chart_div'));
    chart.draw(data, options);

    google.visualization.events.addListener(chart, 'onmouseover', graphOverHandler);
    google.visualization.events.addListener(chart, 'onmouseout', graphOutHandler);
  };


  function graphOutHandler(e){
        marker.setMap(null);
  };
  function graphOverHandler(e){

    var flightCoordIndex = Math.floor((e.row/graphData.length)*(endSlice - beginSlice));
    console.log(flightCoordIndex + beginSlice);
    marker = new google.maps.Marker({
      position: flightCoord[flightCoordIndex + beginSlice],
      map: map,
      icon: image
    })
  };

  function changeGraph(eventObject, argumentsObject)
  {
    var itemClicked = eventObject.srcElement.id;
    if(itemClicked == "Airspeed"){
      altOn = !altOn
    }
    else if(itemClicked == "Engine"){
      engineOn = !engineOn
    }
    else if(itemClicked == "Oil"){
      oilOn = !oilOn
    }
    else if(itemClicked == "RPM"){
      rpmOn = !rpmOn
    }
    else if(itemClicked == "Fuel"){
      fuelOn = !fuelOn
    }
    getData();
    currentGraph = eventObject.srcElement.id
    updateGraph();
  };

  function makeBoxes()
  {
    for (var i = 0; i < events.length; i++) {
      var boxBound = new google.maps.LatLngBounds(
          new google.maps.LatLng(events[i][4],
                                 events[i][5]),
          new google.maps.LatLng(events[i][2],
                                 events[i][3]));
      var rect = new google.maps.Rectangle({
        strokeColor: '#0000FF',
        strokeOpacity: 0.8,
        strokeWeight: 2,
        fillColor: '#0000FF',
        fillOpacity: 0.2,
        map: map,
        bounds : boxBound
        });
      rectangle[i] = rect; 
    };
  }
  function highlight(eventObject, argumentsObject){
    if(rectangle.length == 0){
      makeBoxes();
    }
    if(clicked == false){
      for (var i = rectangle.length - 1; i >= 0; i--) {
        rectangle[i].setMap(null);
      };
      rectangle[eventObject.srcElement.id[4]].setMap(map);
    }
  }

  function clickTurn(eventObject, argumentsObject){
    if(clicked == false){
      for (var i = rectangle.length - 1; i >= 0; i--) {
        rectangle[i].setMap(null);
      };
      var bounds = new google.maps.LatLngBounds();
      bounds.extend(new google.maps.LatLng(events[eventObject.srcElement.id[4]][4],
                                      events[eventObject.srcElement.id[4]][5]));
      bounds.extend(new google.maps.LatLng(events[eventObject.srcElement.id[4]][2],
                                      events[eventObject.srcElement.id[4]][3]));
      map.setCenter(new google.maps.LatLng(events[eventObject.srcElement.id[4]][6],
                                      events[eventObject.srcElement.id[4]][7]));
      map.fitBounds(bounds);
      beginSlice = events[eventObject.srcElement.id[4]][1];
      endSlice = events[eventObject.srcElement.id[4]][8];
      clicked = true;
    } else {
      map.setCenter(new google.maps.LatLng(<?php echo $center['lat']; ?>, <?php echo $center['long'];?>));
      map.setZoom(<?php echo $zoomLevel ?>);
      clicked = false;
      beginSlice = 0;
      endSlice = flightCoord.length;

    }  
    getData();    
    updateGraph();
  }

  function getData(){
    var xmlhttp = new XMLHttpRequest();
    var price;
    $.ajax({
        url:'<?php echo Router::url(array('controller' => 'flights', 'action' => 'getData')); ?>',
        type:'post',
        data:{
          altOn: altOn,
          engineOn: engineOn,
          oilOn: oilOn,
          rpmOn: rpmOn,
          fuelOn: fuelOn,
          beginSlice: beginSlice,
          endSlice: endSlice,
          flightid: <?php echo $flight['Flight']['id'] ?>,
          studentid: <?php echo '"'.$flight['Flight']['studentid'].'"' ?>,
        },
        success:function(data){
          if(data != 'empty'){
            graphData = JSON.parse(data);
            updateGraph();
          }
        }
    })
  }

  function getEvents(){
    var xmlhttp = new XMLHttpRequest();
    var price;
    $.ajax({
        url:'<?php echo Router::url(array('controller' => 'flights', 'action' => 'getEvents')); ?>',
        type:'post',
        data:{
          flightid: <?php echo $flight['Flight']['id'] ?>,
          studentid: <?php echo '"'.$flight['Flight']['studentid'].'"' ?>,
        },
        success:function(data){
          events = JSON.parse(data);
        }
    })
  }

  function getCoords(){
    var xmlhttp = new XMLHttpRequest();
    var price;
    $.ajax({
        url:'<?php echo Router::url(array('controller' => 'flights', 'action' => 'getCoords')); ?>',
        type:'post',
        data:{
          flightid: <?php echo $flight['Flight']['id'] ?>,
          studentid: <?php echo '"'.$flight['Flight']['studentid'].'"' ?>,
        },
        success:function(data){
          var newCoords = [];

          var crude = JSON.parse(data);
          for (var i = 0; i < crude.length; i++){
            newCoords.push(new google.maps.LatLng(crude[i][0], crude[i][1]));
          }
          flightCoord = newCoords;
          var flightPath = new google.maps.Polyline({
            path: flightCoord,
            geodesic: true,
            strokeColor: '#FF0000',
            strokeOpacity: 1.0,
            strokeWeight: 2
          });

          flightPath.setMap(map);
            
        }
    })
  }
  function updateGraph(){
    activeSeries = [];
    vAxesList = [];
    colorsList =[];
    var data = new google.visualization.DataTable();
    interval = 0;
    data.addColumn('string', 'Time');
    if(altOn){
      data.addColumn('number', 'Altitude');
      data.addColumn({type: 'string', role: 'tooltip'});
      data.addColumn('number', 'Airspeed');
      data.addColumn({type: 'string', role: 'tooltip'});
      activeSeries.push([{targetAxisIndex:0, color:'green'}]);
      activeSeries.push([{targetAxisIndex:1}]);
      vAxesList.push({title: ''});
      vAxesList.push({title: ''});
      interval = interval + 2;
      colorsList.push("#0017FF", "#0DA2FF")
    }
    if(engineOn){
      data.addColumn('number', 'CHT');
      data.addColumn({type: 'string', role: 'tooltip'});
      data.addColumn('number', 'EGT');      
      data.addColumn({type: 'string', role: 'tooltip'});
      activeSeries.push([{targetAxisIndex:2}]);
      activeSeries.push([{targetAxisIndex:3}]);
      vAxesList.push({title: '' ,textStyle:{ color: 'red'}});
      vAxesList.push({title: '' ,textStyle:{ color: 'red'}});
      interval = interval + 2;
      colorsList.push("#FF0000", "#FF540D")
    }
    if(oilOn){
      data.addColumn('number', 'Oil Presure');
      data.addColumn({type: 'string', role: 'tooltip'});
      data.addColumn('number', 'Oil Temp');
      data.addColumn({type: 'string', role: 'tooltip'});
      activeSeries.push([{targetAxisIndex:4}]);
      activeSeries.push([{targetAxisIndex:5}]);
      vAxesList.push({title: '' ,textStyle:{ color: 'red'}});
      vAxesList.push({title: '' ,textStyle:{ color: 'red'}});
      interval = interval + 2;
      colorsList.push('#FF00FD', '#AD0CE8');
    }
    if(rpmOn){
      data.addColumn('number', 'MAP');
      data.addColumn({type: 'string', role: 'tooltip'});
      data.addColumn('number', 'RPM');
      data.addColumn({type: 'string', role: 'tooltip'});
      activeSeries.push([{targetAxisIndex:6}]);
      activeSeries.push([{targetAxisIndex:7}]);
      vAxesList.push({title: '' ,textStyle:{ color: 'red'}});
      vAxesList.push({title: '' ,textStyle:{ color: 'red'}});
      interval = interval + 2;
      colorsList.push("#FFE700", "#FFB70D");
    }
    if(fuelOn){
      data.addColumn('number', 'Fuel Flow');
      data.addColumn({type: 'string', role: 'tooltip'});
      data.addColumn('number', 'Fuel Presure');
      data.addColumn({type: 'string', role: 'tooltip'});
      activeSeries.push([{targetAxisIndex:8}]);
      activeSeries.push([{targetAxisIndex:9}]);
      vAxesList.push({title: '' ,textStyle:{ color: 'red'}});
      vAxesList.push({title: '' ,textStyle:{ color: 'red'}});
      interval = interval + 2;
      colorsList.push('#00FF3F','#0DFFD2');
    }
    interval = Math.floor((endSlice - beginSlice) * interval / 1000);
    data.addRows(graphData);
    var options =  {'width':900,
              'height':300,
              series:activeSeries,
              vAxes:vAxesList,
              legend: { position: 'none'},
              focusTarget: 'category',
              vAxis:{ticks: []},
              colors:colorsList};

    var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
    chart.draw(data, options);

    google.visualization.events.addListener(chart, 'onmouseover', graphOverHandler);
    google.visualization.events.addListener(chart, 'onmouseout', graphOutHandler);
  }
  var table = document.getElementsByClassName("turn-table")[0];
  var turnElement = table.getElementsByTagName("td");

  for (var i = turnElement.length - 1; i >= 0; i--) {
    turnElement[i].addEventListener("mouseover", highlight);
    turnElement[i].addEventListener("click",clickTurn);
  };

</script>

