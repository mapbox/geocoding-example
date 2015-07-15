# Node.js - Mapbox Batch Geocoder

`mapbox_batch.js` reads queries from an input text file, one per line, and constructs parallelized batch queries.

Results are stored in an output directory as JSON files.

## Requirements

- [queue-async](https://www.npmjs.com/package/queue-async)
- [through](https://www.npmjs.com/package/through)
- A Mapbox access token with batch geocoding capabilities (email sales@mapbox.com)

## Installation

`npm install`

## Usage

Command line:
```
MapboxAccessToken=YOUR_ACCESS_TOKEN node mapbox-batch.js input_file.txt /path/to/output/directory
```

Programmatic:
```
var mapbox = require('./mapbox-batch.js');

var geocoder = mapbox('YOUR_ACCESS_TOKEN');
geocoder.on('data', function(data) {
    console.log(data);
});
fs.createReadStream('/path/to/source/data.txt').pipe(geocoder);
```
