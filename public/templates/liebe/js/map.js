$(document).ready(function () {

		//Open street  Map
		var defaultLat = 40.738270;
		var defaultLng = -74.008911;
		var initialLat = (typeof window.map_initial_latitude !== 'undefined' && window.map_initial_latitude !== null)
			? parseFloat(window.map_initial_latitude)
			: defaultLat;
		var initialLng = (typeof window.map_initial_longitude !== 'undefined' && window.map_initial_longitude !== null)
			? parseFloat(window.map_initial_longitude)
			: defaultLng;
		var coord = [initialLat, initialLng]; // <--- coordinates here

		var map = L.map('map-canvas', { scrollWheelZoom:false}).setView(coord, 19);

		L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
		maxZoom: 22,
		attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
		}).addTo(map);

		L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}', {
		attribution: ''
		}).addTo(map);

		// custom icon
		var customIcon = L.icon({
		iconUrl: (typeof window.map_marker_icon !== 'undefined' && window.map_marker_icon) ? window.map_marker_icon : 'img/mapmarker.png',
		iconSize:     [64, 64], // size of the icon
		iconAnchor:   [32, 63] // point of the icon which will correspond to marker's location
		});

		// marker object, pass custom icon as option, add to map
		var markers = (typeof window.map_markers !== 'undefined' && Array.isArray(window.map_markers) && window.map_markers.length)
			? window.map_markers
			: [{ latitude: initialLat, longitude: initialLng }];

		markers.forEach(function(markerData) {
			var markerLat = markerData.latitude || markerData.lat || markerData.geo_lat || initialLat;
			var markerLng = markerData.longitude || markerData.lng || markerData.geo_lng || initialLng;
			var marker = L.marker([markerLat, markerLng], {icon: customIcon}).addTo(map);
			if (markerData.title) {
				marker.bindPopup(markerData.title);
			}
		});
});
