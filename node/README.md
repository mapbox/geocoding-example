# Node.js

`mapbox-geocode.js` takes a single query as a parameter and outputs the result.

## Requirements

- Node.js
- A Mapbox access token with batch geocoding capabilities (email sales@mapbox.com)

## Usage

Command line:
```
MAPBOX_ACCESS_TOKEN=__your access token__ node ./mapbox-geocode.js '1600 Pennsylvania Ave Washington, DC'
```

Programmatic:
```
> var mapbox = require('./mapbox-geocode.js');
> mapbox('YOUR_ACCESS_TOKEN', '1600 Pennsylvania Ave Washington, DC', function(err, data) { console.log(data); });

{ type: 'FeatureCollection',
  query: [ '1600', 'pennsylvania', 'ave', 'washington', 'dc' ],
  features:
   [ { id: 'address.170282823806239',
       type: 'Feature',
       text: 'Pennsylvania Ave NW',
       place_name: '1600 Pennsylvania Ave NW, Washington, 20006, District of Columbia, United States',
       relevance: 0.863516129032258,
       center: [Object],
       geometry: [Object],
       bbox: [Object],
       address: '1600',
       properties: {},
       context: [Object] },
[...]
```