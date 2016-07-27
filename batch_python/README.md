# Python 2/3 - Mapbox Batch Geocoder

`mapbox_batch.py` reads queries from an input text file, one per line, and constructs parallelized batch queries.

Results are stored in an output directory as JSON files.

## Requirements

- [gevent 1.1.2 or later](https://pypi.python.org/pypi/gevent)
- [requests](http://docs.python-requests.org/en/latest/)
- A Mapbox access token with batch geocoding capabilities (email sales@mapbox.com)

## Installation

`pip install -r requirements.txt`

## Usage

Command line:
```
MapboxAccessToken=YOUR_ACCESS_TOKEN python mapbox_batch.py input_file.txt /path/to/output/directory
```

Programmatic:
```
from mapbox_batch import MapboxBatchGeocoder

mapbox = MapboxBatchGeocoder('YOUR_ACCESS_TOKEN')
with open('/path/to/input_file.txt') as input_file:
    mapbox.geocode(input_file, '/path/to/output/directory/')
```
