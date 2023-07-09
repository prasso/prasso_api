export default function gogo_route() {
    function getLatLonForAddress(address) {
    const encodedInput = encodeURIComponent(address);
    const url = `/getLatLonFromAddress?q=${encodedInput}`;
    console.log(url);
    return fetch(url)
      .then(response => response.json())
      .then(data => {
        if (data.length > 0) {
          const { lat, lon } = data[0];
          return { lat, lon };
        } else {
          console.log(data);
          throw new Error('Address not found');
        }
      })
      .catch(error => {
        console.log(error);
          
        throw new Error('Error fetching address');
      });
  }
  
  function sendSelectedItems() {
    const selectedItems = $data.routeItems;
    const storedAddress = localStorage.getItem('address');
    const input = prompt(`Please enter your beginning address to calculate the routes for ${selectedItems.length} items:`, storedAddress || '');
    if (input !== null) {
      localStorage.setItem('address', input);
      let startLat, startLon; // declare variables to store the starting point's latitude and longitude
      getLatLonForAddress(input)
        .then(({ lat, lon }) => {
          startLat = lat; // store the starting point's latitude
          startLon = lon; // store the starting point's longitude
          map.setView([lat, lon], 13);
          L.marker([lat, lon]).addTo(map);
          // calculate the route between the starting point and all the destinations
          const waypoints = selectedItems.map(item => {
            const destinationAddress = item ? `${JSON.parse(item.address).address}, ${JSON.parse(item.address).city}, ${JSON.parse(item.address).state} ${JSON.parse(item.address).zip}` : '';
            return getLatLonForAddress(destinationAddress).then(({ lat, lon }) => L.latLng(lat, lon));
          });
          Promise.all(waypoints).then(waypoints => {
            const routingControl = L.Routing.control({
              waypoints: [L.latLng(startLat, startLon), ...waypoints],
              router: L.Routing.mapbox('sk.eyJ1IjoiYnBlcnJlYXVsdCIsImEiOiJjbGplYjY2aWoyZjFqM2tsNGdnd3BndGQzIn0.j1xDdq6Y3KI3Q_bcBmqCqg'),
              lineOptions: {
                styles: [
                  { color: 'red', opacity: 0.8, weight: 5 },
                  { color: 'green', opacity: 0.8, weight: 5 },
                  { color: 'blue', opacity: 0.8, weight: 5 },
                  { color: 'orange', opacity: 0.8, weight: 5 },
                  { color: 'purple', opacity: 0.8, weight: 5 },
                  { color: 'yellow', opacity: 0.8, weight: 5 },
                  { color: 'brown', opacity: 0.8, weight: 5 },
                  { color: 'pink', opacity: 0.8, weight: 5 },
                  { color: 'gray', opacity: 0.8, weight: 5 },
                  { color: 'black', opacity: 0.8, weight: 5 },
                ],
              },
            }).addTo(map);
            // display the route
            routingControl.route();
            // add markers for each destination and color them according to the route segment color
            routingControl.on('routesfound', function(event) {
              const routes = event.routes;
              for (let i = 0; i < routes.length; i++) {
                const route = routes[i];
                const segmentColor = routingControl.options.lineOptions.styles[i].color;
                for (let j = 0; j < route.coordinates.length; j++) {
                  const coordinate = route.coordinates[j];
                  L.marker([coordinate.lat, coordinate.lng], { icon: L.divIcon({ className: 'marker', iconSize: [20, 20], html: `<div style="background-color: ${segmentColor}"></div>` }) }).addTo(map);
                }
              }
            });
          });
        })
        .catch(error => {
          alert(error.message);
        });
    }
  }
  
  function sortSelectedRows(newlySelectedRows, currentSelectedRows, currentRouteItems) {
    currentRouteItems.splice(0, currentRouteItems.length);
   const selectedRows = currentSelectedRows.filter(row => newlySelectedRows.includes(row));
   newlySelectedRows.forEach(row => {
     if (!selectedRows.includes(row)) {
       selectedRows.push(row);
     }
   });
    selectedRows.forEach(row => {
      const order = row;
      const item = JSON.parse(order.display);
      const pickupAddress = item.pickupAddress;
      const deliveryAddress = item.deliveryAddress;
      currentRouteItems.push({
        address: pickupAddress,
        type: 'pickup',
        order: 1
      });
      currentRouteItems.push({
        address: deliveryAddress,
        type: 'delivery',
        order: 2
      });
      
    });
    currentRouteItems.forEach((item, index) => {
      item.order = index + 1;
    });
    
    return selectedRows;
  }
  
  function updateSelectedRows(selectedRows, allRows, currentRouteItems) {
    allRows.forEach((row, index) => {
      const selectedindex = selectedRows.indexOf(row);
      if (selectedindex >= 0) {
  
        const pickupIndex = currentRouteItems.findIndex(item => item.address === JSON.parse(row.display).pickupAddress);
        const deliveryIndex = currentRouteItems.findIndex(item => item.address === JSON.parse(row.display).deliveryAddress);
        
        if (pickupIndex >= 0) {
          row.pickupRouteOrder = currentRouteItems[pickupIndex].order;
        } else {
          row.pickupRouteOrder = null;
        }
        if (deliveryIndex >= 0) {
          row.deliveryRouteOrder = currentRouteItems[deliveryIndex].order;
        } else {
          row.deliveryRouteOrder = null;
        }
      } else {
        row.pickupRouteOrder = null;
        row.deliveryRouteOrder = null;
      }
    });
    gridOptions.api.refreshCells({ columns: ['pickupRouteOrder', 'deliveryRouteOrder'] });
  }
  const gridOptions = {
    columnDefs: [
      { field: 'select', checkboxSelection: true },
      { field: 'customer', headerName: 'Customer', valueGetter: 'JSON.parse(data.display).customer' },
      { field: 'pickupRouteOrder', headerName: 'Pickup Route Order'},
      { field: 'pickupAddress', headerName: 'Pickup Address', valueGetter: 'JSON.parse(data.display).pickupAddress ? JSON.parse(JSON.parse(data.display).pickupAddress).address + \', \' + JSON.parse(JSON.parse(data.display).pickupAddress).city + \', \' + JSON.parse(JSON.parse(data.display).pickupAddress).state + \' \' + JSON.parse(JSON.parse(data.display).pickupAddress).zip : \'\' ' },
      { field: 'deliveryRouteOrder', headerName: 'Delivery Route Order'},
      { field: 'deliveryAddress', headerName: 'Delivery Address', valueGetter: 'JSON.parse(data.display).deliveryAddress ? JSON.parse(JSON.parse(data.display).deliveryAddress).address + \', \' + JSON.parse(JSON.parse(data.display).deliveryAddress).city + \', \' + JSON.parse(JSON.parse(data.display).deliveryAddress).state + \' \' + JSON.parse(JSON.parse(data.display).deliveryAddress).zip : \'\' ' },
      { field: 'item', headerName: 'Item', valueGetter: 'JSON.parse(data.display).item' },
      { field: 'status', headerName: 'Status', valueGetter: 'JSON.parse(data.display).status' }
    ],
   rowData: $data.orders,
   rowSelection: 'multiple',
   onSelectionChanged: function() {
     const selectedRows = gridOptions.api.getSelectedRows();
     
     const sortedRows = sortSelectedRows(selectedRows,$data.selectedItems, $data.routeItems);
  
     $data.selectedItems = sortedRows;
     $data.itemCount = $data.routeItems.length;
     updateSelectedRows(sortedRows, $data.orders, $data.routeItems);
   }
  };
  new agGrid.Grid(document.querySelector('#myGridAlpine'), gridOptions);
  $data.sendSelectedItems = sendSelectedItems;
  }