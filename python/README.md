# Python 2/3

`mapbox_geocode.py` takes a single query as a parameter and outputs the result.

## Requirements

- Python 2/3
- A Mapbox access token with batch geocoding capabilities (email sales@mapbox.com)

## Usage

Command line:
```
MAPBOX_ACCESS_TOKEN=__your access token__ python ./mapbox_geocode.py '123 Main St. Smallville, KS'
```

Programmatic:
```
>>> from mapbox_geocode import geocode
>>> geocode('YOUR_ACCESS_TOKEN', '123 Main St. Smallville, KS')
{u'attribution': u'\xa9 2015 Mapbox and its suppliers. All rights reserved. Use of this data is subject to the Mapbox Terms of Service. (https://www.mapbox.com/about/maps/)', u'query': [u'123', u'main', u'st', u'smallville', u'ks'], u'type': u'FeatureCollection', u'features': [{u'center': [-101.046349, 39.807475], u'geometry': {u'type': u'Point', u'coordinates': [-101.046349, 39.807475], u'interpolated': True}, u'text': u'Main St', u'properties': {}, u'bbox': [-101.04683399999998, 39.80746699999999, -101.03744699999999, [...]
```

